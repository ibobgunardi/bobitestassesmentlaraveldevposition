<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TaskReordered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $old_priority;
    public $new_priority;
    public $user_name;
    public $task_log_id;

    public function __construct(Task $task, int $oldPriority, int $newPriority, $taskLogId = null)
    {
        $this->task = $task;
        $this->old_priority = $oldPriority;
        $this->new_priority = $newPriority;
        $this->user_name = auth()->user() ? auth()->user()->name : 'System';
        $this->task_log_id = $taskLogId;
    }

    public function broadcastOn()
    {
        try {
            // Log the broadcast attempt
            Log::info('Broadcasting TaskReordered event', [
                'task_id' => $this->task->id,
                'channel' => 'presence-task-updates',
                'user' => $this->user_name
            ]);
            
            return new PresenceChannel('task-updates');
        } catch (\Exception $e) {
            Log::error('Error in broadcastOn', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function broadcastAs()
    {
        return 'task.reordered';
    }
    
    public function broadcastWith()
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'priority' => $this->task->priority,
                'title' => $this->task->title,
            ],
            'old_priority' => $this->old_priority,
            'new_priority' => $this->new_priority,
            'user_name' => $this->user_name,
            'task_log_id' => $this->task_log_id,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}