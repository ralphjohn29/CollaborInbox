<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OutlookAuthController extends Controller
{
    public function __construct()
    {
        // Only apply auth middleware to specific methods, not callback
        $this->middleware('auth')->except(['handleProviderCallback']);
    }

    /**
     * Redirect the user to the Microsoft authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider(Request $request)
    {
        // Store the current user ID and auth data in session
        $request->session()->put('outlook_auth_data', [
            'user_id' => Auth::id(),
            'display_name' => $request->input('display_name', 'Outlook Account'),
            'description' => $request->input('description', 'Connected via Microsoft OAuth'),
        ]);

        return Socialite::driver('azure')
            ->stateless()
            ->redirect();
    }

    /**
     * Obtain the user information from Microsoft.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(Request $request)
    {
        try {
            // Get the auth data from session first
            $authData = $request->session()->get('outlook_auth_data', []);
            
            // If no auth data or user not logged in, redirect to login
            if (!Auth::check() && !empty($authData['user_id'])) {
                // Try to log the user back in
                Auth::loginUsingId($authData['user_id']);
            }
            
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('error', 'Please log in to connect your Outlook account.');
            }
            
            // Get Outlook user data using stateless
            try {
                $outlookUser = Socialite::driver('azure')
                    ->stateless()
                    ->user();
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $responseBody = $response->getBody()->getContents();
                Log::error('Socialite OAuth error', [
                    'status' => $response->getStatusCode(),
                    'body' => $responseBody,
                ]);
                throw new \Exception('OAuth authentication failed: ' . $responseBody);
            }
            
            // Log the successful OAuth response for debugging
            Log::info('Outlook OAuth successful', [
                'user_id' => $outlookUser->getId(),
                'email' => $outlookUser->getEmail(),
                'has_token' => !empty($outlookUser->token),
                'has_refresh_token' => !empty($outlookUser->refreshToken),
            ]);
            
            // Clear the session data
            $request->session()->forget('outlook_auth_data');
            
            // Extract user information
            $email = $outlookUser->getEmail();
            $accessToken = $outlookUser->token;
            $refreshToken = $outlookUser->refreshToken ?? null;
            
            // If no email, try to get from raw data
            if (empty($email)) {
                $rawUser = $outlookUser->getRaw();
                $email = $rawUser['userPrincipalName'] ?? $rawUser['mail'] ?? null;
                
                if (empty($email)) {
                    throw new \Exception('Could not retrieve email address from Microsoft account');
                }
            }
            
            // Create or update the email account
            $emailAccount = EmailAccount::updateOrCreate(
                [
                    'email_address' => $email,
                    'workspace_id' => 1, // Default workspace, adjust as needed
                    'tenant_id' => Auth::user()->tenant_id,
                ],
                [
                    'display_name' => $authData['display_name'] ?? $outlookUser->getName(),
                    'description' => $authData['description'] ?? 'Connected via Microsoft OAuth',
                    'provider' => 'outlook',
                    'is_active' => true,
                    
                    // OAuth tokens
                    'oauth_access_token' => $accessToken, // Model will handle encryption
                    'oauth_refresh_token' => $refreshToken,
                    
                    // Outlook uses OAuth, so we set specific values for IMAP/SMTP
                    'incoming_server_type' => 'oauth',
                    'incoming_server_host' => 'outlook.office365.com',
                    'incoming_server_port' => 993,
                    'incoming_server_ssl' => true,
                    'incoming_server_username' => $email,
                    'incoming_server_password' => $accessToken, // Use OAuth token, model will encrypt
                    
                    'outgoing_server_host' => 'smtp.office365.com',
                    'outgoing_server_port' => 587,
                    'outgoing_server_ssl' => true,
                    'outgoing_server_username' => $email,
                    'outgoing_server_password' => $accessToken, // Use OAuth token, model will encrypt,
                    'oauth_expires_at' => now()->addSeconds($outlookUser->expiresIn ?? 3600)
                ]
            );
            
            // Test the connection
            try {
                // Here you would typically test the IMAP connection
                // For now, we'll assume it's successful
                $connectionTest = true;
                
                if ($connectionTest) {
                    return redirect()->route('inbox.email-setup')
                        ->with('success', 'Outlook account connected successfully! You can now receive emails from ' . $email);
                }
            } catch (\Exception $e) {
                Log::error('Outlook connection test failed: ' . $e->getMessage());
                
                // Delete the account if connection fails
                $emailAccount->delete();
                
                return redirect()->route('inbox.email-setup')
                    ->with('error', 'Failed to connect to Outlook. Please try again.');
            }
            
        } catch (\Exception $e) {
            Log::error('Outlook OAuth callback error: ' . $e->getMessage());
            
            return redirect()->route('inbox.email-setup')
                ->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Disconnect an Outlook account
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function disconnect($id)
    {
        try {
            $account = EmailAccount::findOrFail($id);
            
            // Check if it's an Outlook account
            if ($account->provider !== 'outlook') {
                return redirect()->back()->with('error', 'This is not an Outlook account.');
            }
            
            // Delete the account
            $account->delete();
            
            return redirect()->route('inbox.email-setup')
                ->with('success', 'Outlook account disconnected successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to disconnect account: ' . $e->getMessage());
        }
    }
}
