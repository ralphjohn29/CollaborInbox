<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchEmailsJob;
use App\Models\Tenant\MailboxConfiguration;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Log;

class FetchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:emails {--tenant= : Specific tenant ID to process (optional, default: all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch new emails from configured mailboxes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');
        
        // If a specific tenant is specified, only process that tenant
        if ($tenantId) {
            $this->info("Fetching emails for tenant ID: {$tenantId}");
            $this->dispatchJobForTenant($tenantId);
            return;
        }
        
        // Otherwise, process all active tenants
        $this->info("Fetching emails for all active tenants...");
        
        // Get list of all tenants
        $tenants = $this->getActiveTenants();
        
        $count = count($tenants);
        $this->info("Found {$count} tenants to process.");
        
        foreach ($tenants as $tenant) {
            $this->dispatchJobForTenant($tenant->id);
        }
        
        $this->info("Email fetch jobs dispatched for all tenants.");
    }
    
    /**
     * Dispatch fetch emails job for a specific tenant
     */
    protected function dispatchJobForTenant($tenantId)
    {
        try {
            // Set tenant context (in a real app, this would switch to the tenant's DB)
            app(TenantContext::class)->setTenant($tenantId);
            
            // Dispatch the job with the tenant info
            FetchEmailsJob::dispatch($tenantId)
                ->onQueue('emails');
                
            $this->info("Job dispatched for tenant {$tenantId}");
        } catch (\Exception $e) {
            $this->error("Failed to dispatch job for tenant {$tenantId}: {$e->getMessage()}");
            Log::error("Failed to dispatch email fetch job", [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get list of active tenants
     * This is a placeholder - in a real app, this would query the central tenants table
     */
    protected function getActiveTenants()
    {
        // Since we don't have an 'active' column in the Tenant model,
        // we'll simply get all tenants instead
        $tenantModel = app()->make(\App\Models\Tenant::class);
        return $tenantModel->all();
    }
} 