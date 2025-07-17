<?php

namespace App\Services;

use App\Models\EmailAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OutlookOAuthService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $tokenEndpoint = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

    public function __construct()
    {
        $this->clientId = config('services.outlook.client_id');
        $this->clientSecret = config('services.outlook.client_secret');
        $this->redirectUri = config('services.outlook.redirect');
    }

    /**
     * Refresh the OAuth access token for an email account
     */
    public function refreshAccessToken(EmailAccount $account)
    {
        if (empty($account->oauth_refresh_token)) {
            Log::error('No refresh token available for account: ' . $account->email_address);
            return false;
        }

        Log::info('🔄 Refreshing OAuth token for account: ' . $account->email_address);

        try {
            $response = Http::asForm()->post($this->tokenEndpoint, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $account->oauth_refresh_token,
                'grant_type' => 'refresh_token',
                'scope' => 'https://graph.microsoft.com/Mail.ReadWrite https://graph.microsoft.com/Mail.Send offline_access'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Update the account with new tokens
                $account->update([
                    'oauth_access_token' => $data['access_token'],
                    'oauth_refresh_token' => $data['refresh_token'] ?? $account->oauth_refresh_token,
                    'oauth_expires_at' => Carbon::now()->addSeconds($data['expires_in'] ?? 3600)
                ]);

                Log::info('✅ OAuth token refreshed successfully for: ' . $account->email_address);
                return true;
            } else {
                Log::error('❌ Failed to refresh OAuth token', [
                    'account' => $account->email_address,
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('❌ Exception refreshing OAuth token', [
                'account' => $account->email_address,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if token is expired or will expire soon
     */
    public function isTokenExpired(EmailAccount $account)
    {
        if (!$account->oauth_expires_at) {
            return true;
        }

        $expiresAt = Carbon::parse($account->oauth_expires_at);
        $now = Carbon::now();
        
        // Consider token expired if it expires within 5 minutes
        return $now->addMinutes(5)->greaterThan($expiresAt);
    }

    /**
     * Get a valid access token, refreshing if necessary
     */
    public function getValidAccessToken(EmailAccount $account)
    {
        if ($this->isTokenExpired($account)) {
            Log::info('🔄 Token expired, attempting refresh for: ' . $account->email_address);
            
            if ($this->refreshAccessToken($account)) {
                $account->refresh(); // Reload the model with fresh data
                return $account->oauth_access_token;
            } else {
                Log::error('❌ Failed to refresh token for: ' . $account->email_address);
                return null;
            }
        }

        return $account->oauth_access_token;
    }

    /**
     * Test if the current token is working
     */
    public function testToken(EmailAccount $account)
    {
        $token = $this->getValidAccessToken($account);
        if (!$token) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->get('https://graph.microsoft.com/v1.0/me');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('❌ Token test failed', [
                'account' => $account->email_address,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
