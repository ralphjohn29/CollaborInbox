<?php

namespace App\Events;

use App\Models\Message;
use App\Services\BroadcastService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message instance.
     *
     * @var Message
     */
    public $message;

    /**
     * The tenant ID.
     *
     * @var int
     */
    public $tenantId;

    /**
     * Create a new event instance.
     *
     * @param Message $message
     * @param int $tenantId
     */
    public function __construct(Message $message, int $tenantId)
    {
        $this->message = $message;
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
        $channels[] = new PrivateChannel(BroadcastService::getThreadChannel($this->tenantId, $this->message->thread_id));
        
        // Add the assigned user's channel if applicable
        $thread = $this->message->thread;
        if ($thread && $thread->assigned_to_id) {
            $channels[] = new PrivateChannel(BroadcastService::getUserChannel($this->tenantId, $thread->assigned_to_id));
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
        return 'new.message';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $message = $this->message->load(['sender', 'attachments']);
        
        return [
            'id' => $message->id,
            'thread_id' => $message->thread_id,
            'body' => $message->body,
            'created_at' => $message->created_at->toIso8601String(),
            'sender' => $message->sender ? [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'email' => $message->sender->email,
            ] : null,
            'attachments' => $message->attachments->map(function($attachment) {
                return [
                    'id' => $attachment->id,
                    'filename' => $attachment->filename,
                    'file_size' => $attachment->file_size,
                    'mime_type' => $attachment->mime_type,
                ];
            }),
            'tenant_id' => $this->tenantId,
        ];
    }
} 