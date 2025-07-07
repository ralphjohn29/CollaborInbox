<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Database\Seeders\TenantDatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Stancl\Tenancy\Jobs\CreateDatabase;

class CreateTestTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create-test {name?} {--subdomain=} {--count=1} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test tenants with subdomain support';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $count = max(1, min($count, 10)); // Ensure count is between 1 and 10
        $shouldSeed = $this->option('seed');
        
        $wildcard = config('tenancy.domain.wildcard_domain', '*.collaborinbox.test');
        $baseDomain = Str::after($wildcard, '*.');
        
        for ($i = 1; $i <= $count; $i++) {
            $name = $this->argument('name');
            $subdomain = $this->option('subdomain');
            
            if (!$name) {
                $name = 'Test Tenant ' . $i;
            } elseif ($count > 1) {
                $name = $name . ' ' . $i;
            }
            
            if (!$subdomain) {
                // Generate a slug from the name and add a random suffix for uniqueness
                $subdomain = Str::slug($name) . '-' . Str::lower(Str::random(4));
            } elseif ($count > 1) {
                $subdomain = $subdomain . $i;
            }
            
            $domain = $subdomain . '.' . $baseDomain;
            
            $this->info("Creating test tenant: {$name}");
            $this->info("Domain: {$domain}");
            
            try {
                // Create the tenant with a name
                $tenant = Tenant::create([
                    'name' => $name,
                ]);
                
                // Add the domain to the tenant
                $tenant->domains()->create([
                    'domain' => $domain,
                ]);
                
                $this->info("Tenant created successfully with ID: {$tenant->id}");
                $this->info("Access at: http://{$domain}");
                
                // Create tenant database
                CreateDatabase::dispatch($tenant);
                $this->info("Tenant database created.");
                
                // Seed tenant database if requested
                if ($shouldSeed) {
                    $this->info("Seeding tenant database with test data...");
                    
                    // Switch to tenant context to run the seeder
                    $tenant->run(function () {
                        $seeder = new TenantDatabaseSeeder();
                        $seeder->run();
                    });
                    
                    $this->info("Tenant database seeded successfully.");
                }
                
            } catch (\Exception $e) {
                $this->error("Failed to create tenant: {$e->getMessage()}");
            }
            
            if ($count > 1 && $i < $count) {
                $this->newLine();
            }
        }
        
        return Command::SUCCESS;
    }
} 