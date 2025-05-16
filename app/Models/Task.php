<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'project_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
        'is_active' => 'boolean',
        'estimated_hours' => 'integer',
        'actual_hours' => 'integer',
    ];


    protected $priorityThresholds = [];

    public function setPriorityThresholds(array $thresholds)
{
    $this->priorityThresholds = $thresholds;
}
    /**
     * Get the project that owns the task.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    /**
     * Get the priority level label based on the numeric priority value
     *
     * @return string
     */
    public function getPriorityLevelAttribute()
    {
        $thresholds = $this->priorityThresholds;
    
        if (!$thresholds || !isset($thresholds['high_min'], $thresholds['medium_min'])) {
            return null; // Fallback
        }
    
        $priority = (int) $this->priority;
    
        if ($priority >= $thresholds['high_min']) {
            return [
                'level' => 'high',
                'label' => 'High',
                'bg' => 'bg-red-500',
                'text' => 'text-white',
                'border' => 'border-red-500'
            ];
        } elseif ($priority >= $thresholds['medium_min']) {
            return [
                'level' => 'medium',
                'label' => 'Medium',
                'bg' => 'bg-yellow-500',
                'text' => 'text-yellow-900',
                'border' => 'border-yellow-500'
            ];
        } else {
            return [
                'level' => 'low',
                'label' => 'Low',
                'bg' => 'bg-green-500',
                'text' => 'text-white',
                'border' => 'border-green-500'
            ];
        }
    }
    
    
    
    /**
     * Get the priority badge class based on the numeric priority value
     *
     * @return string
     */
    public function getPriorityBadgeClassAttribute()
    {
        return match($this->priority_level) {
            'urgent' => 'bg-danger',
            'high' => 'bg-warning',
            'medium' => 'bg-info',
            'low' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the company that owns the task.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user that created the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that the task is assigned to.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope a query to only include tasks with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include tasks with a priority greater than or equal to the given value.
     */
    public function scopeHighPriority($query, $value = 8)
    {
        return $query->where('priority', '>=', $value);
    }

    /**
     * Scope a query to only include tasks with a priority between the given values.
     */
    public function scopeMediumPriority($query, $min = 4, $max = 7)
    {
        return $query->whereBetween('priority', [$min, $max]);
    }

    /**
     * Scope a query to only include tasks with a priority less than or equal to the given value.
     */
    public function scopeLowPriority($query, $value = 3)
    {
        return $query->where('priority', '<=', $value);
    }

    /**
     * Scope a query to only include tasks that are overdue.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope a query to only include tasks that are due today.
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', now()->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope a query to only include tasks that are due this week.
     */
    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Check if the task is overdue.
     */
    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast() && !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if the task is due today.
     */
    public function isDueToday()
    {
        return $this->due_date && $this->due_date->isToday() && !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if the task is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Mark the task as completed.
     */
    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        return $this->save();
    }

    /**
     * Mark the task as in progress.
     */
    public function markAsInProgress()
    {
        $this->status = 'in_progress';
        $this->completed_at = null;
        return $this->save();
    }

    /**
     * Mark the task as pending.
     */
    public function markAsPending()
    {
        $this->status = 'pending';
        $this->completed_at = null;
        return $this->save();
    }

    /**
     * Mark the task as cancelled.
     */
    public function markAsCancelled()
    {
        $this->status = 'cancelled';
        return $this->save();
    }
}
