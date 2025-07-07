<?php

namespace App\Events;

use App\Models\Thread;
use App\Services\BroadcastService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThreadUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The thread instance.
     *
     * @var Thread
     */
    public $thread;

    /**
     * The tenant ID.
     *
     * @var int
     */
    public $tenantId;

    /**
     * The action performed on the thread.
     *
     * @var string
     */
    public $action;

    /**
     * Additional data to include in the event.
     *
     * @var array
     */
    public $additionalData;

    /**
     * Create a new event instance.
     *
     * @param Thread $thread
     * @param int $tenantId
     * @param string $action
     * @param array $additionalData
     */
    public function __construct(Thread $thread, int $tenantId, string $action, array $additionalData = [])
    {
        $this->thread = $thread;
        $this->tenantId = $tenantId;
        $this->action = $action;
        $this->additionalData = $additionalData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        // Add the tenant-wide channel
        $channels[] = new PrivateChannel(BroadcastService::getThreadsChannel($this->tenantId));
        
        // Add the thread-specific channel
        $channels[] = new PrivateChannel(BroadcastService::getThreadChannel($this->tenantId, $this->thread->id));
        
        // Add the assigned user's channel if applicable
        if ($this->thread->assigned_to) {
            $channels[] = new PrivateChannel(BroadcastService::getUserChannel($this->tenantId, $this->thread->assigned_to));
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'thread.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->thread->id,
            'subject' => $this->thread->subject,
            'status' => $this->thread->status,
            'assigned_to_id' => $this->thread->assigned_to_id,
            'assigned_to' => $this->thread->assignedTo ? [
                'id' => $this->thread->assignedTo->id,
                'name' => $this->thread->assignedTo->name,
            ] : null,
            'last_activity_at' => $this->thread->last_activity_at->toIso8601String(),
            'action' => $this->action,
            'additional_data' => $this->additionalData,
            'tenant_id' => $this->tenantId,
        ];
    }
} 