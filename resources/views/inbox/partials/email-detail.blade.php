<div class="email-detail-container">
    <!-- Email Header -->
    <div class="bg-white p-6 border-b">
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
        <div class="flex items-center space-x-4 text-sm">
            <!-- Status -->
            <div class="flex items-center space-x-2">
                <label class="text-gray-600">Status:</label>
                <select onchange="updateEmailStatus({{ $email->id }}, this.value)" class="px-2 py-1 border rounded">
                    <option value="unread" {{ $email->status == 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ $email->status == 'read' ? 'selected' : '' }}>Read</option>
                    <option value="replied" {{ $email->status == 'replied' ? 'selected' : '' }}>Replied</option>
                    <option value="archived" {{ $email->status == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>

            <!-- Assign To -->
            <div class="flex items-center space-x-2">
                <label class="text-gray-600">Assign to:</label>
                <select onchange="assignEmail({{ $email->id }}, this.value)" class="px-2 py-1 border rounded">
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
                <select onchange="setDisposition({{ $email->id }}, this.value)" class="px-2 py-1 border rounded">
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
    <div class="bg-white p-6">
        <div class="prose max-w-none">
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
    <div class="bg-gray-50 p-6 border-t">
        <button onclick="showReplyForm({{ $email->id }})" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Reply
        </button>
        <button onclick="showForwardForm({{ $email->id }})" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 ml-2">
            Forward
        </button>

        <!-- Reply Form (hidden by default) -->
        <div id="reply-form-{{ $email->id }}" class="hidden mt-4">
            <form onsubmit="sendReply(event, {{ $email->id }})">
                <textarea name="reply_body" rows="6" class="w-full p-3 border rounded" placeholder="Type your reply..."></textarea>
                <div class="mt-2 flex space-x-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Send Reply
                    </button>
                    <button type="button" onclick="hideReplyForm({{ $email->id }})" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
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

function showReplyForm(emailId) {
    document.getElementById(`reply-form-${emailId}`).classList.remove('hidden');
}

function hideReplyForm(emailId) {
    document.getElementById(`reply-form-${emailId}`).classList.add('hidden');
}

function showNotification(message) {
    // Simple notification - you can enhance this
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
