<?php

use App\Models\Email;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INBOX WORKSPACE DEBUG ===\n\n";

// Check all emails
$totalEmails = Email::count();
echo "Total emails in database: $totalEmails\n";

// Check emails by workspace
$workspace1Emails = Email::where('workspace_id', 1)->count();
echo "Emails in workspace 1: $workspace1Emails\n\n";

// Check what the InboxController would query
echo "What InboxController queries:\n";
echo "- It looks for emails where workspace_id = session('workspace_id') ?? user->workspaces->first()->id ?? 1\n";
echo "- Since users don't have workspaces relation, it defaults to 1\n";
echo "- So it should find all $workspace1Emails emails\n\n";

// Show some sample emails
echo "Sample emails in the database:\n";
$emails = Email::orderBy('created_at', 'desc')->limit(5)->get();
foreach ($emails as $email) {
    echo "- {$email->subject} (workspace_id: {$email->workspace_id}, status: {$email->status})\n";
}

echo "\nThe issue is likely in the inbox view or filters, not the database query.\n";
