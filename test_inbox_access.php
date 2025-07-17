<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Get or create a test user
$user = User::where('email', 'test@example.com')->first();

if ($user) {
    echo "Test user found: {$user->email}\n";
    echo "User ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    
    // Simulate login
    Auth::login($user);
    
    echo "\nSimulating access to inbox page...\n";
    
    // Create a request to the inbox
    $request = Illuminate\Http\Request::create('/inbox', 'GET');
    $app->instance('request', $request);
    
    try {
        $controller = new \App\Http\Controllers\SimpleInboxController();
        $response = $controller->index($request);
        
        if ($response instanceof \Illuminate\View\View) {
            echo "✓ Inbox page can be accessed successfully!\n";
            
            $data = $response->getData();
            echo "\nInbox Data:\n";
            echo "- Total emails: " . ($data['stats']['total'] ?? 0) . "\n";
            echo "- Unread: " . ($data['stats']['unread'] ?? 0) . "\n";
            echo "- Email accounts: " . count($data['emailAccounts'] ?? []) . "\n";
            echo "- Emails on page: " . count($data['emails'] ?? []) . "\n";
        } else {
            echo "✗ Unexpected response type: " . get_class($response) . "\n";
        }
    } catch (\Exception $e) {
        echo "✗ Error accessing inbox: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "No test user found. Please create one first.\n";
}
