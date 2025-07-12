<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Workspace;

try {
    // Find or create a workspace
    $workspace = Workspace::first();
    if (!$workspace) {
        $workspace = Workspace::create([
            'name' => 'Default Workspace',
            'email_alias' => 'support@collaborinbox.com',
            'is_active' => true,
        ]);
        echo "Created new workspace with ID: {$workspace->id}\n";
    } else {
        echo "Found existing workspace with ID: {$workspace->id}\n";
    }
    
    // Update test user
    $user = User::where('email', 'test@example.com')->first();
    if ($user) {
        $user->workspace_id = $workspace->id;
        $user->save();
        echo "Updated test@example.com user with workspace_id: {$workspace->id}\n";
    } else {
        echo "User test@example.com not found!\n";
    }
    
    // Show updated user info
    $user = User::where('email', 'test@example.com')->first();
    if ($user) {
        echo "\nUser details:\n";
        echo "ID: {$user->id}\n";
        echo "Email: {$user->email}\n";
        echo "Workspace ID: {$user->workspace_id}\n";
        echo "Tenant ID: {$user->tenant_id}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
