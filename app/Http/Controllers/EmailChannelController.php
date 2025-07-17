<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Exception;

class EmailChannelController extends Controller
{
    /**
     * Show the email channel connection page
     */
    public function index()
    {
        $emailAccounts = EmailAccount::where('user_id', Auth::id())->get();
        return view('inbox.channels.index', compact('emailAccounts'));
    }

    /**
     * Show the form to connect a new email channel
     */
    public function create()
    {
        return view('inbox.channels.connect');
    }

    /**
     * Show Gmail connection setup
     */
    public function gmailSetup()
    {
        return view('inbox.channels.gmail');
    }

    /**
     * Show Outlook connection setup
     */
    public function outlookSetup()
    {
        return view('inbox.channels.outlook');
    }

    /**
     * Show other email provider setup
     */
    public function otherSetup()
    {
        $users = User::all();
        return view('inbox.channels.other', compact('users'));
    }

    /**
     * Store Gmail connection
     */
    public function storeGmail(Request $request)
    {
        $request->validate([
            'email_address' => 'required|email',
            'access_token' => 'required|string',
            'refresh_token' => 'required|string',
        ]);

        try {
            $emailAccount = new EmailAccount();
            $emailAccount->user_id = Auth::id();
            $emailAccount->email_address = $request->email_address;
            $emailAccount->from_name = $request->from_name ?? explode('@', $request->email_address)[0];
            $emailAccount->provider = 'gmail';
            
            // Gmail SMTP settings
            $emailAccount->outgoing_server_host = 'smtp.gmail.com';
            $emailAccount->outgoing_server_port = 587;
            $emailAccount->outgoing_server_encryption = 'tls';
            $emailAccount->outgoing_server_username = $request->email_address;
            $emailAccount->outgoing_server_password = $request->access_token;
            
            // Gmail IMAP settings
            $emailAccount->incoming_server_host = 'imap.gmail.com';
            $emailAccount->incoming_server_port = 993;
            $emailAccount->incoming_server_encryption = 'ssl';
            $emailAccount->incoming_server_username = $request->email_address;
            $emailAccount->incoming_server_password = $request->access_token;
            
            // OAuth tokens
            $emailAccount->oauth_access_token = $request->access_token;
            $emailAccount->oauth_refresh_token = $request->refresh_token;
            
            $emailAccount->is_active = true;
            $emailAccount->save();

            return redirect()->route('inbox.channels.index')
                ->with('success', 'Gmail account connected successfully!');
        } catch (Exception $e) {
            Log::error('Failed to connect Gmail account: ' . $e->getMessage());
            return back()->with('error', 'Failed to connect Gmail account. Please try again.');
        }
    }

    /**
     * Store Outlook connection
     */
    public function storeOutlook(Request $request)
    {
        $request->validate([
            'email_address' => 'required|email',
            'access_token' => 'required|string',
            'refresh_token' => 'required|string',
        ]);

        try {
            $emailAccount = new EmailAccount();
            $emailAccount->user_id = Auth::id();
            $emailAccount->email_address = $request->email_address;
            $emailAccount->from_name = $request->from_name ?? explode('@', $request->email_address)[0];
            $emailAccount->provider = 'outlook';
            
            // Outlook SMTP settings
            $emailAccount->outgoing_server_host = 'smtp-mail.outlook.com';
            $emailAccount->outgoing_server_port = 587;
            $emailAccount->outgoing_server_encryption = 'tls';
            $emailAccount->outgoing_server_username = $request->email_address;
            $emailAccount->outgoing_server_password = $request->access_token;
            
            // Outlook IMAP settings
            $emailAccount->incoming_server_host = 'outlook.office365.com';
            $emailAccount->incoming_server_port = 993;
            $emailAccount->incoming_server_encryption = 'ssl';
            $emailAccount->incoming_server_username = $request->email_address;
            $emailAccount->incoming_server_password = $request->access_token;
            
            // OAuth tokens
            $emailAccount->oauth_access_token = $request->access_token;
            $emailAccount->oauth_refresh_token = $request->refresh_token;
            
            $emailAccount->is_active = true;
            $emailAccount->save();

            return redirect()->route('inbox.channels.index')
                ->with('success', 'Outlook account connected successfully!');
        } catch (Exception $e) {
            Log::error('Failed to connect Outlook account: ' . $e->getMessage());
            return back()->with('error', 'Failed to connect Outlook account. Please try again.');
        }
    }

