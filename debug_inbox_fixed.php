<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\User;

echo "=== INBOX DEBUG ===\n\n";

// Check users and their workspace
echo "1. Checking Users:\n";
$users = User::all();
foreach ($users as $u) {
    echo "   - User: {$u->email}\n";
    echo "     • ID: {$u->id}\n";
    echo "     • workspace_id: " . ($u->workspace_id ?? 'NULL') . "\n";
    echo "     • tenant_id: " . ($u->tenant_id ?? 'NULL') . "\n";
}

// Check the test user specifically
echo "\n2. Test User Details:\n";
$user = User::where('email', 'test@example.com')->first();
if ($user) {
    echo "   - workspace_id from user: " . ($user->workspace_id ?? 'NULL') . "\n";
    echo "   - Session workspace_id: " . (session('workspace_id') ?? 'NULL') . "\n";
    
    // Simulate the controller logic
    $workspaceId = session('workspace_id') ?? $user->workspace_id ?? 1;
    echo "   - Controller would use workspace_id: {$workspaceId}\n";
}

// Check email distribution
echo "\n3. Email Distribution:\n";
$totalEmails = Email::count();
echo "   - Total emails: {$totalEmails}\n";

$workspaceGroups = Email::selectRaw('workspace_id, count(*) as count')
    ->groupBy('workspace_id')
    ->get();

foreach ($workspaceGroups as $group) {
    echo "   - Workspace {$group->workspace_id}: {$group->count} emails\n";
}

// Check email accounts
echo "\n4. Email Accounts:\n";
$accounts = EmailAccount::all();
foreach ($accounts as $account) {
    echo "   - {$account->email_address}:\n";
    echo "     • workspace_id: {$account->workspace_id}\n";
    echo "     • is_active: " . ($account->is_active ? 'Yes' : 'No') . "\n";
}

// The key issue - let's check what workspace the controller would query
echo "\n5. CRITICAL CHECK - What the inbox page queries:\n";
$testWorkspaceId = $user->workspace_id ?? 1;
echo "   - Looking for emails in workspace_id: {$testWorkspaceId}\n";

$emailsInWorkspace = Email::where('workspace_id', $testWorkspaceId)->count();
echo "   - Found {$emailsInWorkspace} emails in workspace {$testWorkspaceId}\n";

$activeAccounts = EmailAccount::where('workspace_id', $testWorkspaceId)
    ->where('is_active', true)
    ->count();
echo "   - Found {$activeAccounts} active email accounts in workspace {$testWorkspaceId}\n";

// Show sample emails
echo "\n6. Sample emails (first 3):\n";
$samples = Email::take(3)->get();
foreach ($samples as $email) {
    echo "   - [{$email->id}] {$email->subject}\n";
    echo "     • workspace_id: {$email->workspace_id}\n";
    echo "     • email_account_id: {$email->email_account_id}\n";
}
