<?php

namespace App\Jobs;

use App\Models\AiRecommendation;
use App\Services\AiRecommendationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiRecommendation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The AI recommendation instance.
     *
     * @var \App\Models\AiRecommendation
     */
    protected $recommendation;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\AiRecommendation  $recommendation
     * @return void
     */
    public function __construct(AiRecommendation $recommendation)
    {
        $this->recommendation = $recommendation;
    }

    /**
     * Execute the job.
     *
     * @param  \App\Services\AiRecommendationService  $aiRecommendationService
     * @return void
     */
    public function handle(AiRecommendationService $aiRecommendationService)
    {
        Log::info('Processing AI recommendation', [
            'recommendation_id' => $this->recommendation->id
        ]);

        try {
            // Process the recommendation
            $result = $aiRecommendationService->processRecommendation($this->recommendation);
            
            Log::info('AI recommendation processed', [
                'recommendation_id' => $this->recommendation->id,
                'success' => $result
            ]);
            
            // Broadcast the result
            // event(new AiRecommendationCompleted($this->recommendation));
            
        } catch (\Exception $e) {
            Log::error('Error processing AI recommendation', [
                'recommendation_id' => $this->recommendation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update recommendation with error
            $this->recommendation->status = 'failed';
            $this->recommendation->error = 'Job error: ' . $e->getMessage();
            $this->recommendation->save();
            
            // Fail the job
            $this->fail($e);
        }
    }
}
