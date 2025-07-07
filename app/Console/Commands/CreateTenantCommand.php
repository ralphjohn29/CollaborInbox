<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {--id=} {--name=} {--domain=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant and associate a domain (e.g., tenant:create --name="Test Co" --domain=test)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->option('id') ?: Str::uuid()->toString();
        $name = $this->option('name') ?: 'Tenant ' . $id;
        $subdomainPart = $this->option('domain') ?: Str::slug($name);

        if (empty($subdomainPart)) {
            $this->error('Domain part cannot be empty. Please provide --domain or a --name that generates a valid slug.');
            return Command::FAILURE;
        }

        $appUrl = config('app.url', 'http://collaborinbox.test');
        $baseHost = parse_url($appUrl, PHP_URL_HOST) ?? 'collaborinbox.test';
        $fullDomain = $subdomainPart . '.' . $baseHost;

        try {
            $tenant = Tenant::create(['id' => $id, 'name' => $name]);

            $tenant->domains()->create(['domain' => $fullDomain]);

            $this->info("Tenant '$name' created successfully with ID: $id");
            $this->table(
                ['Tenant ID', 'Name', 'Domain'],
                [[$tenant->id, $name, $fullDomain]]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create tenant: " . $e->getMessage());
            if (isset($tenant)) {
                // Tenant::find($id)?->delete();
            }
            return Command::FAILURE;
        }
    }
} 