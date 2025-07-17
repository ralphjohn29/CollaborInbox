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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\Services\OutlookOAuthService;

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
        try {
            $workspaceId = $this->getCurrentWorkspaceId();

            // Try to find email by workspace_id first, then try other approaches
            $email = Email::where('workspace_id', $workspaceId)->with('emailAccount')->find($id);
            
            if (!$email) {
                // Fallback: try to find by id without workspace restriction
                $email = Email::with('emailAccount')->find($id);
            }
            
            if (!$email) {
                return response()->json([
                    'success' => false, 
                    'error' => 'Email not found'
                ], 404);
            }

        $request->validate([
            'reply_body' => 'required|string',
            'from_account_id' => 'nullable|integer|exists:email_accounts,id',
        ]);

            // Check if email has an associated account for sending
            if (!$email->emailAccount) {
                return response()->json([
                    'success' => false, 
                    'error' => 'No email account configured for sending replies'
                ], 400);
            }

            // Use selected account or fallback to original email's account
            $selectedAccountId = $request->from_account_id;
            if ($selectedAccountId) {
                $emailAccount = EmailAccount::where('workspace_id', $workspaceId)
                    ->where('is_active', true)
                    ->findOrFail($selectedAccountId);
            } else {
                $emailAccount = $email->emailAccount;
            }
            
            $user = Auth::user();
            
            // Create the reply record first
            $reply = EmailReply::create([
                'email_id' => $email->id,
                'user_id' => $user->id,
                'to_email' => $email->from_email,
                'subject' => 'Re: ' . $email->subject,
                'body_html' => $request->reply_body,
                'body_text' => strip_tags($request->reply_body),
                'status' => 'draft',
                'sent_at' => null,
            ]);

            try {
                // Update status to sending before attempting to send
                $reply->update(['status' => 'sending']);
                
                // Send the actual email using the selected account
                $sent = $this->sendReplyEmail($emailAccount, $email, $request->reply_body, $user);
                
                if ($sent) {
                    // Update reply status to sent
                    $reply->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                    
                    // Update original email status
                    $email->update(['status' => 'replied']);
                    
                    return response()->json(['success' => true, 'reply' => $reply]);
                } else {
                    // Update reply status to failed
                    $reply->update(['status' => 'failed']);
                    
                    return response()->json([
                        'success' => false, 
                        'error' => 'Failed to send reply email'
                    ], 500);
                }
            } catch (\Exception $e) {
                // Update reply status to failed
                $reply->update(['status' => 'failed']);
                
                \Log::error('Failed to send reply email: ' . $e->getMessage(), [
                    'email_id' => $email->id,
                    'account_id' => $emailAccount->id,
                    'exception' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false, 
                    'error' => 'Error sending reply: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Reply method error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'email_id' => $id,
                'exception' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
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
    
    /**
     * Send reply email using PHPMailer with OAuth2 support
     */
    private function sendReplyEmail($emailAccount, $originalEmail, $replyBody, $user)
    {
        Log::info('📧 Starting email send process', [
            'from' => $emailAccount->email_address,
            'to' => $originalEmail->from_email,
            'subject' => 'Re: ' . $originalEmail->subject,
            'user' => $user->name,
            'environment' => config('app.env'),
            'has_oauth_token' => !empty($emailAccount->oauth_access_token)
        ]);
        
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $emailAccount->outgoing_server_host;
            $mail->Port = $emailAccount->outgoing_server_port;
            
            // Set encryption
            if ($emailAccount->outgoing_server_encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($emailAccount->outgoing_server_encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            // For development/testing, allow self-signed certificates
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            // Authentication - check if we have OAuth tokens
            if ($emailAccount->oauth_access_token && $emailAccount->provider === 'outlook') {
                // Use OAuth2 authentication for Outlook with automatic token refresh
                $oauthService = new OutlookOAuthService();
                $validToken = $oauthService->getValidAccessToken($emailAccount);
                
                if (!$validToken) {
                    Log::error('❌ Failed to get valid OAuth token for SMTP');
                    return false;
                }
                
                $mail->SMTPAuth = true;
                $mail->AuthType = 'XOAUTH2';
                $mail->Username = $emailAccount->outgoing_server_username;
                $mail->Password = $validToken;
                
                Log::info('✅ Using OAuth2 authentication for Outlook SMTP with refreshed token');
            } else {
                // Use basic authentication
                $mail->SMTPAuth = true;
                $mail->Username = $emailAccount->outgoing_server_username;
                $mail->Password = $emailAccount->outgoing_server_password;
                
                Log::info('Using basic authentication for SMTP');
            }
            
            // Recipients
            $mail->setFrom($emailAccount->email_address, $emailAccount->from_name ?: $user->name);
            $mail->addAddress($originalEmail->from_email, $originalEmail->from_name);
            $mail->addReplyTo($emailAccount->email_address, $emailAccount->from_name ?: $user->name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Re: ' . $originalEmail->subject;
            $mail->Body = $this->formatReplyBody($replyBody, $originalEmail, $user);
            $mail->AltBody = strip_tags($replyBody);
            
            // Add headers for threading
            if ($originalEmail->message_id) {
                $mail->addCustomHeader('In-Reply-To', $originalEmail->message_id);
                $mail->addCustomHeader('References', $originalEmail->message_id);
            }
            
            // Send the email
            $result = $mail->send();
            
            Log::info('Reply email sent successfully', [
                'to' => $originalEmail->from_email,
                'subject' => $mail->Subject,
                'account' => $emailAccount->email_address,
                'auth_type' => $emailAccount->oauth_access_token ? 'OAuth2' : 'Basic'
            ]);
            
            return $result;
            
        } catch (PHPMailerException $e) {
            Log::error('PHPMailer error: ' . $e->getMessage(), [
                'account' => $emailAccount->email_address,
                'provider' => $emailAccount->provider,
                'has_oauth' => !empty($emailAccount->oauth_access_token),
                'error_details' => $e->getTraceAsString()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('General error sending reply: ' . $e->getMessage(), [
                'error_details' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Format the reply body with proper styling and original message context
     */
    private function formatReplyBody($replyBody, $originalEmail, $user)
    {
        $signature = $user->name;
        $timestamp = now()->format('M j, Y \a\t g:i A');
        
        $formattedBody = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='margin-bottom: 20px;'>
                {$replyBody}
            </div>
            
            <div style='margin: 20px 0; padding: 10px 0; border-top: 1px solid #eee;'>
                <p style='margin: 0; font-size: 12px; color: #666;'>
                    Best regards,<br>
                    {$signature}
                </p>
            </div>
            
            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;'>
                <p><strong>On {$originalEmail->received_at->format('M j, Y \a\t g:i A')}, {$originalEmail->from_name} &lt;{$originalEmail->from_email}&gt; wrote:</strong></p>
                <div style='margin-left: 20px; border-left: 2px solid #ddd; padding-left: 10px;'>
                    " . ($originalEmail->body_html ?: nl2br(e($originalEmail->body_text))) . "
                </div>
            </div>
        </div>
        ";
        
        return $formattedBody;
    }
}
