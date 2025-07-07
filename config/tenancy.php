<?php

return [
    'tenant_model' => App\Models\Tenant::class,

    'identification_driver' => [
        'domain',
    ],

    'database' => [
        'based-on' => 'default',
        'prefix' => env('TENANCY_DATABASE_PREFIX', 'tenant_'),
        'suffix' => '',
    ],

    'domain' => [
        'wildcard_domain' => '*.collaborinbox.test',
    ],

    'migrations' => [
        'paths' => [
            database_path('migrations/tenant'),
        ],
    ],

    'routes' => [
        'prefix' => 'api',
        'middleware' => [
            'web',
            'tenant',
        ],
        'paths' => [
            base_path('routes/tenant.php'),
        ],
    ],

    'filesystem' => [
        'asset_helper_tenancy' => false, // Keep this disabled for now
    ],

    // Add this empty array to disable default bootstrappers
    'bootstrappers' => [
        // Empty array means no default bootstrappers will be loaded automatically
        // when the Tenancy service is first resolved.
    ],
];