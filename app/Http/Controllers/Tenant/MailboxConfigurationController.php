<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller; // Import base controller
use App\Models\Tenant\MailboxConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MailboxConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Implement pagination
        $mailboxes = MailboxConfiguration::all();
        // Avoid returning encrypted password in list view
        $mailboxes->makeHidden('encrypted_password'); 
        return response()->json($mailboxes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: Add more specific validation (e.g., valid encryption types)
        $validatedData = $request->validate([
            'email_address' => 'required|email|max:255|unique:mailbox_configurations,email_address', // Unique within this tenant DB
            'imap_server' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'encryption_type' => 'required|string|in:ssl,tls,none',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:1', // Validate plain password, model will encrypt
            'folder_to_monitor' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|in:active,inactive',
        ]);

        try {
            $mailbox = MailboxConfiguration::create([
                'email_address' => $validatedData['email_address'],
                'imap_server' => $validatedData['imap_server'],
                'port' => $validatedData['port'],
                'encryption_type' => $validatedData['encryption_type'],
                'username' => $validatedData['username'],
                'encrypted_password' => $validatedData['password'], // Pass plain text, mutator handles encryption
                'folder_to_monitor' => $validatedData['folder_to_monitor'] ?? 'INBOX',
                'status' => $validatedData['status'] ?? 'active',
            ]);

            Log::info('Mailbox configuration created', ['tenant_id' => tenant('id'), 'mailbox_id' => $mailbox->id]);
            $mailbox->makeHidden('encrypted_password'); // Don't return password
            return response()->json($mailbox, 201);

        } catch (\Exception $e) {
            Log::error('Failed to create mailbox configuration', [
                'tenant_id' => tenant('id'), 
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Include stack trace for debugging
            ]);
            return response()->json(['message' => 'Failed to create mailbox configuration.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MailboxConfiguration $mailboxConfiguration)
    {
        // Avoid returning encrypted password
        $mailboxConfiguration->makeHidden('encrypted_password'); 
        return response()->json($mailboxConfiguration);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MailboxConfiguration $mailboxConfiguration)
    {
         // TODO: Add more specific validation
         $validatedData = $request->validate([
            'email_address' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('mailbox_configurations')->ignore($mailboxConfiguration->id)],
            'imap_server' => 'sometimes|required|string|max:255',
            'port' => 'sometimes|required|integer|min:1|max:65535',
            'encryption_type' => 'sometimes|required|string|in:ssl,tls,none',
            'username' => 'sometimes|required|string|max:255',
            'password' => 'nullable|string|min:1', // Allow password update (optional)
            'folder_to_monitor' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|in:active,inactive,error',
            'last_error' => 'nullable|string',
        ]);

        try {
            // Prepare update data, handle password separately due to encryption
            $updateData = $validatedData;
            if (!empty($validatedData['password'])) {
                // If password is provided, let the mutator handle encryption
                $updateData['encrypted_password'] = $validatedData['password'];
            }
            unset($updateData['password']); // Remove plain password key

            $mailboxConfiguration->update($updateData);

            Log::info('Mailbox configuration updated', ['tenant_id' => tenant('id'), 'mailbox_id' => $mailboxConfiguration->id]);
            $mailboxConfiguration->makeHidden('encrypted_password'); // Don't return password
            return response()->json($mailboxConfiguration);

        } catch (\Exception $e) {
             Log::error('Failed to update mailbox configuration', [
                'tenant_id' => tenant('id'), 
                'mailbox_id' => $mailboxConfiguration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to update mailbox configuration.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MailboxConfiguration $mailboxConfiguration)
    {
        try {
            $mailboxId = $mailboxConfiguration->id;
            $mailboxConfiguration->delete();
            Log::info('Mailbox configuration deleted', ['tenant_id' => tenant('id'), 'mailbox_id' => $mailboxId]);
            return response()->json(null, 204); // No content on successful delete
        } catch (\Exception $e) {
            Log::error('Failed to delete mailbox configuration', [
                'tenant_id' => tenant('id'), 
                'mailbox_id' => $mailboxConfiguration->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to delete mailbox configuration.'], 500);
        }
    }
    
    // TODO: Add method to test connection?
    // public function testConnection(MailboxConfiguration $mailboxConfiguration) { ... }
} 