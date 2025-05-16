<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'created_by',
        'client_id',
        'name',
        'slug',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tasks for the project.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the company that owns the project.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user that created the project.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the client associated with the project.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the completion percentage of the project.
     */
    public function getCompletionPercentageAttribute()
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }
        
        $completedTasks = $this->tasks()->where('status', 'completed')->count();
        
        return ($completedTasks / $totalTasks) * 100;
    }

    /**
     * Scope a query to only include active projects.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    /**
     * Scope a query to only include completed projects.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include projects that are on hold.
     */
    public function scopeOnHold($query)
    {
        return $query->where('status', 'on_hold');
    }

    /**
     * Scope a query to only include cancelled projects.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get the pending tasks for the project.
     */
    public function pendingTasks()
    {
        return $this->tasks()->where('status', 'not_started');
    }

    /**
     * Get the in-progress tasks for the project.
     */
    public function inProgressTasks()
    {
        return $this->tasks()->where('status', 'in_progress');
    }

    /**
     * Get the completed tasks for the project.
     */
    public function completedTasks()
    {
        return $this->tasks()->where('status', 'completed');
    }

    /**
     * Get the cancelled tasks for the project.
     */
    public function cancelledTasks()
    {
        return $this->tasks()->where('status', 'cancelled');
    }

    /**
     * Get the high priority tasks for the project.
     */
    public function highPriorityTasks()
    {
        return $this->tasks()->where('priority', 'high')->orWhere('priority', 'urgent');
    }

    /**
     * Get the medium priority tasks for the project.
     */
    public function mediumPriorityTasks()
    {
        return $this->tasks()->where('priority', 'medium');
    }

    /**
     * Get the low priority tasks for the project.
     */
    public function lowPriorityTasks()
    {
        return $this->tasks()->where('priority', 'low');
    }

    /**
     * Get the status badge CSS class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'not_started' => 'bg-secondary',
            'in_progress' => 'bg-primary',
            'on_hold' => 'bg-warning',
            'completed' => 'bg-info',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the formatted status.
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
    
    /**
     * Get the status badge CSS class (non-attribute version).
     * This method is added for backward compatibility.
     */
    public function getStatusBadgeClass()
    {
        return $this->status_badge_class;
    }
    
    /**
     * Get the status label for display.
     * This method is added for backward compatibility.
     */
    public function getStatusLabel()
    {
        return $this->formatted_status;
    }
}
