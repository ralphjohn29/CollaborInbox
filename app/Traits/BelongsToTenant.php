<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;

trait BelongsToTenant
{
    /**
     * Boot the trait
     */
    public static function bootBelongsToTenant(): void
    {
        // Set tenant_id on model creation
        static::creating(function (Model $model) {
            if (!$model->tenant_id && tenant()) {
                $model->tenant_id = tenant()->id;
            }
        });
        
        // Always apply the tenant scope when querying the model
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (tenant()) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', tenant()->id);
            }
        });
        
        // Register an event for when tenant is switched
        if (method_exists(static::class, 'tenantSwitched')) {
            \Event::listen(\Stancl\Tenancy\Events\TenancyInitialized::class, function ($event) {
                static::tenantSwitched($event->tenancy->tenant);
            });
        }
    }
    
    /**
     * Get the current tenant
     */
    public function tenant()
    {
        return $this->belongsTo(config('tenancy.tenant_model', 'App\\Models\\Tenant'));
    }
    
    /**
     * Create a new instance without tenant scoping
     *
     * @param array $attributes
     * @return static
     */
    public static function createWithoutTenancy(array $attributes = [])
    {
        // Create a model instance without the global tenant scope
        $instance = new static($attributes);
        $instance->setConnection('system'); // Use the main connection, not the tenant connection
        $instance->save();
        
        return $instance;
    }
    
    /**
     * Query without tenant scoping
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutTenancy()
    {
        return static::withoutGlobalScope('tenant');
    }
    
    /**
     * Scope a query to a specific tenant
     *
     * @param Builder $query
     * @param string $tenantId 
     * @return Builder
     */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
    
    /**
     * Check if model belongs to the current tenant
     *
     * @return bool
     */
    public function belongsToCurrentTenant(): bool
    {
        return tenant() && $this->tenant_id === tenant()->id;
    }
    
    /**
     * Get all models across all tenants (admin function)
     * WARNING: Use with caution - this breaks tenant isolation
     *
     * @return Builder
     */
    public static function allTenants(): Builder
    {
        return static::withoutTenancy()->setConnection('system');
    }
    
    /**
     * Get the name of the "tenant_id" column.
     *
     * @return string
     */
    public function getTenantIdColumn(): string
    {
        return 'tenant_id';
    }
    
    /**
     * Get the qualified name of the "tenant_id" column.
     * 
     * @return string
     */
    public function getQualifiedTenantIdColumn(): string
    {
        return $this->qualifyColumn($this->getTenantIdColumn());
    }
} 