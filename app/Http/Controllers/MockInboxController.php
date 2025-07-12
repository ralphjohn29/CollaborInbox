<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MockInboxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Mock data for testing
        $emails = collect([
            (object)[
                'id' => 1,
                'subject' => 'Welcome to CollaborInbox',
                'from_email' => 'support@collaborinbox.com',
                'from_name' => 'CollaborInbox Support',
                'to_email' => 'test@example.com',
                'body_text' => 'Welcome! This is a test email.',
                'status' => 'unread',
                'is_starred' => false,
                'received_at' => now()->subHours(2),
                'assigned_to' => null,
                'disposition_id' => null,
            ],
            (object)[
                'id' => 2,
                'subject' => 'Your account is ready',
                'from_email' => 'noreply@collaborinbox.com',
                'from_name' => 'CollaborInbox',
                'to_email' => 'test@example.com',
                'body_text' => 'Your account has been set up successfully.',
                'status' => 'read',
                'is_starred' => true,
                'received_at' => now()->subDays(1),
                'assigned_to' => 1,
                'disposition_id' => null,
            ],
        ]);

        // Mock statistics
        $stats = [
            'total' => 2,
            'unread' => 1,
            'starred' => 1,
            'unassigned' => 1,
        ];

        // Mock email accounts
        $emailAccounts = collect([
            (object)[
                'id' => 1,
                'email_address' => 'support@collaborinbox.com',
                'display_name' => 'Support',
                'is_active' => true,
            ],
        ]);

        // Mock dispositions
        $dispositions = collect([
            (object)[
                'id' => 1,
                'name' => 'Resolved',
                'color' => '#10B981',
            ],
            (object)[
                'id' => 2,
                'name' => 'Pending',
                'color' => '#F59E0B',
            ],
        ]);

        // Mock users
        $users = collect([
            (object)[
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
            ],
        ]);

        // Create a simple paginator
        $emails = new \Illuminate\Pagination\LengthAwarePaginator(
            $emails,
            count($emails),
            50,
            $request->get('page', 1),
            ['path' => $request->url()]
        );

        return view('inbox.index', compact(
            'emails', 
            'emailAccounts', 
            'dispositions', 
            'users', 
            'stats'
        ));
    }

    public function show($id)
    {
        // Mock email data
        $email = (object)[
            'id' => $id,
            'subject' => 'Test Email',
            'from_email' => 'sender@example.com',
            'from_name' => 'Test Sender',
            'to_email' => 'test@example.com',
            'body_html' => '<p>This is a test email body.</p>',
            'body_text' => 'This is a test email body.',
            'status' => 'read',
            'is_starred' => false,
            'received_at' => now(),
            'assigned_to' => null,
            'disposition_id' => null,
            'thread_id' => null,
            'attachments' => collect([]),
            'replies' => collect([]),
        ];

        $threadEmails = collect([]);
        $dispositions = collect([]);
        $users = collect([]);

        if (request()->ajax()) {
            return view('inbox.partials.email-detail', compact('email', 'threadEmails', 'dispositions', 'users'));
        }

        return view('inbox.show', compact('email', 'threadEmails', 'dispositions', 'users'));
    }

    // Other methods return mock responses
    public function toggleStar($id)
    {
        return response()->json(['starred' => true]);
    }

    public function updateStatus(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }

    public function assign(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }

    public function setDisposition(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }
}
