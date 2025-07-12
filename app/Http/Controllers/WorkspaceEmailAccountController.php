<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use App\Services\EmailProviders\EmailProviderConfig;
use App\Services\EmailProviders\ImapEmailFetcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;

class WorkspaceEmailAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the email setup form.
     */
    public function create()
    {
        return view('inbox.settings.email-setup');
    }

    /**
     * Store a new email account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email_address' => 'required|email',
            'incoming_server_host' => 'required',
            'incoming_server_port' => 'required|integer',
            'incoming_server_username' => 'required',
            'incoming_server_password' => 'required',
            'incoming_server_ssl' => 'boolean',
            'outgoing_server_host' => 'required',
            'outgoing_server_port' => 'required|integer',
            'outgoing_server_ssl' => 'boolean',
        ]);

        // Get the current workspace
        $workspaceId = session('workspace_id') ?? Auth::user()->workspaces()->first()->id;

        // Create the email account
        $emailAccount = EmailAccount::create([
            'workspace_id' => $workspaceId,
            'email_address' => $validated['email_address'],
            'provider' => $this->detectProviderFromEmail($validated['email_address']),
            'incoming_server_type' => 'imap',
            'incoming_server_host' => $validated['incoming_server_host'],
            'incoming_server_port' => $validated['incoming_server_port'],
            'incoming_server_username' => $validated['incoming_server_username'],
            'incoming_server_password' => Crypt::encryptString($validated['incoming_server_password']),
            'incoming_server_ssl' => $validated['incoming_server_ssl'] ?? true,
            'outgoing_server_type' => 'smtp',
            'outgoing_server_host' => $validated['outgoing_server_host'],
            'outgoing_server_port' => $validated['outgoing_server_port'],
            'outgoing_server_username' => $validated['incoming_server_username'], // Usually same as incoming
            'outgoing_server_password' => Crypt::encryptString($validated['incoming_server_password']),
            'outgoing_server_ssl' => $validated['outgoing_server_ssl'] ?? true,
            'is_active' => true,
        ]);

        // Fetch initial emails
        try {
            $emailFetcher = new ImapEmailFetcher($emailAccount);
            $emailFetcher->connect()->fetchNewEmails(20);
        } catch (\Exception $e) {
            Log::error('Failed to fetch initial emails', [
                'email_account_id' => $emailAccount->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('inbox.index')
            ->with('success', 'Email account connected successfully!');
    }

    /**
     * Detect email provider from email address.
     */
    public function detectProvider(Request $request)
    {
        $email = $request->input('email');
        $domain = substr(strrchr($email, "@"), 1);
        
        $providerMap = [
            'gmail.com' => 'gmail',
            'googlemail.com' => 'gmail',
            'outlook.com' => 'outlook',
            'hotmail.com' => 'outlook',
            'live.com' => 'outlook',
            'msn.com' => 'outlook',
            'yahoo.com' => 'yahoo',
            'yahoo.co.uk' => 'yahoo',
            'icloud.com' => 'icloud',
            'me.com' => 'icloud',
            'mac.com' => 'icloud',
            'protonmail.com' => 'protonmail',
            'pm.me' => 'protonmail',
        ];

        $providerId = $providerMap[$domain] ?? null;
        
        if ($providerId) {
            $providerConfig = new EmailProviderConfig();
            $config = $providerConfig->getProviderConfig($providerId);
            
            $response = [
                'provider' => [
                    'id' => $providerId,
                    'name' => $config['name'],
                    'app_password_required' => $config['app_password_required'],
                    'instructions' => $config['app_password_instructions'] ?? '',
                    'imap' => $config['imap'],
                    'smtp' => $config['smtp'],
                ]
            ];
        } else {
            // Return generic IMAP/SMTP settings
            $response = [
                'provider' => [
                    'id' => 'generic',
                    'name' => 'Email Provider',
                    'app_password_required' => false,
                    'instructions' => 'Enter your email password',
                    'imap' => [
                        'host' => "imap.$domain",
                        'port' => 993,
                        'encryption' => 'ssl'
                    ],
                    'smtp' => [
                        'host' => "smtp.$domain",
                        'port' => 587,
                        'encryption' => 'tls'
                    ],
                ]
            ];
        }

        return response()->json($response);
    }

    /**
     * Test email connection.
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'email_address' => 'required|email',
            'incoming_server_host' => 'required',
            'incoming_server_port' => 'required|integer',
            'incoming_server_username' => 'required',
            'incoming_server_password' => 'required',
            'incoming_server_ssl' => 'boolean',
        ]);

        try {
            // Test IMAP connection
            $cm = new ClientManager();
            
            $client = $cm->make([
                'host'          => $validated['incoming_server_host'],
                'port'          => $validated['incoming_server_port'],
                'username'      => $validated['incoming_server_username'],
                'password'      => $validated['incoming_server_password'],
                'encryption'    => $validated['incoming_server_ssl'] ? 'ssl' : false,
                'validate_cert' => true,
                'protocol'      => 'imap'
            ]);

            // Try to connect
            $client->connect();
            
            // Get folder list to verify connection
            $folders = $client->getFolders();
            
            $client->disconnect();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
                'folders_count' => count($folders)
            ]);

        } catch (\Exception $e) {
            Log::error('Email connection test failed', [
                'error' => $e->getMessage(),
                'host' => $validated['incoming_server_host']
            ]);

            return response()->json([
                'success' => false,
                'error' => $this->getReadableError($e->getMessage())
            ], 422);
        }
    }

    /**
     * Convert technical errors to user-friendly messages.
     */
    protected function getReadableError($error)
    {
        if (str_contains($error, 'AUTHENTICATIONFAILED')) {
            return 'Invalid email or password. For Gmail/Outlook, you may need an app password.';
        }
        
        if (str_contains($error, 'Host not found') || str_contains($error, 'Connection refused')) {
            return 'Cannot connect to email server. Please check the server settings.';
        }
        
        if (str_contains($error, 'certificate')) {
            return 'SSL certificate error. Try disabling SSL/TLS or check your server settings.';
        }

        return 'Connection failed: ' . $error;
    }

    /**
     * Detect provider from email domain.
     */
    private function detectProviderFromEmail($email)
    {
        $domain = substr(strrchr($email, "@"), 1);
        
        $providerMap = [
            'gmail.com' => 'gmail',
            'googlemail.com' => 'gmail',
            'outlook.com' => 'outlook',
            'hotmail.com' => 'outlook',
            'live.com' => 'outlook',
            'yahoo.com' => 'yahoo',
            'icloud.com' => 'icloud',
            'me.com' => 'icloud',
        ];

        return $providerMap[$domain] ?? 'other';
    }

    /**
     * List all email accounts for the workspace.
     */
    public function index()
    {
        $workspaceId = session('workspace_id') ?? Auth::user()->workspaces()->first()->id;
        
        $emailAccounts = EmailAccount::where('workspace_id', $workspaceId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('inbox.settings.email-accounts', compact('emailAccounts'));
    }

    /**
     * Toggle email account active status.
     */
    public function toggle(EmailAccount $emailAccount)
    {
        // Check workspace ownership
        $workspaceId = session('workspace_id') ?? Auth::user()->workspaces()->first()->id;
        if ($emailAccount->workspace_id !== $workspaceId) {
            abort(403);
        }

        $emailAccount->update([
            'is_active' => !$emailAccount->is_active
        ]);

        return back()->with('success', 'Email account status updated.');
    }

    /**
     * Delete an email account.
     */
    public function destroy(EmailAccount $emailAccount)
    {
        // Check workspace ownership
        $workspaceId = session('workspace_id') ?? Auth::user()->workspaces()->first()->id;
        if ($emailAccount->workspace_id !== $workspaceId) {
            abort(403);
        }

        $emailAccount->delete();

        return redirect()->route('inbox.settings.accounts.index')
            ->with('success', 'Email account removed.');
    }
}
