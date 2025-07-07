<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Thread;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Tenant-specific channel for threads
Broadcast::channel('tenant.{tenantId}.threads', function ($user, $tenantId) {
    // Check if user has access to this tenant
    return $user->tenants->contains('id', $tenantId);
});

// Channel for specific thread updates
Broadcast::channel('tenant.{tenantId}.thread.{threadId}', function ($user, $tenantId, $threadId) {
    // Check if user has access to this tenant and thread
    if (!$user->tenants->contains('id', $tenantId)) {
        return false;
    }

    $thread = Thread::find($threadId);
    
    // Check if thread exists and belongs to the tenant
    return $thread && (int) $thread->tenant_id === (int) $tenantId;
});

// Channel for agent-specific notifications
Broadcast::channel('tenant.{tenantId}.user.{userId}', function ($user, $tenantId, $userId) {
    // Check if user has access to this tenant
    if (!$user->tenants->contains('id', $tenantId)) {
        return false;
    }
    
    // Only allow the specific user to listen to their own channel
    return (int) $user->id === (int) $userId;
}); 