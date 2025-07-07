<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantService
{
    /**
     * Check if a user belongs to a tenant
     *
     * @param int $userId
     * @param int $tenantId
     * @return bool
     */
    public function userBelongsToTenant(int $userId, int $tenantId): bool
    {
        try {
            // Check if the user exists
            $user = User::find($userId);
            if (!$user) {
                Log::warning("User not found when checking tenant access", [
                    'user_id' => $userId,
                    'tenant_id' => $tenantId
                ]);
                return false;
            }

            // First check if the user's tenant_id directly matches
            if ($user->tenant_id === $tenantId) {
                return true;
            }

            // If not, check if the user has an entry in the tenant_user pivot table
            $exists = DB::table('tenant_user')
                ->where('user_id', $userId)
                ->where('tenant_id', $tenantId)
                ->exists();

            if ($exists) {
                Log::debug("User has access to tenant via pivot table", [
                    'user_id' => $userId,
                    'tenant_id' => $tenantId
                ]);
            }

            return $exists;
        } catch (\Exception $e) {
            Log::error("Error checking if user belongs to tenant", [
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get all tenants a user has access to
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTenants(int $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return collect();
        }

        // If user has direct tenant_id, include that
        $tenants = collect();
        if ($user->tenant_id) {
            $tenants = Tenant::where('id', $user->tenant_id)->get();
        }

        // Get tenants from pivot table
        $pivotTenants = Tenant::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->get();

        // Merge the collections and remove duplicates
        return $tenants->merge($pivotTenants)->unique('id');
    }
} 