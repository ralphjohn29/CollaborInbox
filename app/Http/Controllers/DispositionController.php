<?php

namespace App\Http\Controllers;

use App\Models\Disposition;
use Illuminate\Http\Request;
use App\Services\TenantManager;

class DispositionController extends Controller
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
        
        if (!$tenant) {
            abort(403, 'No tenant context');
        }

        $dispositions = Disposition::where('tenant_id', $tenant->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('inbox.settings.dispositions', compact('dispositions'));
    }

    public function create()
    {
        return view('inbox.settings.dispositions-form');
    }

    public function store(Request $request)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        Disposition::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('inbox.settings.dispositions')->with('success', 'Disposition created successfully.');
    }

    public function edit($id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $disposition = Disposition::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('inbox.settings.dispositions-form', compact('disposition'));
    }

    public function update(Request $request, $id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $disposition = Disposition::where('tenant_id', $tenant->id)->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $disposition->update([
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('inbox.settings.dispositions')->with('success', 'Disposition updated successfully.');
    }

    public function destroy($id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $disposition = Disposition::where('tenant_id', $tenant->id)->findOrFail($id);
        
        // Check if there are emails using this disposition
        if ($disposition->emails()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete disposition with associated emails.']);
        }
        
        $disposition->delete();

        return redirect()->route('inbox.settings.dispositions')->with('success', 'Disposition deleted successfully.');
    }

    public function toggle($id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $disposition = Disposition::where('tenant_id', $tenant->id)->findOrFail($id);
        
        $disposition->update(['is_active' => !$disposition->is_active]);

        return response()->json(['active' => $disposition->is_active]);
    }

    public function reorder(Request $request)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $request->validate([
            'dispositions' => 'required|array',
            'dispositions.*' => 'integer',
        ]);

        foreach ($request->dispositions as $order => $id) {
            Disposition::where('tenant_id', $tenant->id)
                ->where('id', $id)
                ->update(['sort_order' => $order]);
        }

        return response()->json(['success' => true]);
    }
}
