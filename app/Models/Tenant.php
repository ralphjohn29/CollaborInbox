<?php

namespace App\Models;

use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model implements TenantContract
{
    use HasDomains, HasFactory;

    protected $fillable = [
        'name',
        'database',
        'status',
        'settings',
        'is_active',
        'id',
        'tenancy_db_name',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users associated with this tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the admin users for this tenant.
     */
    public function adminUsers()
    {
        return $this->users()->where('is_admin', true);
    }

    /**
     * Determine if the tenant is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active' || $this->is_active;
    }

    /**
     * Get the status attribute (maintains backward compatibility)
     */
    public function getStatusAttribute()
    {
        if (isset($this->attributes['status'])) {
            return $this->attributes['status'];
        }

        return $this->is_active ? 'active' : 'inactive';
    }

    /**
     * Set the status attribute (maintains backward compatibility)
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;

        // Also update is_active for backward compatibility
        if (array_key_exists('is_active', $this->attributes)) {
            $this->attributes['is_active'] = ($value === 'active');
        }
    }

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public function getTenantDatabaseName(): ?string
    {
        return $this->tenancy_db_name;
    }

    /**
     * Get internal data.
     *
     * @param string $key
     * @return mixed
     */
    public function getInternal(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Set internal data.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setInternal(string $key, $value)
    {
        $this->setAttribute($key, $value);
        return $this;
    }

    /**
     * Run a callback in this tenant's context.
     *
     * @param callable $callback
     * @return mixed
     */
    public function run(callable $callback)
    {
        return tenancy()->run($callback, $this);
    }
}
