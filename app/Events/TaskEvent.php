<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The task instance.
     *
     * @var \App\Models\Task
     */
    public $task;

    /**
     * The event type (created, updated, deleted, etc.).
     *
     * @var string
     */
    public $type;

    /**
     * The event message.
     *
     * @var string
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Task  $task
     * @param  string  $type
     * @param  string  $message
     * @return void
     */
    public function __construct(Task $task, string $type, string $message)
    {
        $this->task = $task;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast on a private channel for the task's project
        return new PrivateChannel('project.' . $this->task->project_id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'task.' . $this->type;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'task' => $this->task->load(['project', 'assignee', 'creator']),
            'type' => $this->type,
            'message' => $this->message,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
