<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailAccount;

echo "=== Email Accounts ===\n";
$accounts = EmailAccount::all();

if ($accounts->isEmpty()) {
    echo "No email accounts found.\n";
} else {
    foreach ($accounts as $account) {
        echo "ID: {$account->id}\n";
        echo "Email: {$account->email_address}\n";
        echo "Provider: {$account->provider}\n";
        echo "Active: " . ($account->is_active ? 'Yes' : 'No') . "\n";
        echo "OAuth Token: " . ($account->oauth_access_token ? 'Present' : 'Not present') . "\n";
        echo "---\n";
    }
}
