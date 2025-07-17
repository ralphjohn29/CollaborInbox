<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use App\Services\MicrosoftGraphEmailFetcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailFetchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Fetch emails for all accounts or a specific account
     */
    public function fetchEmails(Request $request)
    {
        $accountId = $request->get('account_id');
        $results = [];

        if ($accountId) {
            // Fetch for specific account
            $account = EmailAccount::find($accountId);
            if ($account && $account->is_active) {
                $results[] = $this->fetchForAccount($account);
            }
        } else {
            // Fetch for all active accounts
            $accounts = EmailAccount::where('is_active', true)->get();
            foreach ($accounts as $account) {
                $results[] = $this->fetchForAccount($account);
            }
        }

        // Always return JSON for this endpoint since it's called via AJAX
        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Fetch emails for a specific account
     */
    protected function fetchForAccount(EmailAccount $account)
    {
        try {
            // Handle OAuth accounts (currently only Outlook)
            if ($account->provider === 'outlook' && $account->oauth_access_token) {
                $fetcher = new MicrosoftGraphEmailFetcher($account);
                $result = $fetcher->fetchEmails(25); // Fetch last 25 emails
                
                return [
                    'account' => $account->email_address,
                    'provider' => $account->provider,
                    'success' => $result['success'],
                    'count' => $result['count'] ?? 0,
                    'message' => $result['message'] ?? $result['error'] ?? 'Unknown error',
                ];
            }

            // For non-OAuth accounts, we would use IMAP fetcher
            // This is not implemented yet
            return [
                'account' => $account->email_address,
                'provider' => $account->provider,
                'success' => false,
                'count' => 0,
                'message' => 'IMAP fetching not implemented yet',
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching emails for account', [
                'account_id' => $account->id,
                'email' => $account->email_address,
                'error' => $e->getMessage(),
            ]);

            return [
                'account' => $account->email_address,
                'provider' => $account->provider,
                'success' => false,
                'count' => 0,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}