    /**
     * Store other email provider connection
     */
    public function storeOther(Request $request)
    {
        $request->validate([
            'email_address' => 'required|email',
            'from_name' => 'nullable|string|max:255',
            'incoming_server_host' => 'required|string',
            'incoming_server_port' => 'required|integer',
            'incoming_server_encryption' => 'required|in:ssl,tls,none',
            'incoming_server_username' => 'required|string',
            'incoming_server_password' => 'required|string',
            'outgoing_server_host' => 'required|string',
            'outgoing_server_port' => 'required|integer',
            'outgoing_server_encryption' => 'required|in:ssl,tls,none',
            'outgoing_server_username' => 'required|string',
            'outgoing_server_password' => 'required|string',
        ]);

        try {
            $emailAccount = new EmailAccount();
            $emailAccount->user_id = Auth::id();
            $emailAccount->email_address = $request->email_address;
            $emailAccount->from_name = $request->from_name ?? explode('@', $request->email_address)[0];
            $emailAccount->provider = 'other';
            
            // SMTP settings
            $emailAccount->outgoing_server_host = $request->outgoing_server_host;
            $emailAccount->outgoing_server_port = $request->outgoing_server_port;
            $emailAccount->outgoing_server_encryption = $request->outgoing_server_encryption === 'none' ? null : $request->outgoing_server_encryption;
            $emailAccount->outgoing_server_username = $request->outgoing_server_username;
            $emailAccount->outgoing_server_password = $request->outgoing_server_password;
            
            // IMAP settings
            $emailAccount->incoming_server_host = $request->incoming_server_host;
            $emailAccount->incoming_server_port = $request->incoming_server_port;
            $emailAccount->incoming_server_encryption = $request->incoming_server_encryption === 'none' ? null : $request->incoming_server_encryption;
            $emailAccount->incoming_server_username = $request->incoming_server_username;
            $emailAccount->incoming_server_password = $request->incoming_server_password;
            
            $emailAccount->is_active = true;
            $emailAccount->save();

            return redirect()->route('inbox.channels.index')
                ->with('success', 'Email account connected successfully!');
        } catch (Exception $e) {
            Log::error('Failed to connect email account: ' . $e->getMessage());
            return back()->with('error', 'Failed to connect email account. Please try again.');
        }
    }

    /**
     * Test email connection
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:gmail,outlook,other',
            'email_address' => 'required|email',
        ]);

        try {
            // Test IMAP connection based on provider
            $config = [];
            
            switch ($request->provider) {
                case 'gmail':
                    $config = [
                        'host' => 'imap.gmail.com',
                        'port' => 993,
                        'encryption' => 'ssl',
                        'username' => $request->email_address,
                        'password' => $request->password ?? $request->access_token,
                    ];
                    break;
                    
                case 'outlook':
                    $config = [
                        'host' => 'outlook.office365.com',
                        'port' => 993,
                        'encryption' => 'ssl',
                        'username' => $request->email_address,
                        'password' => $request->password ?? $request->access_token,
                    ];
                    break;
                    
                case 'other':
                    $config = [
                        'host' => $request->incoming_server_host,
                        'port' => $request->incoming_server_port,
                        'encryption' => $request->incoming_server_encryption === 'none' ? null : $request->incoming_server_encryption,
                        'username' => $request->incoming_server_username,
                        'password' => $request->incoming_server_password,
                    ];
                    break;
            }

            // Attempt connection (simplified for now)
            // In production, you would use proper IMAP library
            $connection = @imap_open(
                "{{$config['host']}:{$config['port']}/imap/{$config['encryption']}/novalidate-cert}",
                $config['username'],
                $config['password']
            );

            if ($connection) {
                imap_close($connection);
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection failed. Please check your credentials.'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete email channel
     */
    public function destroy($id)
    {
        try {
            $emailAccount = EmailAccount::where('user_id', Auth::id())->findOrFail($id);
            $emailAccount->delete();
            
            return redirect()->route('inbox.channels.index')
                ->with('success', 'Email account disconnected successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to disconnect email account.');
        }
    }
}
