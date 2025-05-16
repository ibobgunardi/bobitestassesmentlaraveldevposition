<?php

namespace App\Jobs;

use App\Http\Controllers\API\AI\AiRecommendationController;
use App\Models\AiRecommendation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiRecommendation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $projectId;
    public $reasoningModel;
    public $resultModel;
    public $apiToken;

    /**
     * Create a new job instance.
     *
     * @param int $projectId
     * @param string $reasoningModel
     * @param string $resultModel
     * @param string $apiToken
     */
    public function __construct(int $projectId, string $reasoningModel, string $resultModel, string $apiToken)
    {
        $this->projectId = $projectId;
        $this->reasoningModel = $reasoningModel;
        $this->resultModel = $resultModel;
        $this->apiToken = $apiToken;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Starting GenerateAiRecommendation job for project {$this->projectId}");
        
        $controller = new AiRecommendationController();
        $recommendation = $controller->generateRecommendation(
            $this->projectId,
            $this->reasoningModel,
            $this->resultModel,
            $this->apiToken
        );

        if ($recommendation) {
            Log::info("GenerateAiRecommendation job completed successfully for project {$this->projectId}. Recommendation ID: {$recommendation->id}");
            // You might want to add further logic here, like sending a notification to the user.
        } else {
            Log::error("GenerateAiRecommendation job failed for project {$this->projectId}.");
            // Handle failure - perhaps retry or notify an administrator.
            $this->fail(new \Exception("AI Recommendation failed. Check logs for details.")); 
        }
    }
}