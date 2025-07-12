<div id="emailDetailContent">
    <div class="email-detail-header">
        <div class="email-actions">
            <button class="btn btn-primary" onclick="showReplyForm({{ $email->id }})">
                <i class="fas fa-reply"></i> Reply
            </button>
            <button class="btn btn-outline" onclick="toggleStar({{ $email->id }})">
                <i id="star-{{ $email->id }}" class="fas fa-star star-icon {{ $email->is_starred ? 'starred' : '' }}"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-outline dropdown-toggle" onclick="toggleDropdown('statusDropdown')">
                    <i class="fas fa-tag"></i> Status
                </button>
                <div class="dropdown-menu" id="statusDropdown">
                    <a href="#" class="dropdown-item" onclick="updateStatus({{ $email->id }}, 'unread')">Mark as Unread</a>
                    <a href="#" class="dropdown-item" onclick="updateStatus({{ $email->id }}, 'archived')">Archive</a>
                    <a href="#" class="dropdown-item" onclick="updateStatus({{ $email->id }}, 'spam')">Mark as Spam</a>
                    <a href="#" class="dropdown-item" onclick="updateStatus({{ $email->id }}, 'trash')">Move to Trash</a>
                </div>
            </div>
            <button class="btn btn-outline back-button" onclick="hideEmailDetail()">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>
        
        <div class="email-detail-subject">{{ $email->subject }}</div>
        
        <div class="email-detail-meta">
            <div>
                <strong>From:</strong> {{ $email->from_name ?: $email->from_email }} &lt;{{ $email->from_email }}&gt;
            </div>
            <div>
                <strong>To:</strong> {{ $email->to_email }}
            </div>
            <div>
                <strong>Date:</strong> {{ $email->received_at->format('F j, Y, g:i a') }}
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <div style="flex: 1;">
                <label style="font-size: 0.875rem; color: hsl(var(--muted-foreground)); margin-bottom: 0.25rem; display: block;">Assigned To</label>
                <select id="assignUserSelect" class="filter-select" onchange="assignUser({{ $email->id }})">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $email->assigned_to == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 0.875rem; color: hsl(var(--muted-foreground)); margin-bottom: 0.25rem; display: block;">Disposition</label>
                <select id="dispositionSelect" class="filter-select" onchange="setDisposition({{ $email->id }})">
                    <option value="">No Disposition</option>
                    @foreach($dispositions as $disposition)
                        <option value="{{ $disposition->id }}" {{ $email->disposition_id == $disposition->id ? 'selected' : '' }}>
                            {{ $disposition->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    
    <div class="email-detail-content">
        <div class="email-detail-body">
            @if($email->body_html)
                {!! $email->body_html !!}
            @else
                {!! nl2br(e($email->body_text)) !!}
            @endif
        </div>
        
        @if($email->attachments->count() > 0)
            <div class="attachments-section">
                <h3 class="attachments-title">Attachments ({{ $email->attachments->count() }})</h3>
                <div class="attachments-list">
                    @foreach($email->attachments as $attachment)
                        <a href="{{ route('inbox.attachment.download', $attachment->id) }}" class="attachment-item" target="_blank">
                            <div class="attachment-icon">
                                @if($attachment->isImage())
                                    <i class="fas fa-image"></i>
                                @elseif($attachment->isPdf())
                                    <i class="fas fa-file-pdf"></i>
                                @elseif($attachment->isDocument())
                                    <i class="fas fa-file-alt"></i>
                                @else
                                    <i class="fas fa-file"></i>
                                @endif
                            </div>
                            <div class="attachment-info">
                                <div class="attachment-name">{{ $attachment->filename }}</div>
                                <div class="attachment-size">{{ $attachment->getSizeFormatted() }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        
        @if($threadEmails->count() > 0)
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid hsl(var(--border));">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Thread History</h3>
                @foreach($threadEmails as $threadEmail)
                    <div style="padding: 1rem; border: 1px solid hsl(var(--border)); border-radius: calc(var(--radius) - 2px); margin-bottom: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <strong>{{ $threadEmail->from_name ?: $threadEmail->from_email }}</strong>
                            <span style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">
                                {{ $threadEmail->received_at->format('M j, g:i a') }}
                            </span>
                        </div>
                        <div style="color: hsl(var(--muted-foreground)); font-size: 0.875rem;">
                            {{ $threadEmail->getPreviewText(200) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
        @if($email->replies->count() > 0)
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid hsl(var(--border));">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Replies</h3>
                @foreach($email->replies as $reply)
                    <div style="padding: 1rem; border: 1px solid hsl(var(--border)); border-radius: calc(var(--radius) - 2px); margin-bottom: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <strong>{{ $reply->user->name }}</strong>
                            <span style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">
                                {{ $reply->sent_at->format('M j, g:i a') }}
                            </span>
                        </div>
                        <div>
                            @if($reply->body_html)
                                {!! $reply->body_html !!}
                            @else
                                {!! nl2br(e($reply->body_text)) !!}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    
    <div class="reply-section" style="display: none;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Reply to Email</h3>
        <form id="replyForm" onsubmit="event.preventDefault(); sendReply({{ $email->id }});">
            <div class="reply-form">
                <input type="email" name="to_email" class="reply-input" placeholder="To" value="{{ $email->from_email }}" required>
                <input type="text" name="cc" class="reply-input" placeholder="CC (comma separated)">
                <input type="text" name="bcc" class="reply-input" placeholder="BCC (comma separated)">
                <input type="text" name="subject" class="reply-input" placeholder="Subject" value="Re: {{ $email->subject }}" required>
                <textarea id="replyBody" name="body_html" class="reply-input reply-textarea" placeholder="Write your reply..." required></textarea>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                    <button type="button" class="btn btn-outline" onclick="document.querySelector('.reply-section').style.display='none';">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.classList.toggle('show');
    
    // Close when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!e.target.closest('.dropdown')) {
            dropdown.classList.remove('show');
            document.removeEventListener('click', closeDropdown);
        }
    });
}

function hideEmailDetail() {
    document.querySelector('.email-detail').classList.remove('show');
}
</script>
