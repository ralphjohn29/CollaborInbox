<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\DeleteDatabase;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants
     */
    public function index()
    {
        $tenants = Tenant::latest()->paginate(10);
        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        return view('admin.tenants.create');
    }

    /**
     * Store a newly created tenant in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:50|regex:/^[a-z0-9-]+$/|unique:tenants',
            'database' => 'required|string|max:50|regex:/^[a-z0-9_]+$/|unique:tenants',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|string|email|max:255',
            'admin_password' => 'required|string|min:8',
        ], [
            'domain.regex' => 'The subdomain may only contain lowercase letters, numbers, and hyphens.',
            'database.regex' => 'The database name may only contain lowercase letters, numbers, and underscores.',
        ]);

        DB::beginTransaction();

        try {
            // Create new tenant
            $tenant = Tenant::create([
                'name' => $request->name,
                'domain' => $request->domain,
                'database' => $request->database,
                'status' => 'active',
            ]);

            // Run tenant setup process
            $this->setupTenantDatabase($tenant);
            $this->createTenantAdmin($tenant, $request);

            DB::commit();

            return redirect()->route('tenants.index')
                ->with('success', "Tenant '{$tenant->name}' created successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tenant creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create tenant: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant)
    {
        // Get admin user for this tenant
        $admin = $this->getTenantAdmin($tenant);
        
        return view('admin.tenants.edit', compact('tenant', 'admin'));
    }

    /**
     * Update the specified tenant in storage
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|string|email|max:255',
            'admin_password' => 'nullable|string|min:8',
        ]);

        DB::beginTransaction();

        try {
            // Update tenant
            $tenant->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            // Update tenant admin
            $this->updateTenantAdmin($tenant, $request);

            DB::commit();

            return redirect()->route('tenants.index')
                ->with('success', "Tenant '{$tenant->name}' updated successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tenant update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'tenant_id' => $tenant->id,
                'request' => $request->all()
            ]);
            
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update tenant: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified tenant from storage
     */
    public function destroy(Tenant $tenant)
    {
        try {
            $tenantName = $tenant->name;
            
            // Delete the tenant which should trigger cascading deletes
            $tenant->delete();
            
            // Additional cleanup could happen here
            
            return redirect()->route('tenants.index')
                ->with('success', "Tenant '{$tenantName}' deleted successfully.");
        } catch (\Exception $e) {
            Log::error('Tenant deletion failed: ' . $e->getMessage(), [
                'exception' => $e,
                'tenant_id' => $tenant->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to delete tenant: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of a tenant between active and inactive
     */
    public function toggleStatus(Tenant $tenant)
    {
        try {
            // Toggle the status
            $newStatus = $tenant->status === 'active' ? 'inactive' : 'active';
            $tenant->status = $newStatus;
            $tenant->save();
            
            Log::info("Tenant status toggled", [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'old_status' => ($newStatus === 'active' ? 'inactive' : 'active'),
                'new_status' => $newStatus
            ]);
            
            return redirect()->route('tenants.index')
                ->with('success', "Tenant '{$tenant->name}' is now " . ucfirst($newStatus));
        } catch (\Exception $e) {
            Log::error('Failed to toggle tenant status: ' . $e->getMessage(), [
                'exception' => $e,
                'tenant_id' => $tenant->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update tenant status: ' . $e->getMessage());
        }
    }

    /**
     * Set up the tenant's database
     */
    private function setupTenantDatabase(Tenant $tenant)
    {
        // This would typically create the tenant's database and run migrations
        // For now, let's assume it's handled by a tenant setup service or event
        
        // Example placeholder:
        Log::info("Setting up database for tenant: {$tenant->name}", [
            'tenant_id' => $tenant->id,
            'database' => $tenant->database
        ]);
    }

    /**
     * Create the admin user for the tenant
     */
    private function createTenantAdmin(Tenant $tenant, Request $request)
    {
        // This would create the admin user in the tenant's database
        // For now, let's create an admin user record
        
        $admin = new User([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'tenant_id' => $tenant->id,
            'is_admin' => true
        ]);
        
        $admin->save();
        
        Log::info("Created admin user for tenant: {$tenant->name}", [
            'tenant_id' => $tenant->id,
            'admin_id' => $admin->id
        ]);
        
        return $admin;
    }

    /**
     * Update the admin user for the tenant
     */
    private function updateTenantAdmin(Tenant $tenant, Request $request)
    {
        $admin = $this->getTenantAdmin($tenant);
        
        if (!$admin) {
            // Create admin if it doesn't exist
            return $this->createTenantAdmin($tenant, $request);
        }
        
        // Update admin details
        $admin->name = $request->admin_name;
        $admin->email = $request->admin_email;
        
        // Only update password if provided
        if ($request->filled('admin_password')) {
            $admin->password = Hash::make($request->admin_password);
        }
        
        $admin->save();
        
        Log::info("Updated admin user for tenant: {$tenant->name}", [
            'tenant_id' => $tenant->id,
            'admin_id' => $admin->id
        ]);
        
        return $admin;
    }

    /**
     * Get the admin user for the tenant
     */
    private function getTenantAdmin(Tenant $tenant)
    {
        return User::where('tenant_id', $tenant->id)
            ->where('is_admin', true)
            ->first();
    }

    // TODO: Add methods for activation/deactivation if needed
    // public function activate(Tenant $tenant) { ... }
    // public function deactivate(Tenant $tenant) { ... }
} 