<?php

namespace App\Console\Commands;

use App\Models\EmailAccount;
use App\Services\MicrosoftGraphEmailFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:sync {--account= : Specific account ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync new emails from all active email accounts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting email sync...');
        
        $accountId = $this->option('account');
        $totalFetched = 0;
        $results = [];
        
        if ($accountId) {
            // Sync specific account
            $account = EmailAccount::find($accountId);
            if ($account && $account->is_active) {
                $result = $this->syncAccount($account);
                $results[] = $result;
                $totalFetched += $result['count'] ?? 0;
            }
        } else {
            // Sync all active accounts
            $accounts = EmailAccount::where('is_active', true)->get();
            $this->info("Found {$accounts->count()} active email accounts");
            
            foreach ($accounts as $account) {
                $result = $this->syncAccount($account);
                $results[] = $result;
                $totalFetched += $result['count'] ?? 0;
            }
        }
        
        // Display results
        $this->info("\nSync Results:");
        $this->info("==============");
        foreach ($results as $result) {
            $status = $result['success'] ? '✓' : '✗';
            $this->info("{$status} {$result['account']} - {$result['message']}");
        }
        
        $this->info("\nTotal new emails synced: {$totalFetched}");
        
        return Command::SUCCESS;
    }
    
    /**
     * Sync emails for a specific account
     */
    protected function syncAccount(EmailAccount $account)
    {
        try {
            $this->info("Syncing emails for {$account->email_address}...");
            
            // Handle OAuth accounts (currently only Outlook)
            if ($account->provider === 'outlook' && $account->oauth_access_token) {
                $fetcher = new MicrosoftGraphEmailFetcher($account);
                $result = $fetcher->fetchEmails(50); // Fetch up to 50 new emails
                
                return [
                    'account' => $account->email_address,
                    'provider' => $account->provider,
                    'success' => $result['success'],
                    'count' => $result['count'] ?? 0,
                    'message' => $result['message'] ?? $result['error'] ?? 'Unknown error',
                ];
            }
            
            // For non-OAuth accounts, we would use IMAP fetcher
            return [
                'account' => $account->email_address,
                'provider' => $account->provider,
                'success' => false,
                'count' => 0,
                'message' => 'IMAP fetching not implemented yet',
            ];
            
        } catch (\Exception $e) {
            Log::error('Error syncing emails for account', [
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
