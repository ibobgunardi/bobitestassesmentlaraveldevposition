<?php

namespace App\Listeners;

use App\Events\TaskReordered;
use App\Models\TaskLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogTaskReorder
{
    public function handle(TaskReordered $event)
    {
        try {
            $taskLog = TaskLog::create([
                'company_id' => Auth::user()->company_id,
                'task_id' => $event->task->id,
                'user_id' => Auth::user()->id,
                'action' => 'reordered',
                'description' => 'Task reordered from priority ' . $event->old_priority . ' to ' . $event->new_priority,
                'old_values' => ['priority' => $event->old_priority],
                'new_values' => ['priority' => $event->new_priority],
            ]);
            
            Log::info('Task reorder logged successfully', [
                'task_id' => $event->task->id,
                'old_priority' => $event->old_priority,
                'new_priority' => $event->new_priority,
                'task_log_id' => $taskLog->id
            ]);
            
            // Return the log ID to be used by the event
            return $taskLog->id;
            
        } catch (\Exception $e) {
            Log::error('Failed to log task reorder', [
                'error' => $e->getMessage(),
                'task_id' => $event->task->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}