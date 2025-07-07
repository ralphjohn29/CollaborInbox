<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {name} {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');

        $this->info("Creating tenant: {$name} at {$domain}");

        $tenant = Tenant::create([
            'name' => $name,
            'domain' => $domain,
        ]);

        $this->info("Tenant created successfully with ID: {$tenant->id}");

        return Command::SUCCESS;
    }
} 