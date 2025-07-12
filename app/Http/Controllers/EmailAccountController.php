<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use Illuminate\Http\Request;
use App\Services\TenantManager;

class EmailAccountController extends Controller
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

        $emailAccounts = EmailAccount::where('tenant_id', $tenant->id)
            ->orderBy('email_prefix')
            ->get();

        return view('inbox.settings.accounts', compact('emailAccounts'));
    }

    public function create()
    {
        return view('inbox.settings.accounts-form');
    }

    public function store(Request $request)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $request->validate([
            'email_prefix' => 'required|string|max:50',
            'display_name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'incoming_server_type' => 'required|in:imap,pop3',
            'incoming_server_host' => 'required|string',
            'incoming_server_port' => 'required|integer',
            'incoming_server_username' => 'required|string',
            'incoming_server_password' => 'required|string',
            'incoming_server_ssl' => 'boolean',
            'outgoing_server_host' => 'required|string',
            'outgoing_server_port' => 'required|integer',
            'outgoing_server_username' => 'required|string',
            'outgoing_server_password' => 'required|string',
            'outgoing_server_ssl' => 'boolean',
        ]);

        // Generate full email address
        $domain = explode('.', $tenant->domain)[0] . '.com'; // Or get from tenant settings
        $emailAddress = $request->email_prefix . '@' . $domain;

        // Check if email already exists
        if (EmailAccount::where('email_address', $emailAddress)->exists()) {
            return back()->withErrors(['email_prefix' => 'This email prefix is already in use.']);
        }

        EmailAccount::create([
            'tenant_id' => $tenant->id,
            'email_prefix' => $request->email_prefix,
            'email_address' => $emailAddress,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_active' => true,
            'incoming_server_type' => $request->incoming_server_type,
            'incoming_server_host' => $request->incoming_server_host,
            'incoming_server_port' => $request->incoming_server_port,
            'incoming_server_username' => $request->incoming_server_username,
            'incoming_server_password' => $request->incoming_server_password,
            'incoming_server_ssl' => $request->incoming_server_ssl ?? true,
            'outgoing_server_host' => $request->outgoing_server_host,
            'outgoing_server_port' => $request->outgoing_server_port,
            'outgoing_server_username' => $request->outgoing_server_username,
            'outgoing_server_password' => $request->outgoing_server_password,
            'outgoing_server_ssl' => $request->outgoing_server_ssl ?? true,
        ]);

        return redirect()->route('inbox.settings.accounts')->with('success', 'Email account created successfully.');
    }

    public function edit($id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $emailAccount = EmailAccount::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('inbox.settings.accounts-form', compact('emailAccount'));
    }

    public function update(Request $request, $id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $emailAccount = EmailAccount::where('tenant_id', $tenant->id)->findOrFail($id);
        
        $request->validate([
            'display_name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'incoming_server_type' => 'required|in:imap,pop3',
            'incoming_server_host' => 'required|string',
            'incoming_server_port' => 'required|integer',
            'incoming_server_username' => 'required|string',
            'incoming_server_password' => 'nullable|string',
            'incoming_server_ssl' => 'boolean',
            'outgoing_server_host' => 'required|string',
            'outgoing_server_port' => 'required|integer',
            'outgoing_server_username' => 'required|string',
            'outgoing_server_password' => 'nullable|string',
            'outgoing_server_ssl' => 'boolean',
        ]);

        $updateData = [
            'display_name' => $request->display_name,
            'description' => $request->description,
            'incoming_server_type' => $request->incoming_server_type,
            'incoming_server_host' => $request->incoming_server_host,
            'incoming_server_port' => $request->incoming_server_port,
            'incoming_server_username' => $request->incoming_server_username,
            'incoming_server_ssl' => $request->incoming_server_ssl ?? true,
            'outgoing_server_host' => $request->outgoing_server_host,
            'outgoing_server_port' => $request->outgoing_server_port,
            'outgoing_server_username' => $request->outgoing_server_username,
            'outgoing_server_ssl' => $request->outgoing_server_ssl ?? true,
        ];

        // Only update passwords if provided
        if ($request->filled('incoming_server_password')) {
            $updateData['incoming_server_password'] = $request->incoming_server_password;
        }
        if ($request->filled('outgoing_server_password')) {
            $updateData['outgoing_server_password'] = $request->outgoing_server_password;
        }

        $emailAccount->update($updateData);

        return redirect()->route('inbox.settings.accounts')->with('success', 'Email account updated successfully.');
    }

    public function destroy($id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $emailAccount = EmailAccount::where('tenant_id', $tenant->id)->findOrFail($id);
        
        // Check if there are emails associated
        if ($emailAccount->emails()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete email account with existing emails.']);
        }
        
        $emailAccount->delete();

        return redirect()->route('inbox.settings.accounts')->with('success', 'Email account deleted successfully.');
    }

    public function toggle($id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $emailAccount = EmailAccount::where('tenant_id', $tenant->id)->findOrFail($id);
        
        $emailAccount->update(['is_active' => !$emailAccount->is_active]);

        return response()->json(['active' => $emailAccount->is_active]);
    }
}
