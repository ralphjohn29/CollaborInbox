<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Find the test user
    $user = User::where('email', 'test@example.com')->first();
    
    if ($user) {
        // Update password to 12345678
        $user->password = Hash::make('12345678');
        $user->save();
        
        echo "Password updated successfully!\n";
        echo "Email: test@example.com\n";
        echo "New Password: 12345678\n";
    } else {
        echo "User test@example.com not found!\n";
        
        // List all users
        $users = User::all();
        echo "\nAvailable users:\n";
        foreach ($users as $u) {
            echo "- ID: {$u->id}, Email: {$u->email}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // If database connection fails, show the error
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "\nDatabase connection failed. Please check:\n";
        echo "1. MySQL/MariaDB service is running\n";
        echo "2. Your .env file has correct database settings:\n";
        echo "   DB_HOST=127.0.0.1\n";
        echo "   DB_PORT=3306\n";
        echo "   DB_DATABASE=collaborinbox\n";
        echo "   DB_USERNAME=root\n";
        echo "   DB_PASSWORD=(your password)\n";
    }
}
