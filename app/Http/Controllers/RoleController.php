<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles and permissions.
     */
    public function index()
    {
        // Check if user has permission to view roles
        if (!auth()->user()->hasPermission('view roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Get all roles with their permissions
        $roles = Role::with('permissions')->get();
        
        // Get all permissions
        $permissions = Permission::all();
        
        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to create roles
        if (!auth()->user()->hasPermission('create roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        
        // Create the role
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
            'description' => $request->description,
        ]);
        
        // Assign permissions if provided
        if ($request->has('permissions') && is_array($request->permissions)) {
            $role->permissions()->sync($request->permissions);
        }
        
        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role->load('permissions'),
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show(string $id)
    {
        // Check if user has permission to view roles
        if (!auth()->user()->hasPermission('view roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Find the role
        $role = Role::with('permissions')->find($id);
        
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        
        return response()->json(['role' => $role]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, string $id)
    {
        // Check if user has permission to edit roles
        if (!auth()->user()->hasPermission('edit roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Find the role
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
            'guard_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        
        // Update the role
        $role->update($request->only(['name', 'guard_name', 'description']));
        
        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(string $id)
    {
        // Check if user has permission to delete roles
        if (!auth()->user()->hasPermission('delete roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Find the role
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        
        // Check if the role is in use by any users
        $usersWithRole = User::where('role_id', $id)->count();
        
        if ($usersWithRole > 0) {
            return response()->json([
                'message' => 'Role cannot be deleted because it is assigned to ' . $usersWithRole . ' user(s)',
                'users_count' => $usersWithRole,
            ], 422);
        }
        
        // Delete the role
        $role->delete();
        
        return response()->json(['message' => 'Role deleted successfully']);
    }
    
    /**
     * Update the permissions for a role.
     */
    public function updatePermissions(Request $request, string $id)
    {
        // Check if user has permission to edit roles
        if (!auth()->user()->hasPermission('edit roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Find the role
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        
        // Sync the permissions
        $role->permissions()->sync($request->permissions);
        
        return response()->json([
            'message' => 'Role permissions updated successfully',
            'role' => $role->load('permissions'),
        ]);
    }
    
    /**
     * Assign a role to a user.
     */
    public function assignRoleToUser(Request $request, string $userId)
    {
        // Check if user has permission to edit users
        if (!auth()->user()->hasPermission('edit users')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Find the user
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        
        // Assign the role to the user
        $user->role_id = $request->role_id;
        $user->save();
        
        return response()->json([
            'message' => 'Role assigned to user successfully',
            'user' => $user->load('role'),
        ]);
    }
}
