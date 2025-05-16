<?php

namespace App\Http\Controllers\API\AI;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use App\Models\Task;
use App\Http\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AiRecommendationController extends Controller
{
    /**
     * Handle AI Recommendation request.
     * Expects: project_id, reasoning_model, result_model, api_token, reasoning_prompt, result_prompt
     */
    public function getRecommendations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'reasoning_model' => 'required|string',
            'result_model' => 'required|string',
            'api_token' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }
    
        try {
            $tasks = Task::where('project_id', $request->project_id)
                ->orderBy('priority', 'desc')
                ->get();
    
            if ($tasks->isEmpty()) {
                return ApiResponse::error('No tasks found for this project.');
            }
    
            $taskListText = $tasks->map(fn($task) => "- {$task->name}: {$task->description}")->implode("\n");
    
            // Build Reasoning Prompt
            $reasoningPrompt = <<<PROMPT
                Analyze the following project tasks. Identify the correct order of execution based on priority, dependencies, and impact. Also identify any missing steps or logical gaps in the workflow.
    
                Project Tasks:
                $taskListText
                PROMPT;
    
            // Call OpenRouter for Reasoning
            $reasoningOutput = '';
            $reasoningTokens = 0;
            $api_token = trim($request->api_token);   
            // Strict validation
            if (!preg_match('/^sk-or-v1-[a-f0-9]{64}$/i', $api_token)) {
                return ApiResponse::error('Invalid OpenRouter key format');
            }
            
            // Create a unique request ID for tracking this recommendation process
            $requestId = uniqid('rec_', true);
            Log::info("Starting recommendation process [{$requestId}] for project {$request->project_id}");
            
            try {
                $requestData = [
                    'model' => $request->reasoning_model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert AI project manager...'],
                        ['role' => 'user', 'content' => $reasoningPrompt],
                    ],
                ];
                
                Log::info("Making first OpenRouter request [{$requestId}]");
                
                // Make the first request with rate limiting control
                $reasoningResponse = $this->makeRequestWithRetry(
                    'https://openrouter.ai/api/v1/chat/completions',
                    [
                        'Authorization' => 'Bearer '.$api_token, 
                        'HTTP-Referer' => config('app.url'),
                        'X-Title' => config('app.name'),
                        'Content-Type' => 'application/json'
                    ],
                    $requestData
                );
            
                $reasoningData = $reasoningResponse->json();
                
                $reasoningOutput = $reasoningData['choices'][0]['message']['content'] ?? '';
                $reasoningTokens = $reasoningData['usage']['total_tokens'] ?? 0;
                
                Log::info("First OpenRouter request [{$requestId}] completed successfully. Tokens: {$reasoningTokens}");
            } catch (\Throwable $e) {
                Log::error("Reasoning AI call failed [{$requestId}]", ['error' => $e->getMessage()]);
                return ApiResponse::error('AI reasoning failed: ' . $e->getMessage());
            }
    
            // Build Result Prompt based on Reasoning Output
            $resultPrompt = <<<PROMPT
                Based on this reasoning:
    
                $reasoningOutput
    
                Create a detailed project execution plan. List the steps, responsible roles, estimated deadlines (if possible), and milestones in logical order. The output should be highly actionable and optimized for efficiency.
                PROMPT;
    
            // Call OpenRouter for Result
            $resultOutput = '';
            $resultTokens = 0;
    
            try {
                $requestDataResult = [
                    'model' => $request->result_model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a strategic AI planner. Output a clear, actionable execution roadmap with responsible roles, deadlines, and project flow.'],
                        ['role' => 'user', 'content' => $resultPrompt],
                    ],
                ];
                
                Log::info("Making second OpenRouter request [{$requestId}]");
                
                // The makeRequestWithRetry method now handles rate limiting correctly,
                // we don't need an extra sleep here anymore as it's built into the method
                
                // Make the second request with rate limiting control
                $resultResponse = $this->makeRequestWithRetry(
                    'https://openrouter.ai/api/v1/chat/completions',
                    [
                        'Authorization' => 'Bearer '.$api_token, 
                        'HTTP-Referer' => config('app.url'),
                        'X-Title' => config('app.name'),
                        'Content-Type' => 'application/json'
                    ],
                    $requestDataResult
                );
            
                $resultData = $resultResponse->json();
                $resultOutput = $resultData['choices'][0]['message']['content'] ?? '';
                $resultTokens = $resultData['usage']['total_tokens'] ?? 0;
                
                Log::info("Second OpenRouter request [{$requestId}] completed successfully. Tokens: {$resultTokens}");
            } catch (\Throwable $e) {
                Log::error("Result AI call failed [{$requestId}]", ['error' => $e->getMessage()]);
                return ApiResponse::error('AI result generation failed: ' . $e->getMessage());
            }
    
            // Save to database
            $recommendation = AiRecommendation::create([
                'project_id' => $request->project_id,
                'reasoning_model' => $request->reasoning_model,
                'result_model' => $request->result_model,
                'api_token' => substr($api_token, 0, 10) . '...', // Only store truncated token for security
                'reasoning_prompt' => $reasoningPrompt,
                'result_prompt' => $resultPrompt,
                'reasoning_output' => $reasoningOutput,
                'result_output' => $resultOutput,
                'reasoning_tokens' => $reasoningTokens,
                'result_tokens' => $resultTokens,
            ]);
            
            Log::info("Recommendation process [{$requestId}] completed and saved with ID {$recommendation->id}");
    
            return ApiResponse::success($recommendation,  'AI recommendation generated successfully.');
        } catch (\Throwable $e) {
            Log::error('Unexpected error during AI recommendation generation', ['error' => $e->getMessage()]);
            return ApiResponse::error('Unexpected server error: ' . $e->getMessage());
        }
    }
    
    /**
     * Make an API request with rate limiting control
     * Handles 10 requests per 10-second limit
     * 
     * @param string $url The API endpoint URL
     * @param array $headers Request headers
     * @param array $data Request body data
     * @param int $maxRetries Maximum number of retry attempts
     * @return \Illuminate\Http\Client\Response
     * @throws \Exception
     */
    private function makeRequestWithRetry($url, $headers, $data, $maxRetries = 3)
    {
        // Use a cache prefix for rate limiting
        $cacheKey = 'openrouter_requests';
        $windowDuration = 10; // 10 seconds window
        $rateLimit = 10; // 10 requests per window
        
        // Get current request count in the time window
        $requestCount = Cache::get($cacheKey, []);
        
        // Clean up expired timestamps (older than windowDuration)
        $now = time();
        $requestCount = array_filter($requestCount, function($timestamp) use ($now, $windowDuration) {
            return $timestamp > ($now - $windowDuration);
        });
        
        // If we're at the limit, wait until the oldest request expires
        if (count($requestCount) >= $rateLimit) {
            $oldestRequest = min($requestCount);
            $timeToWait = $oldestRequest + $windowDuration - $now;
            
            if ($timeToWait > 0) {
                Log::info("Rate limit reached. Waiting {$timeToWait} seconds before making next request.");
                sleep($timeToWait);
            }
        }
        
        $attempt = 0;
        $backoffSeconds = 1;
        
        while ($attempt < $maxRetries) {
            try {
                // Add this request to our tracking before making it
                $requestCount[] = time();
                Cache::put($cacheKey, $requestCount, $windowDuration * 2);
                
                $response = Http::withHeaders($headers)
                    ->timeout(60)
                    ->post($url, $data);
                
                // Check if we got rate limited (despite our best efforts)
                if ($response->status() === 429) {
                    $attempt++;
                    $waitTime = $response->header('Retry-After') ?? ($windowDuration / 2);
                    
                    // Log the rate limit
                    Log::warning("Rate limited by OpenRouter API. Retrying in {$waitTime} seconds. Attempt {$attempt}/{$maxRetries}");
                    
                    // Wait before retry
                    sleep((int)$waitTime);
                    
                    // Exponential backoff
                    $backoffSeconds *= 2;
                    continue;
                }
                
                // If the response is not successful (and not rate-limited), throw an exception
                if ($response->failed()) {
                    throw new \Exception('API request failed with status code: ' . $response->status() . '. Response: ' . $response->body());
                }
                
                // If we get here, we had a successful response
                return $response;
                
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                
                Log::error("API request failed: {$e->getMessage()}. Retrying in {$backoffSeconds} seconds.");
                sleep($backoffSeconds);
                $backoffSeconds *= 2;
            }
        }
        
        return ApiResponse::error("Failed after {$maxRetries} attempts");
    }
    /**
     * Get AI recommendation history for a project
     */
    public function getRecommendationHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
        ]);
    
        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }
        
        try {
            $history = AiRecommendation::where('project_id', $request->project_id)
            ->with('project')
                ->where(function($query) {
                    $query->where('status', 'completed')
                          ->orWhere('status', 'failed');
                })
                ->orderBy('created_at', 'desc')
                ->get();
                
            return ApiResponse::success($history, 'AI recommendation history retrieved successfully.');
        } catch (\Exception $e) {
            Log::error('Error retrieving AI recommendation history', ['error' => $e->getMessage()]);
            return ApiResponse::error('Failed to retrieve AI recommendation history: ' . $e->getMessage());
        }
    }
}
