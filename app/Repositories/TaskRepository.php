<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\CacheService;

class TaskRepository
{
    /**
     * The cache service instance.
     *
     * @var \App\Services\CacheService
     */
    protected $cacheService;

    /**
     * Create a new repository instance.
     *
     * @param  \App\Services\CacheService  $cacheService
     * @return void
     */
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get all tasks with optional filtering and pagination
     *
     * @param array $filters
     * @param int $perPage
     * @param string $sortBy
     * @param string $sortDirection
     * @return LengthAwarePaginator
     */
    public function getTasks(array $filters = [], int $perPage = 10, string $sortBy = 'priority', string $sortDirection = 'desc'): LengthAwarePaginator
    {
        $query = Task::query();
        
        // Apply user filter
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        // Apply project filter - supports both single project_id and array of project_ids
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        } elseif (!empty($filters['project_ids']) && is_array($filters['project_ids'])) {
            $query->whereIn('project_id', $filters['project_ids']);
        }
        
        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortDirection);
        
        return $query->paginate($perPage);
    }
    
    /**
     * Get top priority tasks
     *
     * @param array|null $projectIds
     * @param int $limit
     * @return Collection
     */
    public function getTopPriorityTasks(?array $projectIds = null, int $limit = 5): Collection
    {
        $cacheKey = "top_priority_tasks_" . ($projectIds ? implode('_', $projectIds) : 'all') . "_{$limit}";
        
        return $this->cacheService->remember($cacheKey, 60, function () use ($projectIds, $limit) {
            $query = Task::query()
                ->orderBy('priority', 'desc')
                ->with('project');
            
            if ($projectIds && count($projectIds) > 0) {
                $query->whereIn('project_id', $projectIds);
            }
            
            return $query->limit($limit)->get();
        });
    }
    
    /**
     * Find a task by ID
     *
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }
    
    /**
     * Create a new task
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task
    {
        $task = Task::create($data);
        
        // Invalidate relevant caches
        $this->invalidateCaches($task);
        
        return $task;
    }
    
    /**
     * Update a task
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $task = $this->findById($id);
        
        if (!$task) {
            return false;
        }
        
        $result = $task->update($data);
        
        // Invalidate relevant caches
        $this->invalidateCaches($task);
        
        return $result;
    }
    
    /**
     * Delete a task
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $task = $this->findById($id);
        
        if (!$task) {
            return false;
        }
        
        $result = $task->delete();
        
        // Invalidate relevant caches
        $this->invalidateCaches($task);
        
        return $result;
    }
    
    /**
     * Invalidate relevant caches for a task
     *
     * @param Task $task
     * @return void
     */
    protected function invalidateCaches(Task $task): void
    {
        // Clear all top priority task caches
        $this->cacheService->clearPattern('top_priority_tasks_*');
        
        // Clear project-specific caches
        if ($task->project_id) {
            $this->cacheService->clearPattern("*_{$task->project_id}_*");
        }
    }
    
    /**
     * Get task statistics
     *
     * @param int|null $userId
     * @return array
     */
    public function getTaskStatistics(?int $userId = null): array
    {
        $cacheKey = "task_statistics_" . ($userId ?? 'all');
        
        return $this->cacheService->remember($cacheKey, 300, function () use ($userId) {
            $query = Task::query();
            
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $totalTasks = $query->count();
            $pendingTasks = (clone $query)->where('status', 'pending')->count();
            $inProgressTasks = (clone $query)->where('status', 'in_progress')->count();
            $completedTasks = (clone $query)->where('status', 'completed')->count();
            $cancelledTasks = (clone $query)->where('status', 'cancelled')->count();
            
            $highPriorityTasks = (clone $query)->where('priority', '>=', 8)->count();
            $mediumPriorityTasks = (clone $query)->whereBetween('priority', [4, 7])->count();
            $lowPriorityTasks = (clone $query)->where('priority', '<=', 3)->count();
            
            return [
                'total' => $totalTasks,
                'by_status' => [
                    'pending' => $pendingTasks,
                    'in_progress' => $inProgressTasks,
                    'completed' => $completedTasks,
                    'cancelled' => $cancelledTasks,
                ],
                'by_priority' => [
                    'high' => $highPriorityTasks,
                    'medium' => $mediumPriorityTasks,
                    'low' => $lowPriorityTasks,
                ],
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0
            ];
        });
    }
}
