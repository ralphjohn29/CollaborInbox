<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Find the user
$user = User::where('email', 'rj@collaborinbox.com')->first();

if ($user) {
    echo "Found user: " . $user->email . "\n";
    echo "Current workspace ID: " . $user->workspace_id . "\n";
    
    // Update to workspace 1
    $user->workspace_id = 1;
    $user->save();
    
    echo "Updated workspace ID to: " . $user->workspace_id . "\n";
    echo "User successfully updated!\n";
} else {
    echo "User rj@collaborinbox.com not found!\n";
}
