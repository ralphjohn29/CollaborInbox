<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    /**
     * Display a listing of agents with pagination and search.
     */
    public function index(Request $request)
    {
        // Check if user has permission to view agents
        if (!auth()->user()->hasPermission('view agents')) {
            abort(403, 'Unauthorized');
        }
        
        // Get query parameters
        $search = $request->query('search');
        $perPage = $request->query('per_page', 15);
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $roleFilter = $request->query('role');
        $statusFilter = $request->query('status');
        
        // Get agents query
        $query = User::query()
            ->with('role')
            ->where('tenant_id', auth()->user()->tenant_id);
            
        // Apply search filter if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter if provided
        if ($roleFilter) {
            $query->where('role_id', $roleFilter);
        }
        
        // Apply status filter if provided
        if ($statusFilter !== null) {
            $isActive = $statusFilter === 'active';
            $query->where('is_active', $isActive);
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortDir);
        
        // Get paginated results
        $agents = $query->paginate($perPage);
        
        // Return the Blade view with agents data
        return view('agent.index', compact('agents'));
    }

    /**
     * Store a newly created agent in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to create agents
        if (!auth()->user()->hasPermission('create agents')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Create the agent
            $agent = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'tenant_id' => auth()->user()->tenant_id,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Agent created successfully',
                'agent' => $agent->load('role'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create agent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified agent.
     */
    public function show(string $id)
    {
        // Check if user has permission to view agents
        if (!auth()->user()->hasPermission('view agents')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Find the agent
        $agent = User::with('role')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->find($id);
        
        if (!$agent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }
        
        // Get assigned threads count for the agent
        $assignedThreadsCount = $agent->assignedThreads()->count();
        
        return response()->json([
            'agent' => $agent,
            'stats' => [
                'assigned_threads_count' => $assignedThreadsCount,
            ],
        ]);
    }

    /**
     * Update the specified agent in storage.
     */
    public function update(Request $request, string $id)
    {
        // Check if user has permission to edit agents
        if (!auth()->user()->hasPermission('edit agents')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Find the agent
        $agent = User::where('tenant_id', auth()->user()->tenant_id)->find($id);
        
        if (!$agent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|string|min:8',
            'role_id' => 'sometimes|required|exists:roles,id',
            'is_active' => 'sometimes|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Update the agent's basic information
            $agent->fill($request->only(['name', 'email', 'role_id', 'is_active']));
            
            // Update password if provided
            if ($request->filled('password')) {
                $agent->password = Hash::make($request->password);
            }
            
            $agent->save();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Agent updated successfully',
                'agent' => $agent->load('role'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to update agent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle the active status of the specified agent.
     */
    public function toggleStatus(string $id)
    {
        // Check if user has permission to edit agents
        if (!auth()->user()->hasPermission('edit agents')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Find the agent
        $agent = User::where('tenant_id', auth()->user()->tenant_id)->find($id);
        
        if (!$agent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }
        
        // Toggle the active status
        $agent->is_active = !$agent->is_active;
        $agent->save();
        
        $statusText = $agent->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'message' => "Agent {$statusText} successfully",
            'agent' => $agent->load('role'),
        ]);
    }

    /**
     * Remove the specified agent from storage.
     */
    public function destroy(string $id)
    {
        // Check if user has permission to delete agents
        if (!auth()->user()->hasPermission('delete agents')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Find the agent
        $agent = User::where('tenant_id', auth()->user()->tenant_id)->find($id);
        
        if (!$agent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }
        
        // Check if the agent has assigned threads
        $assignedThreadsCount = $agent->assignedThreads()->count();
        
        if ($assignedThreadsCount > 0) {
            return response()->json([
                'message' => 'Agent cannot be deleted because they have ' . $assignedThreadsCount . ' assigned thread(s)',
                'assigned_threads_count' => $assignedThreadsCount,
            ], 422);
        }
        
        // Delete the agent
        $agent->delete();
        
        return response()->json(['message' => 'Agent deleted successfully']);
    }
    
    /**
     * Perform bulk operations on agents.
     */
    public function bulk(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|exists:users,id',
            'action' => 'required|string|in:activate,deactivate,delete,change_role',
            'role_id' => 'required_if:action,change_role|exists:roles,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        
        // Check permissions based on the action
        $permissionNeeded = 'edit agents';
        if ($request->action === 'delete') {
            $permissionNeeded = 'delete agents';
        }
        
        if (!auth()->user()->hasPermission($permissionNeeded)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Get the agents within the current tenant
        $agents = User::whereIn('id', $request->ids)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->get();
        
        if ($agents->count() === 0) {
            return response()->json(['message' => 'No valid agents found for the operation'], 404);
        }
        
        try {
            DB::beginTransaction();
            
            $processed = 0;
            $failed = 0;
            $messages = [];
            
            foreach ($agents as $agent) {
                try {
                    switch ($request->action) {
                        case 'activate':
                            $agent->is_active = true;
                            $agent->save();
                            $processed++;
                            break;
                            
                        case 'deactivate':
                            $agent->is_active = false;
                            $agent->save();
                            $processed++;
                            break;
                            
                        case 'delete':
                            // Check if the agent has assigned threads
                            $assignedThreadsCount = $agent->assignedThreads()->count();
                            
                            if ($assignedThreadsCount > 0) {
                                $failed++;
                                $messages[] = "Agent {$agent->name} has {$assignedThreadsCount} assigned thread(s) and cannot be deleted";
                                continue 2; // Use continue 2 to target the outer foreach loop
                            }
                            
                            $agent->delete();
                            $processed++;
                            break;
                            
                        case 'change_role':
                            $agent->role_id = $request->role_id;
                            $agent->save();
                            $processed++;
                            break;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $messages[] = "Failed to process agent {$agent->name}: {$e->getMessage()}";
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => "Bulk operation completed. Processed: {$processed}, Failed: {$failed}",
                'details' => $messages,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Bulk operation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
} 