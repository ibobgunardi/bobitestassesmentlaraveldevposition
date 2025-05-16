<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Project;
use App\Models\AiRecommendation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiRecommendationService
{
    /**
     * Build a prompt for AI recommendation based on project IDs.
     *
     * @param array|null $projectIds
     * @return string
     */
    public function buildPromptForRecommendation(?array $projectIds = []): string
    {
        // Get tasks based on project IDs
        $query = Task::query()
            ->with('project')
            ->orderBy('priority', 'desc');
            
        // Filter by project IDs if provided
        if (!empty($projectIds)) {
            $query->whereIn('project_id', $projectIds);
        }
        
        $tasks = $query->limit(10)->get();
        
        // Get project information
        $projectInfo = '';
        if (!empty($projectIds)) {
            $projects = Project::whereIn('id', $projectIds)->get();
            foreach ($projects as $project) {
                $projectInfo .= "Project: {$project->name}\n";
                $projectInfo .= "Description: {$project->description}\n";
                $projectInfo .= "Client: {$project->client_name}\n\n";
            }
        } else {
            $projectInfo = "All projects are being considered.\n\n";
        }
        
        // Format tasks for the prompt
        $taskList = '';
        foreach ($tasks as $index => $task) {
            $taskList .= ($index + 1) . ". Task: {$task->name}\n";
            $taskList .= "   Project: " . ($task->project ? $task->project->name : 'No Project') . "\n";
            $taskList .= "   Status: " . ucfirst(str_replace('_', ' ', $task->status)) . "\n";
            $taskList .= "   Priority: {$task->priority}/10\n";
            $taskList .= "   Description: {$task->description}\n\n";
        }
        
        // Build the complete prompt
        $prompt = <<<EOT
You are an AI assistant for a task management system. Based on the following information about projects and tasks, provide recommendations for task prioritization, potential efficiencies, and next steps.

PROJECT INFORMATION:
{$projectInfo}

CURRENT TASKS (ordered by priority):
{$taskList}

Please provide:
1. A brief analysis of the current task priorities and their alignment with project goals.
2. Recommendations for task prioritization or reorganization.
3. Suggestions for potential efficiencies or improvements in workflow.
4. Recommended next steps or action items.

Your response should be concise, practical, and directly applicable to the tasks and projects listed above.
EOT;

        return $prompt;
    }
    
    /**
     * Send a request to the OpenRouter API for AI recommendations.
     *
     * @param AiRecommendation $recommendation
     * @return array
     */
    public function getRecommendationFromAI(AiRecommendation $recommendation): array
    {
        $apiKey = config('services.openrouter.api_key');
        $model = $recommendation->model ?? config('services.openrouter.default_model');
        $endpoint = config('services.openrouter.endpoint');
        
        // Log the request details
        Log::info('Sending AI recommendation request', [
            'model' => $model,
            'recommendation_id' => $recommendation->id,
        ]);
        
        try {
            // Make the API request
            $response = Http::withoutVerifying() // For development only
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => 'Task Management System'
                ])
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful assistant for a task management system. Provide concise, practical recommendations based on the information provided.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $recommendation->prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1000
                ]);
                
            // Check if the request was successful
            if ($response->successful()) {
                $result = $response->json();
                
                // Log the successful response
                Log::info('AI recommendation response received', [
                    'recommendation_id' => $recommendation->id,
                    'status' => 'success',
                ]);
                
                // Extract the content from the response
                $content = $result['choices'][0]['message']['content'] ?? '';
                
                return [
                    'success' => true,
                    'content' => $content,
                    'model' => $model,
                    'raw_response' => $result
                ];
            } else {
                // Log the error response
                Log::error('AI recommendation request failed', [
                    'recommendation_id' => $recommendation->id,
                    'status' => 'error',
                    'status_code' => $response->status(),
                    'response' => $response->json()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'API request failed with status code ' . $response->status(),
                    'details' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            // Log any exceptions
            Log::error('Exception during AI recommendation request', [
                'recommendation_id' => $recommendation->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process an AI recommendation request.
     *
     * @param AiRecommendation $recommendation
     * @return bool
     */
    public function processRecommendation(AiRecommendation $recommendation): bool
    {
        // Update status to processing
        $recommendation->status = 'processing';
        $recommendation->save();
        
        // Get recommendation from AI
        $result = $this->getRecommendationFromAI($recommendation);
        
        if ($result['success']) {
            // Update recommendation with result
            $recommendation->result = $result['content'];
            $recommendation->model_used = $result['model'];
            $recommendation->raw_response = json_encode($result['raw_response']);
            $recommendation->status = 'completed';
            $recommendation->completed_at = now();
            
            return $recommendation->save();
        } else {
            // Update recommendation with error
            $recommendation->status = 'failed';
            $recommendation->error = $result['error'];
            $recommendation->raw_response = json_encode($result['details'] ?? []);
            
            return $recommendation->save();
        }
    }
}
