<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailAccount;

$account = EmailAccount::find(1);

if ($account) {
    echo "Email: {$account->email_address}\n";
    echo "OAuth Expires At: " . ($account->oauth_expires_at ? $account->oauth_expires_at->format('Y-m-d H:i:s') : 'Not set') . "\n";
    
    if ($account->oauth_expires_at) {
        echo "Is Expired: " . ($account->oauth_expires_at->isPast() ? 'Yes' : 'No') . "\n";
        echo "Time until expiry: " . $account->oauth_expires_at->diffForHumans() . "\n";
    }
    
    echo "\nRefresh Token: " . ($account->oauth_refresh_token ? 'Present' : 'Not present') . "\n";
}
