<?php

namespace App\Observers;

use App\Models\Task;
use App\Events\TaskEvent;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     *
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function created(Task $task)
    {
        Log::info('Task created', ['task' => $task->id, 'name' => $task->name]);
        
        // Broadcast the task created event
        event(new TaskEvent(
            $task,
            'created',
            "Task '{$task->name}' has been created"
        ));
    }

    /**
     * Handle the Task "updated" event.
     *
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function updated(Task $task)
    {
        Log::info('Task updated', ['task' => $task->id, 'name' => $task->name]);
        
        // Get the changed attributes
        $changes = $task->getChanges();
        
        // Remove timestamps from changes
        unset($changes['updated_at']);
        
        // Only broadcast if there are meaningful changes
        if (!empty($changes)) {
            // Broadcast the task updated event
            event(new TaskEvent(
                $task,
                'updated',
                "Task '{$task->name}' has been updated"
            ));
        }
    }

    /**
     * Handle the Task "deleted" event.
     *
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function deleted(Task $task)
    {
        Log::info('Task deleted', ['task' => $task->id, 'name' => $task->name]);
        
        // Broadcast the task deleted event
        event(new TaskEvent(
            $task,
            'deleted',
            "Task '{$task->name}' has been deleted"
        ));
    }

    /**
     * Handle the Task "restored" event.
     *
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function restored(Task $task)
    {
        Log::info('Task restored', ['task' => $task->id, 'name' => $task->name]);
        
        // Broadcast the task restored event
        event(new TaskEvent(
            $task,
            'restored',
            "Task '{$task->name}' has been restored"
        ));
    }

    /**
     * Handle the Task "force deleted" event.
     *
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function forceDeleted(Task $task)
    {
        Log::info('Task force deleted', ['task' => $task->id, 'name' => $task->name]);
        
        // Broadcast the task force deleted event
        event(new TaskEvent(
            $task,
            'forceDeleted',
            "Task '{$task->name}' has been permanently deleted"
        ));
    }
}
