<?php

namespace App\Console\Commands;

use App\Providers\TenancyServiceProvider;
use Illuminate\Console\Command;
use Stancl\Tenancy\Commands\InstallCommand;

class SetupTenancyCommand extends Command
{
    protected $signature = 'tenancy:setup';
    protected $description = 'Setup and configure the stancl/tenancy package for this application';

    public function handle(): int
    {
        $this->info('Setting up stancl/tenancy for multi-tenant subdomain routing...');
        
        // 1. Register service provider
        $this->registerServiceProvider();
        
        // 2. Publish the package assets
        $this->publishTenancyAssets();
        
        // 3. Create the tenant migrations directory
        $this->createTenantMigrationsDirectory();
        
        // 4. Run migrations
        if ($this->confirm('Run the tenancy migrations now?', true)) {
            $this->call('migrate');
        }
        
        // 5. Suggest next steps
        $this->newLine();
        $this->info('Tenancy setup complete! Next steps:');
        $this->newLine();
        $this->line('1. Create a tenant: php artisan tenant:create --name="Company Name" --domain=subdomain');
        $this->line('2. Access tenant routes via: http://subdomain.collaborinbox.test/tenant-info');
        $this->line('3. Create tenant migrations: php artisan make:migration create_tenant_table --path=database/migrations/tenant');
        $this->newLine();
        
        return Command::SUCCESS;
    }
    
    private function registerServiceProvider(): void
    {
        // Inform user
        $this->info('Ensuring TenancyServiceProvider is registered...');
        
        // Check for provider registration in config/app.php
        $providerClass = TenancyServiceProvider::class;
        $this->info("Note: Make sure $providerClass is registered in config/app.php");
        $this->info("Alternatively, you can manually add it to the 'providers' array in config/app.php");
    }
    
    private function publishTenancyAssets(): void
    {
        $this->info('Publishing tenancy configuration and migrations...');
        
        // Publish the stancl/tenancy config
        $this->call('vendor:publish', [
            '--provider' => 'Stancl\\Tenancy\\TenancyServiceProvider',
            '--tag' => 'config',
        ]);
        
        // Publish the migrations
        $this->call('vendor:publish', [
            '--provider' => 'Stancl\\Tenancy\\TenancyServiceProvider',
            '--tag' => 'migrations',
        ]);
    }
    
    private function createTenantMigrationsDirectory(): void
    {
        $tenantMigrationsPath = database_path('migrations/tenant');
        
        if (!file_exists($tenantMigrationsPath)) {
            mkdir($tenantMigrationsPath, 0755, true);
            $this->info("Created tenant migrations directory: $tenantMigrationsPath");
        } else {
            $this->info("Tenant migrations directory already exists.");
        }
        
        // Create a README file
        $readmePath = $tenantMigrationsPath . '/README.md';
        
        if (!file_exists($readmePath)) {
            file_put_contents($readmePath, 
                "# Tenant Migrations\n\n" .
                "Migrations in this directory will be run for each tenant database.\n" .
                "Create tenant migrations with:\n\n" .
                "```\nphp artisan make:migration create_tenant_table --path=database/migrations/tenant\n```\n"
            );
            $this->info("Created tenant migrations README.");
        }
    }
} 