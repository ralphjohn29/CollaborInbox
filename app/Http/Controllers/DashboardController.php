<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $tenantManager;
    
    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }
    
    /**
     * Show the appropriate dashboard based on tenant context
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get the current tenant
        $tenant = $this->tenantManager->getCurrentTenant();
        
        // Log debug information
        Log::debug('Dashboard request', [
            'has_tenant' => $tenant ? 'yes' : 'no',
            'tenant_id' => $tenant ? $tenant->id : null,
            'host' => $request->getHost(),
            'user' => Auth::user() ? Auth::user()->id : 'guest'
        ]);
        
        // If we have a tenant context, use the tenant dashboard
        if ($tenant) {
            Log::debug('Using tenant dashboard');
            return view('tenant.dashboard', [
                'tenant' => $tenant
            ]);
        }
        
        // Otherwise use the regular dashboard
        Log::debug('Using regular dashboard');
        return view('dashboard');
    }
} 