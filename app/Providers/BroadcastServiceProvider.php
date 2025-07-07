<?php

namespace App\Providers;

use App\Models\Thread;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes(['middleware' => ['web', 'auth']]);

        require base_path('routes/channels.php');

        // Tenant channel
        Broadcast::channel('tenant.{tenantId}', function (User $user, int $tenantId) {
            // Verify the user belongs to this tenant
            return app(TenantService::class)->userBelongsToTenant($user->id, $tenantId);
        });

        // Tenant threads channel
        Broadcast::channel('tenant.{tenantId}.threads', function (User $user, int $tenantId) {
            // Verify the user belongs to this tenant
            return app(TenantService::class)->userBelongsToTenant($user->id, $tenantId);
        });

        // Individual thread channel
        Broadcast::channel('tenant.{tenantId}.thread.{threadId}', function (User $user, int $tenantId, int $threadId) {
            // First verify tenant access
            if (!app(TenantService::class)->userBelongsToTenant($user->id, $tenantId)) {
                return false;
            }

            // Then verify thread access
            $thread = Thread::findOrFail($threadId);
            return $thread->tenant_id === $tenantId;
        });

        // User-specific channel
        Broadcast::channel('tenant.{tenantId}.user.{userId}', function (User $user, int $tenantId, int $userId) {
            // Verify the user belongs to this tenant
            if (!app(TenantService::class)->userBelongsToTenant($user->id, $tenantId)) {
                return false;
            }

            // Only allow if requesting own channel
            return $user->id === (int) $userId;
        });
    }
} 