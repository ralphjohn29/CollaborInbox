<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Email;

$count = Email::count();
echo "Total emails in database: $count\n";

// Show last 5 emails
$emails = Email::orderBy('received_at', 'desc')->take(5)->get();
echo "\nLast 5 emails:\n";
foreach ($emails as $email) {
    echo "- [{$email->received_at}] From: {$email->from_email} - Subject: {$email->subject}\n";
}
