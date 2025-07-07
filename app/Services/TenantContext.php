<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class TenantContext
{
    /**
     * The current tenant instance.
     *
     * @var \App\Models\Tenant|null
     */
    protected static ?Tenant $currentTenant = null;

    /**
     * Set the current tenant.
     *
     * @param  \App\Models\Tenant|null  $tenant
     * @return void
     */
    public static function setTenant(?Tenant $tenant): void
    {
        static::$currentTenant = $tenant;
        Log::debug('Tenant context set', ['tenant_id' => $tenant?->id, 'tenant_name' => $tenant?->name]);
    }

    /**
     * Get the current tenant.
     *
     * @return \App\Models\Tenant|null
     */
    public static function getTenant(): ?Tenant
    {
        return static::$currentTenant;
    }

    /**
     * Check if a tenant is set.
     *
     * @return bool
     */
    public static function hasTenant(): bool
    {
        return static::$currentTenant !== null;
    }

    /**
     * Get the current tenant ID.
     *
     * @return string|null
     */
    public static function getTenantId(): ?string
    {
        return static::$currentTenant?->id;
    }

    /**
     * Clear the current tenant context.
     *
     * @return void
     */
    public static function clear(): void
    {
        static::$currentTenant = null;
        Log::debug('Tenant context cleared');
    }
} 