<?php

namespace App\Events;

use App\Models\Thread;
use App\Models\User;
use App\Services\BroadcastService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThreadAssignedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The thread instance.
     *
     * @var Thread
     */
    public $thread;

    /**
     * The user the thread was assigned to.
     *
     * @var User|null
     */
    public $assignedTo;

    /**
     * The user who assigned the thread.
     *
     * @var User|null
     */
    public $assignedBy;

    /**
     * The tenant ID.
     *
     * @var int
     */
    public $tenantId;

    /**
     * Create a new event instance.
     *
     * @param Thread $thread
     * @param User|null $assignedTo
     * @param User|null $assignedBy
     * @param int $tenantId
     */
    public function __construct(Thread $thread, ?User $assignedTo, ?User $assignedBy, int $tenantId)
    {
        $this->thread = $thread;
        $this->assignedTo = $assignedTo;
        $this->assignedBy = $assignedBy;
        $this->tenantId = $tenantId;
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
        if ($this->assignedTo) {
            $channels[] = new PrivateChannel(BroadcastService::getUserChannel($this->tenantId, $this->assignedTo->id));
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
        return 'thread.assigned';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'thread' => [
                'id' => $this->thread->id,
                'subject' => $this->thread->subject,
                'status' => $this->thread->status,
            ],
            'assigned_to' => $this->assignedTo ? [
                'id' => $this->assignedTo->id,
                'name' => $this->assignedTo->name,
                'email' => $this->assignedTo->email,
            ] : null,
            'assigned_by' => $this->assignedBy ? [
                'id' => $this->assignedBy->id,
                'name' => $this->assignedBy->name,
            ] : null,
            'tenant_id' => $this->tenantId,
        ];
    }
} 