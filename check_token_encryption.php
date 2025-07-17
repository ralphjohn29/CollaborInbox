<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

echo "=== Email Account Token Check ===\n\n";

// Get raw data from database
$rawAccount = DB::table('email_accounts')->where('id', 1)->first();

if ($rawAccount) {
    echo "1. RAW DATABASE VALUES:\n";
    echo "Email: {$rawAccount->email_address}\n";
    echo "Raw OAuth Token Length: " . strlen($rawAccount->oauth_access_token) . "\n";
    echo "Raw OAuth Token (first 50 chars): " . substr($rawAccount->oauth_access_token, 0, 50) . "...\n";
    echo "Raw Token has 'eyJ' prefix (JWT indicator): " . (strpos($rawAccount->oauth_access_token, 'eyJ') === 0 ? 'Yes' : 'No') . "\n\n";
    
    echo "2. THROUGH MODEL (with decryption):\n";
    $account = EmailAccount::find(1);
    echo "OAuth Token Length: " . strlen($account->oauth_access_token) . "\n";
    echo "OAuth Token (first 50 chars): " . substr($account->oauth_access_token, 0, 50) . "...\n";
    
    // Check if it looks like a JWT
    $tokenParts = explode('.', $account->oauth_access_token);
    echo "Token Parts: " . count($tokenParts) . " (should be 3 for JWT)\n";
    echo "Token has 'eyJ' prefix (JWT indicator): " . (strpos($account->oauth_access_token, 'eyJ') === 0 ? 'Yes' : 'No') . "\n\n";
    
    echo "3. ENCRYPTION TEST:\n";
    // Test if we can decrypt the raw value
    try {
        $decrypted = Crypt::decryptString($rawAccount->oauth_access_token);
        echo "Successfully decrypted raw token\n";
        echo "Decrypted length: " . strlen($decrypted) . "\n";
        echo "Decrypted first 50 chars: " . substr($decrypted, 0, 50) . "...\n";
        $decryptedParts = explode('.', $decrypted);
        echo "Decrypted token parts: " . count($decryptedParts) . "\n";
    } catch (\Exception $e) {
        echo "Failed to decrypt: " . $e->getMessage() . "\n";
    }
}
