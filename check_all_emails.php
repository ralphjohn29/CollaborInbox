<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\Workspace;

echo "=== ALL EMAILS IN DATABASE ===" . PHP_EOL;
$allEmails = Email::all();
echo "Total emails: " . $allEmails->count() . PHP_EOL . PHP_EOL;

foreach ($allEmails as $email) {
    echo "Email ID: " . $email->id . PHP_EOL;
    echo "  Subject: " . $email->subject . PHP_EOL;
    echo "  Account ID: " . $email->email_account_id . PHP_EOL;
    echo "  Workspace ID: " . $email->workspace_id . PHP_EOL;
    echo "  Created: " . $email->created_at . PHP_EOL . PHP_EOL;
}

echo "=== ALL WORKSPACES ===" . PHP_EOL;
$workspaces = Workspace::all();
foreach ($workspaces as $ws) {
    echo "Workspace ID: " . $ws->id . ", Name: " . $ws->name . PHP_EOL;
}

echo PHP_EOL . "=== ALL EMAIL ACCOUNTS ===" . PHP_EOL;
$accounts = EmailAccount::all();
foreach ($accounts as $acc) {
    echo "Account ID: " . $acc->id . ", Email: " . $acc->email . ", Workspace: " . $acc->workspace_id . ", Active: " . ($acc->is_active ? 'Yes' : 'No') . PHP_EOL;
}
