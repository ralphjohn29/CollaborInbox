<?php

require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== EMAIL DATABASE DEBUG ===\n";

// Check total emails
$totalEmails = App\Models\Email::count();
echo "Total emails in database: {$totalEmails}\n\n";

if ($totalEmails > 0) {
    // Check emails by workspace
    echo "Emails by workspace:\n";
    $emailsByWorkspace = App\Models\Email::selectRaw('workspace_id, count(*) as count')
        ->groupBy('workspace_id')
        ->get();
    
    foreach ($emailsByWorkspace as $item) {
        echo "  Workspace {$item->workspace_id}: {$item->count} emails\n";
    }
    
    echo "\nFirst 5 emails:\n";
    $emails = App\Models\Email::orderBy('id')->take(5)->get();
    foreach ($emails as $email) {
        echo "  ID: {$email->id}, Subject: {$email->subject}, Workspace: {$email->workspace_id}\n";
    }
    
    // Check email accounts
    echo "\nEmail accounts:\n";
    $accounts = App\Models\EmailAccount::all();
    foreach ($accounts as $account) {
        echo "  ID: {$account->id}, Email: {$account->email}, Workspace: {$account->workspace_id}\n";
    }
    
    // Check current user's workspace
    echo "\nCurrent session/user workspace info:\n";
    if (session()->has('workspace_id')) {
        echo "  Session workspace_id: " . session('workspace_id') . "\n";
    } else {
        echo "  No workspace_id in session\n";
    }
    
    // Check if there's a logged in user
    if (auth()->check()) {
        $user = auth()->user();
        echo "  Current user ID: {$user->id}\n";
        echo "  Current user workspace_id: {$user->workspace_id}\n";
    } else {
        echo "  No user logged in\n";
    }
} else {
    echo "No emails found in database!\n";
}

echo "\n=== END DEBUG ===\n";
