<?php

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUGGING INBOX ISSUE ===\n\n";

// Check all emails in database
$totalEmails = Email::count();
echo "Total emails in database: $totalEmails\n\n";

// Show emails with their workspace_id
echo "Emails by workspace_id:\n";
$emailsByWorkspace = Email::select('workspace_id', DB::raw('count(*) as count'))
    ->groupBy('workspace_id')
    ->get();

foreach ($emailsByWorkspace as $ws) {
    echo "  Workspace ID {$ws->workspace_id}: {$ws->count} emails\n";
}

// Check email accounts and their workspace_id
echo "\n\nEmail accounts:\n";
$accounts = EmailAccount::all();
foreach ($accounts as $account) {
    echo "  Account: {$account->email_address}\n";
    echo "    - ID: {$account->id}\n";
    echo "    - Tenant ID: {$account->tenant_id}\n";
    echo "    - Workspace ID: {$account->workspace_id}\n";
    $emailCount = Email::where('email_account_id', $account->id)->count();
    echo "    - Email count: $emailCount\n\n";
}

// Check users and their workspaces
echo "\nUsers and their workspaces:\n";
$users = User::with('workspaces')->get();
foreach ($users as $user) {
    echo "  User: {$user->email}\n";
    echo "    - ID: {$user->id}\n";
    echo "    - Workspace IDs: ";
    $workspaceIds = $user->workspaces->pluck('id')->toArray();
    echo implode(', ', $workspaceIds) . "\n\n";
}

// Show latest 5 emails
echo "\nLatest 5 emails:\n";
$latestEmails = Email::orderBy('created_at', 'desc')->limit(5)->get();
foreach ($latestEmails as $email) {
    echo "  - Subject: {$email->subject}\n";
    echo "    From: {$email->from_email}\n";
    echo "    Workspace ID: {$email->workspace_id}\n";
    echo "    Account ID: {$email->email_account_id}\n";
    echo "    Created: {$email->created_at}\n\n";
}
