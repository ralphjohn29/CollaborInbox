<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    /**
     * Get the users that have this role
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the permissions associated with this role
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Assign a permission to this role
     * 
     * @param Permission|int $permission
     * @return $this
     */
    public function givePermissionTo($permission)
    {
        if (is_numeric($permission)) {
            $permission = Permission::findOrFail($permission);
        }

        $this->permissions()->syncWithoutDetaching([$permission->id]);
        
        return $this;
    }

    /**
     * Remove a permission from this role
     * 
     * @param Permission|int $permission
     * @return $this
     */
    public function revokePermissionTo($permission)
    {
        if (is_numeric($permission)) {
            $permission = Permission::findOrFail($permission);
        }

        $this->permissions()->detach($permission->id);
        
        return $this;
    }

    /**
     * Check if this role has the specified permission
     * 
     * @param string|Permission $permission
     * @return bool
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions->contains('name', $permission);
        }
        
        return $this->permissions->contains('id', $permission->id);
    }
}
