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
use App\Services\TenantManager;

class InboxController extends Controller
{
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
        $this->middleware('auth');
    }
    
    /**
     * Get the current workspace ID from session or user's first workspace
     */
    private function getCurrentWorkspaceId()
    {
        return session('workspace_id') ?? Auth::user()->workspaces()->first()->id ?? 1;
    }

    public function index(Request $request)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        if (!$tenant) {
            abort(403, 'No tenant context');
        }

        // Get the current workspace ID
        $workspaceId = $this->getCurrentWorkspaceId();
        
        // Get email accounts
        $emailAccounts = EmailAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        // Get dispositions
        $dispositions = Disposition::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Get users for assignment
        $users = User::where('tenant_id', $tenant->id)->get();

        // Build email query - use workspace_id instead of tenant_id
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

        // Get statistics - use workspace_id
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
        $tenant = $this->tenantManager->getCurrentTenant();
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)
            ->with(['emailAccount', 'assignedUser', 'disposition', 'attachments', 'replies.user'])
            ->findOrFail($id);

        // Mark as read
        $email->markAsRead();

        // Get thread emails if any
        $threadEmails = [];
        if ($email->thread_id) {
            $threadEmails = Email::where('workspace_id', $workspaceId)
                ->where('thread_id', $email->thread_id)
                ->where('id', '!=', $email->id)
                ->orderBy('received_at', 'asc')
                ->get();
        }

        $dispositions = Disposition::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $users = User::where('tenant_id', $tenant->id)->get();

        // If it's an AJAX request, return partial view
        if (request()->ajax()) {
            return view('inbox.partials.email-detail', compact('email', 'threadEmails', 'dispositions', 'users'));
        }

        return view('inbox.show', compact('email', 'threadEmails', 'dispositions', 'users'));
    }

    public function toggleStar($id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)->findOrFail($id);
        $email->toggleStar();

        return response()->json(['starred' => $email->is_starred]);
    }

    public function updateStatus(Request $request, $id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:unread,read,replied,forwarded,archived,spam,trash'
        ]);

        $email->update(['status' => $request->status]);

        if ($request->status === 'read') {
            $email->markAsRead();
        } elseif ($request->status === 'unread') {
            $email->markAsUnread();
        }

        return response()->json(['success' => true]);
    }

    public function assign(Request $request, $id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)->findOrFail($id);
        
        $request->validate([
            'user_id' => 'nullable|exists:users,id'
        ]);

        $email->assignTo($request->user_id);

        return response()->json(['success' => true]);
    }

    public function setDisposition(Request $request, $id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $email = Email::where('workspace_id', $workspaceId)->findOrFail($id);
        
        $request->validate([
            'disposition_id' => 'nullable|exists:dispositions,id'
        ]);

        $email->setDisposition($request->disposition_id);

        return response()->json(['success' => true]);
    }

    public function reply(Request $request, $id)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $email = Email::where('tenant_id', $tenant->id)->findOrFail($id);
        
        $request->validate([
            'to_email' => 'required|email',
            'subject' => 'required|string',
            'body_html' => 'required|string',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'bcc' => 'nullable|array',
            'bcc.*' => 'email',
        ]);

        $reply = EmailReply::create([
            'email_id' => $email->id,
            'user_id' => Auth::id(),
            'to_email' => $request->to_email,
            'cc' => $request->cc,
            'bcc' => $request->bcc,
            'subject' => $request->subject,
            'body_html' => $request->body_html,
            'body_text' => strip_tags($request->body_html),
            'status' => 'sent', // In real implementation, this would be 'draft' until actually sent
            'sent_at' => now(),
        ]);

        // Update email status
        $email->update(['status' => 'replied']);

        // TODO: Actually send the email via SMTP

        return response()->json(['success' => true, 'reply' => $reply]);
    }

    public function downloadAttachment($id)
    {
        $attachment = EmailAttachment::findOrFail($id);
        
        // Verify the attachment belongs to the current tenant
        $tenant = $this->tenantManager->getCurrentTenant();
        if ($attachment->email->tenant_id !== $tenant->id) {
            abort(403);
        }

        if (!Storage::exists($attachment->storage_path)) {
            abort(404, 'File not found');
        }

        return Storage::download($attachment->storage_path, $attachment->filename);
    }

    public function bulkAction(Request $request)
    {
        $tenant = $this->tenantManager->getCurrentTenant();
        
        $request->validate([
            'email_ids' => 'required|array',
            'email_ids.*' => 'integer',
            'action' => 'required|in:read,unread,star,unstar,archive,trash,assign,disposition',
            'user_id' => 'required_if:action,assign|nullable|exists:users,id',
            'disposition_id' => 'required_if:action,disposition|nullable|exists:dispositions,id',
        ]);

        $emails = Email::where('tenant_id', $tenant->id)
            ->whereIn('id', $request->email_ids)
            ->get();

        foreach ($emails as $email) {
            switch ($request->action) {
                case 'read':
                    $email->markAsRead();
                    break;
                case 'unread':
                    $email->markAsUnread();
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
                    $email->assignTo($request->user_id);
                    break;
                case 'disposition':
                    $email->setDisposition($request->disposition_id);
                    break;
            }
        }

        return response()->json(['success' => true, 'affected' => $emails->count()]);
    }
}
