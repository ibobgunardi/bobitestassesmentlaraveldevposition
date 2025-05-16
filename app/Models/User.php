<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'job_title',
        'phone',
        'profile_photo',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the user.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the roles associated with the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Get the primary role associated with the user.
     */
    public function primaryRole()
    {
        return $this->roles()->wherePivot('is_primary', true)->first();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $rolesSlugs): bool
    {
        return $this->roles()->whereIn('slug', $rolesSlugs)->exists();
    }

    /**
     * Get the tasks for the user.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the projects for the user.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the AI recommendations for the user.
     */
    public function aiRecommendations()
    {
        return $this->hasMany(AiRecommendation::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Get the pending tasks for the user.
     */
    public function pendingTasks()
    {
        return $this->tasks()->where('status', 'pending');
    }

    /**
     * Get the in-progress tasks for the user.
     */
    public function inProgressTasks()
    {
        return $this->tasks()->where('status', 'in_progress');
    }

    /**
     * Get the completed tasks for the user.
     */
    public function completedTasks()
    {
        return $this->tasks()->where('status', 'completed');
    }

    /**
     * Get the high priority tasks for the user.
     */
    public function highPriorityTasks()
    {
        return $this->tasks()->where('priority', '>=', 8);
    }
}
