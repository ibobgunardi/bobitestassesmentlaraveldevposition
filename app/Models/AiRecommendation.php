<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id',   
        'reasoning_model',  
        'result_model', 
        'api_token',
        'reasoning_prompt',
        'result_prompt',
        'reasoning_output',
        'result_output',
        'reasoning_tokens',
        'result_tokens',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the recommendation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the projects associated with this recommendation.
     */
    public function projects()
    {
        $projectIds = json_decode($this->project_ids, true) ?: [];
        return Project::whereIn('id', $projectIds)->get();
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope a query to only include completed recommendations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending recommendations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing recommendations.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include failed recommendations.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if the recommendation is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the recommendation is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the recommendation is processing.
     */
    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    /**
     * Check if the recommendation has failed.
     */
    public function hasFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Get the formatted result with Markdown parsing.
     */
    public function getFormattedResultAttribute()
    {
        if (empty($this->result)) {
            return '';
        }

        // You would typically use a Markdown parser here
        // For simplicity, we'll just return the raw result
        return $this->result;
    }
}
