<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Illuminate\Cache\CacheManager::class]->forget('permissions');
        app()[\Illuminate\Cache\CacheManager::class]->forget('roles');

        // Create permissions
        $permissions = [
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Role permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            
            // Thread permissions
            'view threads',
            'create threads',
            'edit threads',
            'delete threads',
            'assign threads',
            
            // Message permissions
            'view messages',
            'create messages',
            'edit messages',
            'delete messages',
            
            // Note permissions
            'view notes',
            'create notes',
            'edit notes',
            'delete notes',
            
            // Mailbox configuration permissions
            'view mailboxes',
            'create mailboxes',
            'edit mailboxes',
            'delete mailboxes',
            'test mailboxes',
        ];

        $createdPermissions = [];

        foreach ($permissions as $permission) {
            $createdPermissions[] = Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
                'description' => 'Allows user to ' . $permission,
            ]);
        }

        // Create roles and assign permissions
        
        // Admin role
        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
            'description' => 'Administrator with full access to all features',
        ]);
        foreach ($createdPermissions as $permission) {
            $adminRole->givePermissionTo($permission);
        }
        
        // Team Lead role
        $teamLeadRole = Role::create([
            'name' => 'team_lead',
            'guard_name' => 'web',
            'description' => 'Team lead with access to manage agents and threads',
        ]);
        
        $teamLeadPermissions = [
            'view users',
            'edit users',
            'view roles',
            'view threads',
            'create threads',
            'edit threads',
            'assign threads',
            'view messages',
            'create messages',
            'edit messages',
            'view notes',
            'create notes',
            'edit notes',
            'delete notes',
            'view mailboxes',
        ];
        
        foreach ($teamLeadPermissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                $teamLeadRole->givePermissionTo($permission);
            }
        }
        
        // Agent role
        $agentRole = Role::create([
            'name' => 'agent',
            'guard_name' => 'web',
            'description' => 'Agent with access to handle inbox threads and messages',
        ]);
        
        $agentPermissions = [
            'view threads',
            'view messages',
            'create messages',
            'view notes',
            'create notes',
            'edit notes',
        ];
        
        foreach ($agentPermissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                $agentRole->givePermissionTo($permission);
            }
        }
    }
}
