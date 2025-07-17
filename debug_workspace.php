<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Email;
use App\Models\User;
use App\Models\EmailAccount;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== WORKSPACE DEBUG ===\n\n";

// Check users and their workspaces
echo "Users in database:\n";
$users = User::all();
foreach ($users as $user) {
    echo "  ID: {$user->id}, Email: {$user->email}, Workspace ID: {$user->workspace_id}\n";
}

// Check if there's a default user we can use
$user = User::first();
if ($user) {
    echo "\nUsing user: {$user->email} (ID: {$user->id}, Workspace: {$user->workspace_id})\n";
    
    // Simulate the workspace retrieval logic from the controller
    $workspaceId = $user->workspace_id ?: 1;
    echo "Workspace ID to use: {$workspaceId}\n";
    
    // Check emails in that workspace
    $emailCount = Email::where('workspace_id', $workspaceId)->count();
    echo "\nEmails in workspace {$workspaceId}: {$emailCount}\n";
    
    // Get first few emails
    $emails = Email::where('workspace_id', $workspaceId)->take(5)->get();
    echo "\nFirst 5 emails:\n";
    foreach ($emails as $email) {
        echo "  ID: {$email->id}, Subject: {$email->subject}, Account ID: {$email->email_account_id}\n";
    }
    
    // Check email accounts
    echo "\nEmail accounts in workspace {$workspaceId}:\n";
    $accounts = EmailAccount::where('workspace_id', $workspaceId)->get();
    foreach ($accounts as $account) {
        echo "  ID: {$account->id}, Email: {$account->email}, Active: " . ($account->is_active ? 'Yes' : 'No') . "\n";
    }
    
    // Check what the controller query would return
    echo "\nSimulating controller query:\n";
    $query = Email::where('workspace_id', $workspaceId)
        ->with(['emailAccount', 'assignedUser', 'disposition', 'attachments']);
    
    $count = $query->count();
    echo "Query would return: {$count} emails\n";
}

echo "\n=== END DEBUG ===\n";
