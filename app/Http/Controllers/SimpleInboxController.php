<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\Disposition;
use App\Models\EmailReply;
use App\Models\EmailAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SimpleInboxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Get the current workspace ID from session or user's workspace
     */
    private function getCurrentWorkspaceId()
    {
        if (session('workspace_id')) {
            return session('workspace_id');
        }
        
        $user = Auth::user();
        if ($user && $user->workspace_id) {
            return $user->workspace_id;
        }
        
        // Default to 1 if no workspace is found
        return 1;
    }

    public function index(Request $request)
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        // Get email accounts
        $emailAccounts = EmailAccount::where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->get();

        // Get dispositions - for now, get all active dispositions
        // In a real implementation, you'd filter by the workspace's tenant
        $dispositions = Disposition::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Get users for assignment - simplified
        $users = User::all();

        // Build email query
        $query = Email::where('workspace_id', $workspaceId)
            ->with(['emailAccount', 'assignedUser', 'disposition', 'attachments']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('account') && $request->account !== 'all') {
            $query->where('email_account_id', $request->account);
        }

        if ($request->has('disposition') && $request->disposition !== 'all') {
            $query->where('disposition_id', $request->disposition);
        }

        if ($request->has('assigned') && $request->assigned !== 'all') {
            if ($request->assigned === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned);
            }
        }

        if ($request->has('starred') && $request->starred === '1') {
            $query->where('is_starred', true);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', $search)
                    ->orWhere('from_email', 'like', $search)
                    ->orWhere('from_name', 'like', $search)
                    ->orWhere('body_text', 'like', $search);
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'received_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $emails = $query->paginate(50);

        // Get statistics
        $stats = [
            'total' => Email::where('workspace_id', $workspaceId)->count(),
            'unread' => Email::where('workspace_id', $workspaceId)->where('status', 'unread')->count(),
            'starred' => Email::where('workspace_id', $workspaceId)->where('is_starred', true)->count(),
            'unassigned' => Email::where('workspace_id', $workspaceId)->whereNull('assigned_to')->count(),
        ];

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
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)
            ->with(['emailAccount', 'assignedUser', 'disposition', 'attachments', 'replies.user'])
            ->findOrFail($id);

        // Mark as read
        if (method_exists($email, 'markAsRead')) {
            $email->markAsRead();
        } else {
            $email->update(['status' => 'read', 'read_at' => now()]);
        }

        // Get thread emails if any
        $threadEmails = [];
        if ($email->thread_id) {
            $threadEmails = Email::where('workspace_id', $workspaceId)
                ->where('thread_id', $email->thread_id)
                ->where('id', '!=', $email->id)
                ->orderBy('received_at', 'asc')
                ->get();
        }

        // Get dispositions - for now, get all active dispositions
        $dispositions = Disposition::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $users = User::all();

        // If it's an AJAX request, return partial view
        if (request()->ajax()) {
            return view('inbox.partials.email-detail', compact('email', 'threadEmails', 'dispositions', 'users'));
        }

        return view('inbox.show', compact('email', 'threadEmails', 'dispositions', 'users'));
    }

    public function toggleStar($id)
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)->findOrFail($id);
        
        if (method_exists($email, 'toggleStar')) {
            $email->toggleStar();
        } else {
            $email->update(['is_starred' => !$email->is_starred]);
        }

        return response()->json(['starred' => $email->is_starred]);
    }

    public function updateStatus(Request $request, $id)
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:unread,read,replied,forwarded,archived,spam,trash'
        ]);

        $email->update(['status' => $request->status]);

        if ($request->status === 'read') {
            $email->update(['read_at' => now()]);
        } elseif ($request->status === 'unread') {
            $email->update(['read_at' => null]);
        }

        return response()->json(['success' => true]);
    }

    public function assign(Request $request, $id)
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)->findOrFail($id);
        
        $request->validate([
            'user_id' => 'nullable|exists:users,id'
        ]);

        if (method_exists($email, 'assignTo')) {
            $email->assignTo($request->user_id);
        } else {
            $email->update([
                'assigned_to' => $request->user_id,
                'assigned_at' => $request->user_id ? now() : null
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function setDisposition(Request $request, $id)
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)->findOrFail($id);
        
        $request->validate([
            'disposition_id' => 'nullable|exists:dispositions,id'
        ]);

        if (method_exists($email, 'setDisposition')) {
            $email->setDisposition($request->disposition_id);
        } else {
            $email->update(['disposition_id' => $request->disposition_id]);
        }

        return response()->json(['success' => true]);
    }
    
    public function reply(Request $request, $id)
    {
        // Placeholder for reply functionality
        return response()->json(['success' => false, 'message' => 'Reply functionality not yet implemented']);
    }
    
    public function downloadAttachment($id)
    {
        $attachment = EmailAttachment::findOrFail($id);
        
        // Verify the attachment belongs to the current workspace
        $workspaceId = $this->getCurrentWorkspaceId();
        $email = Email::where('workspace_id', $workspaceId)
            ->where('id', $attachment->email_id)
            ->first();
            
        if (!$email) {
            abort(403);
        }

        if (!Storage::exists($attachment->storage_path)) {
            abort(404, 'File not found');
        }

        return Storage::download($attachment->storage_path, $attachment->filename);
    }
    
    public function bulkAction(Request $request)
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $request->validate([
            'email_ids' => 'required|array',
            'email_ids.*' => 'integer',
            'action' => 'required|in:read,unread,star,unstar,archive,trash,assign,disposition',
            'user_id' => 'required_if:action,assign|nullable|exists:users,id',
            'disposition_id' => 'required_if:action,disposition|nullable|exists:dispositions,id',
        ]);

        $emails = Email::where('workspace_id', $workspaceId)
            ->whereIn('id', $request->email_ids)
            ->get();

        foreach ($emails as $email) {
            switch ($request->action) {
                case 'read':
                    $email->update(['status' => 'read', 'read_at' => now()]);
                    break;
                case 'unread':
                    $email->update(['status' => 'unread', 'read_at' => null]);
                    break;
                case 'star':
                    $email->update(['is_starred' => true]);
                    break;
                case 'unstar':
                    $email->update(['is_starred' => false]);
                    break;
                case 'archive':
                    $email->update(['status' => 'archived']);
                    break;
                case 'trash':
                    $email->update(['status' => 'trash']);
                    break;
                case 'assign':
                    $email->update([
                        'assigned_to' => $request->user_id,
                        'assigned_at' => $request->user_id ? now() : null
                    ]);
                    break;
                case 'disposition':
                    $email->update(['disposition_id' => $request->disposition_id]);
                    break;
            }
        }

        return response()->json(['success' => true, 'affected' => $emails->count()]);
    }
}
