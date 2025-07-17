<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailAccount;

echo "=== OAuth Token Check ===\n";
$account = EmailAccount::find(1);

if ($account) {
    echo "Email: {$account->email_address}\n";
    echo "Provider: {$account->provider}\n";
    echo "OAuth Token Length: " . strlen($account->oauth_access_token) . "\n";
    echo "OAuth Token (first 50 chars): " . substr($account->oauth_access_token, 0, 50) . "...\n";
    echo "Has Refresh Token: " . ($account->oauth_refresh_token ? 'Yes' : 'No') . "\n";
    echo "Token Expires At: " . ($account->oauth_expires_at ? $account->oauth_expires_at : 'Not set') . "\n";
    
    // Check if it's a valid JWT format (should have 3 parts separated by dots)
    $tokenParts = explode('.', $account->oauth_access_token);
    echo "Token Parts: " . count($tokenParts) . " (should be 3 for a valid JWT)\n";
}
