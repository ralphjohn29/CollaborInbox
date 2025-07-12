<?php

namespace App\Http\Controllers;

use App\Models\Disposition;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispositionDashboardController extends Controller
{
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
        $this->middleware('auth');
    }

    public function index()
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        // Get dispositions with email counts - if no tenant, show all
        $query = Disposition::query();
        
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }
        
        $dispositions = $query
            ->withCount('emails')
            ->withCount(['emails as unread_emails_count' => function ($query) {
                $query->where('status', 'unread');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Get statistics
        $stats = [
            'total_dispositions' => $dispositions->count(),
            'active_dispositions' => $dispositions->where('is_active', true)->count(),
            'total_emails' => $dispositions->sum('emails_count'),
            'unread_emails' => $dispositions->sum('unread_emails_count'),
        ];

        // Get recent activity
        $recentActivityQuery = DB::table('emails')
            ->join('dispositions', 'emails.disposition_id', '=', 'dispositions.id')
            ->whereNotNull('emails.disposition_id');
            
        if ($tenant) {
            $recentActivityQuery->where('dispositions.tenant_id', $tenant->id);
        }
        
        $recentActivity = $recentActivityQuery
            ->select(
                'emails.id',
                'emails.subject',
                'emails.from_email',
                'emails.created_at',
                'dispositions.name as disposition_name',
                'dispositions.color as disposition_color'
            )
            ->orderBy('emails.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dispositions.dashboard', compact('dispositions', 'stats', 'recentActivity', 'tenant'));
    }

    public function create()
    {
        return view('dispositions.form');
    }

    public function store(Request $request)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $disposition = Disposition::create([
            'tenant_id' => $tenant ? $tenant->id : null,
            'name' => $validated['name'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Disposition created successfully',
                'disposition' => $disposition,
            ]);
        }

        return redirect()->route('dispositions.dashboard')
            ->with('success', 'Disposition created successfully.');
    }

    public function edit($id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $query = Disposition::query();
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }
        
        $disposition = $query->findOrFail($id);

        return view('dispositions.form', compact('disposition'));
    }

    public function update(Request $request, $id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $query = Disposition::query();
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }
        
        $disposition = $query->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $disposition->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Disposition updated successfully',
                'disposition' => $disposition,
            ]);
        }

        return redirect()->route('dispositions.dashboard')
            ->with('success', 'Disposition updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $query = Disposition::query();
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }
        
        $disposition = $query->findOrFail($id);
        
        // Check if there are emails using this disposition
        if ($disposition->emails()->count() > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete disposition with associated emails.',
                ], 422);
            }
            
            return back()->withErrors(['error' => 'Cannot delete disposition with associated emails.']);
        }
        
        $disposition->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Disposition deleted successfully',
            ]);
        }

        return redirect()->route('dispositions.dashboard')
            ->with('success', 'Disposition deleted successfully.');
    }

    public function toggle(Request $request, $id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $query = Disposition::query();
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }
        
        $disposition = $query->findOrFail($id);
        
        $disposition->update(['is_active' => !$disposition->is_active]);

        return response()->json([
            'success' => true,
            'active' => $disposition->is_active,
            'message' => $disposition->is_active ? 'Disposition activated' : 'Disposition deactivated',
        ]);
    }

    public function reorder(Request $request)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $validated = $request->validate([
            'dispositions' => 'required|array',
            'dispositions.*' => 'integer|exists:dispositions,id',
        ]);

        DB::transaction(function () use ($validated, $tenant) {
            foreach ($validated['dispositions'] as $order => $id) {
                $query = Disposition::where('id', $id);
                
                if ($tenant) {
                    $query->where('tenant_id', $tenant->id);
                }
                
                $query->update(['sort_order' => $order]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Dispositions reordered successfully',
        ]);
    }
}
