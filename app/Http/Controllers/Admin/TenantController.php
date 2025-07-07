<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of the tenants.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tenants = Tenant::latest()->paginate(10);
        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.tenants.create');
    }

    /**
     * Store a newly created tenant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => [
                'required', 
                'string',
                'alpha_dash', 
                'max:50',
                'min:3',
                Rule::unique('tenants', 'subdomain')
            ],
            'status' => 'required|in:active,inactive',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'database_name' => 'required|string|max:64|unique:tenants,database_name',
        ]);

        try {
            DB::beginTransaction();

            // Create the tenant record
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'subdomain' => $validated['subdomain'],
                'status' => $validated['status'],
                'database_name' => $validated['database_name'],
            ]);

            // Create tenant database
            $success = $this->tenantManager->createTenantDatabase($tenant);
            
            if (!$success) {
                throw new \Exception("Failed to create tenant database");
            }

            // Run migrations for the tenant database
            $this->tenantManager->migrateTenantDatabase($tenant);

            // Create admin user for the tenant
            $adminUser = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'tenant_id' => $tenant->id,
                'role' => 'admin',
            ]);

            DB::commit();

            return redirect()->route('tenants.index')
                ->with('success', "Tenant '{$tenant->name}' has been created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tenant creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create tenant: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified tenant.
     *
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function edit(Tenant $tenant)
    {
        // Get the admin user of this tenant
        $adminUser = User::where('tenant_id', $tenant->id)
                        ->where('role', 'admin')
                        ->first();
                        
        return view('admin.tenants.edit', compact('tenant', 'adminUser'));
    }

    /**
     * Update the specified tenant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => [
                'required', 
                'string', 
                'alpha_dash', 
                'max:50', 
                'min:3',
                Rule::unique('tenants', 'subdomain')->ignore($tenant->id)
            ],
            'status' => 'required|in:active,inactive',
            'admin_name' => 'required|string|max:255',
            'admin_email' => [
                'required', 
                'email', 
                'max:255',
                Rule::unique('users', 'email')->ignore($request->admin_user_id)
            ],
            'admin_password' => 'nullable|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            // Update tenant
            $tenant->update([
                'name' => $validated['name'],
                'subdomain' => $validated['subdomain'],
                'status' => $validated['status'],
            ]);

            // Update admin user
            $adminUser = User::findOrFail($request->admin_user_id);
            
            $adminUser->name = $validated['admin_name'];
            $adminUser->email = $validated['admin_email'];
            
            if (!empty($validated['admin_password'])) {
                $adminUser->password = Hash::make($validated['admin_password']);
            }
            
            $adminUser->save();

            DB::commit();

            return redirect()->route('tenants.index')
                ->with('success', "Tenant '{$tenant->name}' has been updated successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tenant update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update tenant: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of the specified tenant.
     *
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(Tenant $tenant)
    {
        $newStatus = $tenant->status === 'active' ? 'inactive' : 'active';
        
        $tenant->update([
            'status' => $newStatus
        ]);

        $statusMessage = $newStatus === 'active' ? 'activated' : 'deactivated';
        
        return redirect()->route('tenants.index')
            ->with('success', "Tenant '{$tenant->name}' has been {$statusMessage}");
    }

    /**
     * Remove the specified tenant from storage.
     *
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tenant $tenant)
    {
        try {
            // Get tenant name before deletion for the success message
            $tenantName = $tenant->name;
            
            // Drop the tenant database
            $this->tenantManager->dropTenantDatabase($tenant);
            
            // Delete all users associated with this tenant
            User::where('tenant_id', $tenant->id)->delete();
            
            // Delete the tenant
            $tenant->delete();
            
            return redirect()->route('tenants.index')
                ->with('success', "Tenant '{$tenantName}' has been deleted successfully");
        } catch (\Exception $e) {
            Log::error('Tenant deletion failed: ' . $e->getMessage());
            
            return redirect()->route('tenants.index')
                ->with('error', 'Failed to delete tenant: ' . $e->getMessage());
        }
    }
} 