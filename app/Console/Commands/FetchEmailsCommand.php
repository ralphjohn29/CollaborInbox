<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailAccount;
use App\Services\EmailProviders\ImapEmailFetcher;
use Illuminate\Support\Facades\Log;

class FetchEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:fetch {--account= : Specific email account ID to fetch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch new emails from configured email accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->option('account');
        
        if ($accountId) {
            $accounts = EmailAccount::where('id', $accountId)
                ->where('is_active', true)
                ->get();
        } else {
            $accounts = EmailAccount::where('is_active', true)->get();
        }
        
        if ($accounts->isEmpty()) {
            $this->error('No active email accounts found.');
            return 1;
        }
        
        $this->info('Fetching emails from ' . $accounts->count() . ' account(s)...');
        
        $totalFetched = 0;
        
        foreach ($accounts as $account) {
            $this->line("\nProcessing: {$account->email_address}");
            
            try {
                $fetcher = new ImapEmailFetcher($account);
                $fetcher->connect();
                
                $this->info('Connected successfully.');
                
                $count = $fetcher->fetchNewEmails();
                $totalFetched += $count;
                
                $this->info("Fetched {$count} new email(s).");
                
                $fetcher->disconnect();
                
            } catch (\Exception $e) {
                $this->error("Failed: " . $e->getMessage());
                Log::error('Email fetch failed', [
                    'account' => $account->email_address,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        $this->info("\nTotal emails fetched: {$totalFetched}");
        
        return 0;
    }
}
