<?php

namespace App\Services;

use App\Models\Thread;
use App\Models\Message;
use App\Models\User;
use App\Events\ThreadUpdatedEvent;
use App\Events\ThreadAssignedEvent;
use App\Events\NewMessageEvent;
use Illuminate\Support\Facades\Log;

class BroadcastService
{
    /**
     * Broadcast a thread update event.
     *
     * @param Thread $thread
     * @param string $action The action performed (e.g., 'created', 'updated', 'deleted')
     * @param array $additionalData Any additional data to include in the broadcast
     * @return void
     */
    public function broadcastThreadUpdate(Thread $thread, string $action, array $additionalData = [])
    {
        event(new ThreadUpdatedEvent(
            $thread,
            $thread->tenant_id,
            $action,
            $additionalData
        ));
    }

    /**
     * Broadcast a thread assignment event.
     *
     * @param Thread $thread
     * @param User|null $assignedTo The user being assigned to the thread
     * @param User|null $assignedBy The user who made the assignment
     * @return void
     */
    public function broadcastThreadAssignment(Thread $thread, ?User $assignedTo = null, ?User $assignedBy = null)
    {
        event(new ThreadAssignedEvent(
            $thread,
            $assignedTo,
            $assignedBy,
            $thread->tenant_id
        ));
    }

    /**
     * Broadcast a new message event.
     *
     * @param Message $message
     * @return void
     */
    public function broadcastNewMessage(Message $message)
    {
        event(new NewMessageEvent(
            $message,
            $message->tenant_id
        ));
    }

    /**
     * Get the channel name for broadcasting thread updates
     *
     * @param int $tenantId
     * @return string
     */
    public static function getThreadsChannel(int $tenantId): string
    {
        return "tenant.{$tenantId}.threads";
    }

    /**
     * Get the channel name for a specific thread
     *
     * @param int $tenantId
     * @param int $threadId
     * @return string
     */
    public static function getThreadChannel(int $tenantId, int $threadId): string
    {
        return "tenant.{$tenantId}.thread.{$threadId}";
    }

    /**
     * Get the channel name for a specific user's notifications
     *
     * @param int $tenantId
     * @param int $userId
     * @return string
     */
    public static function getUserChannel(int $tenantId, int $userId): string
    {
        return "tenant.{$tenantId}.user.{$userId}";
    }

    /**
     * Get all channels that should be notified about a thread update
     *
     * @param Thread $thread
     * @return array
     */
    public static function getThreadBroadcastChannels(Thread $thread): array
    {
        $channels = [
            self::getThreadsChannel($thread->tenant_id),
            self::getThreadChannel($thread->tenant_id, $thread->id)
        ];

        try {
            // Add channels for users who should be notified about this thread
            if ($thread->assigned_to) {
                $channels[] = self::getUserChannel($thread->tenant_id, $thread->assigned_to);
            }
        } catch (\Exception $e) {
            Log::error('Error getting broadcast channels', [
                'error' => $e->getMessage(),
                'thread_id' => $thread->id
            ]);
        }

        return $channels;
    }
} 