<div id="emailDetailContent" class="email-detail-container" style="background: #fafafa; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <!-- Email Header -->
    <div class="bg-white p-6 border-b" style="background: white; border-bottom: 1px solid #e5e7eb;">
        <div class="flex justify-between items-start mb-4">
            <h2 class="text-xl font-semibold">{{ $email->subject }}</h2>
            <div class="flex space-x-2">
                <button onclick="toggleStar({{ $email->id }})" class="p-2 hover:bg-gray-100 rounded">
                    <svg class="w-5 h-5 {{ $email->is_starred ? 'text-yellow-500 fill-current' : 'text-gray-400' }}" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </button>
                <button onclick="closeEmailDetail()" class="p-2 hover:bg-gray-100 rounded">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Sender Info -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="bg-gray-300 rounded-full h-10 w-10 flex items-center justify-center mr-3">
                    <span class="text-gray-600 font-medium">{{ strtoupper(substr($email->from_name ?: $email->from_email, 0, 1)) }}</span>
                </div>
                <div>
                    <div class="font-medium">{{ $email->from_name ?: $email->from_email }}</div>
                    <div class="text-sm text-gray-500">{{ $email->from_email }}</div>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                {{ $email->received_at->format('M d, Y g:i A') }}
            </div>
        </div>

        <!-- Recipients -->
        <div class="text-sm text-gray-600 mb-4">
            <div><span class="font-medium">To:</span> {{ $email->to_email }}</div>
            @if($email->cc_email)
                <div><span class="font-medium">Cc:</span> {{ $email->cc_email }}</div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center space-x-4 text-sm bg-gray-100 p-4 rounded" style="background: #f3f4f6; border-radius: 8px; margin-top: 16px; border: 1px solid #e5e7eb;">
            <!-- Status -->
            <div class="flex items-center space-x-2">
                <label class="text-gray-600">Status:</label>
                <select onchange="updateEmailStatus({{ $email->id }}, this.value)" class="px-3 py-2 border rounded" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; min-width: 120px;">
                    <option value="unread" {{ $email->status == 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ $email->status == 'read' ? 'selected' : '' }}>Read</option>
                    <option value="replied" {{ $email->status == 'replied' ? 'selected' : '' }}>Replied</option>
                    <option value="archived" {{ $email->status == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>

            <!-- Assign To -->
            <div class="flex items-center space-x-2">
                <label class="text-gray-600">Assign to:</label>
                <select onchange="assignEmail({{ $email->id }}, this.value)" class="px-3 py-2 border rounded" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; min-width: 120px;">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $email->assigned_to == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Disposition -->
            @if($dispositions->count() > 0)
            <div class="flex items-center space-x-2">
                <label class="text-gray-600">Disposition:</label>
                <select onchange="setDisposition({{ $email->id }}, this.value)" class="px-3 py-2 border rounded" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; min-width: 120px;">
                    <option value="">None</option>
                    @foreach($dispositions as $disposition)
                        <option value="{{ $disposition->id }}" {{ $email->disposition_id == $disposition->id ? 'selected' : '' }}>
                            {{ $disposition->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
    </div>

    <!-- Email Body -->
    <div class="bg-white p-6" style="background: white; line-height: 1.6; color: #374151;">
        <div class="prose max-w-none" style="font-size: 15px; line-height: 1.7; color: #374151;">
            @if($email->body_html)
                {!! $email->body_html !!}
            @else
                <p style="white-space: pre-wrap;">{{ $email->body_text }}</p>
            @endif
        </div>

        <!-- Attachments -->
        @if($email->attachments->count() > 0)
        <div class="mt-6 border-t pt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Attachments ({{ $email->attachments->count() }})</h4>
            <div class="space-y-2">
                @foreach($email->attachments as $attachment)
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        <span class="text-sm">{{ $attachment->filename }}</span>
                        <span class="text-xs text-gray-500 ml-2">({{ number_format($attachment->size / 1024, 1) }} KB)</span>
                    </div>
                    <a href="{{ route('inbox.attachment.download', $attachment->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        Download
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Reply Section -->
    <div class="reply-section testtt" style="padding: 1.5rem; border-top: 1px solid #e5e7eb; background: #ffffff; border-radius: 0 0 8px 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);">
        <div class="reply-actions" style="margin-bottom: 1rem; display: flex; gap: 0.75rem; align-items: center;">
            <button onclick="showReplyForm({{ $email->id }})" class="btn btn-primary reply-btn" style="background: #2563eb; color: white; border: none; border-radius: 6px; padding: 0.625rem 1.25rem; font-size: 14px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);">
                <i class="fas fa-reply"></i> Reply
            </button>
            <button onclick="showForwardForm({{ $email->id }})" class="btn btn-secondary" style="background: #6b7280; color: white; border: none; border-radius: 6px; padding: 0.625rem 1.25rem; font-size: 14px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);">
                <i class="fas fa-share"></i> Forward
            </button>
            <button onclick="toggleStar({{ $email->id }})" id="star-btn-{{ $email->id }}" class="btn btn-star" style="background: {{ $email->is_starred ? '#fbbf24' : 'transparent' }}; border: 1px solid #fbbf24; color: {{ $email->is_starred ? 'white' : '#fbbf24' }}; border-radius: 6px; padding: 0.625rem 1rem; font-size: 14px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.15s ease;">
                <i id="star-icon-{{ $email->id }}" class="fas fa-star"></i> {{ $email->is_starred ? 'Starred' : 'Star' }}
            </button>
        </div>

        <!-- Reply Form (hidden by default) -->
        <div id="reply-form-{{ $email->id }}" class="reply-form" style="background: #f9fafb; border-radius: 8px; padding: 1.25rem; margin-top: 1rem; border: 1px solid #e5e7eb; display: none;">
            <form onsubmit="sendReply(event, {{ $email->id }})" style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- From Account Selector -->
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <label for="reply-from-{{ $email->id }}" style="font-size: 14px; font-weight: 500; color: #374151;">Send from</label>
                    <select id="reply-from-{{ $email->id }}" name="from_account_id" class="px-3 py-2 border rounded" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; min-width: 200px;">
                        @php
                            // FIX: Use the email's workspace_id to get the correct accounts
                            $accounts = App\Models\EmailAccount::where('workspace_id', $email->workspace_id)->where('is_active', true)->get();
                        @endphp
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $account->id == $email->email_account_id ? 'selected' : '' }}>
                                {{ $account->from_name ? $account->from_name . ' <' . $account->email_address . '>' : $account->email_address }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <label for="reply-editor-{{ $email->id }}" style="font-size: 14px; font-weight: 500; color: #374151;">Your Reply</label>
                    <div id="reply-editor-{{ $email->id }}" style="min-height: 200px; background: white; border-radius: 6px; border: 1px solid #d1d5db;"></div>
                    <input type="hidden" id="reply-body-{{ $email->id }}" name="reply_body" value="">
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <button type="submit" class="btn btn-primary" style="background: #2563eb; color: white; border: none; border-radius: 6px; padding: 0.625rem 1.25rem; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                    <button type="button" onclick="hideReplyForm({{ $email->id }})" class="btn btn-outline" style="background: white; color: #6b7280; border: 1px solid #d1d5db; border-radius: 6px; padding: 0.625rem 1.25rem; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.15s ease;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Thread/Previous Emails -->
    @if($threadEmails->count() > 0)
    <div class="bg-white border-t p-6">
        <h3 class="text-lg font-medium mb-4">Previous emails in this thread</h3>
        <div class="space-y-4">
            @foreach($threadEmails as $threadEmail)
            <div class="border rounded p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="font-medium">{{ $threadEmail->from_name ?: $threadEmail->from_email }}</div>
                        <div class="text-sm text-gray-500">{{ $threadEmail->received_at->format('M d, Y g:i A') }}</div>
                    </div>
                </div>
                <div class="text-sm text-gray-700">
                    @if($threadEmail->body_text)
                        <p>{{ Str::limit($threadEmail->body_text, 200) }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
// Functions for email actions
function toggleStar(emailId) {
    fetch(`/inbox/email/${emailId}/star`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update star icon
        location.reload(); // Simple reload for now
    });
}

function updateEmailStatus(emailId, status) {
    fetch(`/inbox/email/${emailId}/status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Status updated');
        }
    });
}

function assignEmail(emailId, userId) {
    fetch(`/inbox/email/${emailId}/assign`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: userId || null })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Assignment updated');
        }
    });
}

function setDisposition(emailId, dispositionId) {
    fetch(`/inbox/email/${emailId}/disposition`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ disposition_id: dispositionId || null })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Disposition updated');
        }
    });
}

// Reply form functions are now handled by the main page JavaScript
// These functions are placeholders that will be overridden by the main page functions

// showReplyForm, hideReplyForm, and sendReply are defined in the main page
// and will work with the Quill.js editor

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    const bgColor = type === 'error' ? 'bg-red-500' : 'bg-green-500';
    notification.className = `fixed bottom-4 right-4 ${bgColor} text-white px-4 py-2 rounded shadow-lg z-50`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Add CSS for enhanced interactions
const style = document.createElement('style');
style.textContent = `
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    }
    
    .btn:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
    }
    
    .btn-primary:hover {
        background: #1d4ed8 !important;
    }
    
    .btn-secondary:hover {
        background: #4b5563 !important;
    }
    
    .btn-star:hover {
        background: #fbbf24 !important;
        color: white !important;
    }
    
    .btn-outline:hover {
        background: #f3f4f6 !important;
        border-color: #9ca3af !important;
    }
    
    .reply-textarea:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    .reply-form {
        animation: slideDown 0.2s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>
