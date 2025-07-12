@extends('layouts.dashboard')

@section('title', 'Inbox - CollaborInbox')

@section('body-class', 'inbox-page')

@section('page-styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Modern color palette */
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --primary: 222.2 47.4% 11.2%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96.1%;
            --secondary-foreground: 222.2 47.4% 11.2%;
            --muted: 210 40% 96.1%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: 210 40% 96.1%;
            --accent-foreground: 222.2 47.4% 11.2%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 222.2 84% 4.9%;
            --radius: 0.5rem;
        }

        /* Modern inbox layout */
        .inbox-layout {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 1rem;
            height: calc(100vh - 140px);
        }

        .email-list-sidebar {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .email-detail {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            overflow-y: auto;
            padding: 2rem;
        }

        /* Stats row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            padding: 1rem;
            border-bottom: 1px solid hsl(var(--border));
        }

        .stat-card {
            text-align: center;
            padding: 0.5rem;
            background: hsl(var(--muted));
            border-radius: calc(var(--radius) - 2px);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: hsl(var(--foreground));
        }

        .stat-label {
            font-size: 0.75rem;
            color: hsl(var(--muted-foreground));
            margin-top: 0.25rem;
        }

        /* Filters */
        .filters-bar {
            padding: 1rem;
            border-bottom: 1px solid hsl(var(--border));
        }

        .search-bar {
            margin-bottom: 0.75rem;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            background: hsl(var(--background));
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            border-color: hsl(var(--primary));
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .filter-select {
            padding: 0.5rem 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            background: hsl(var(--background));
            font-size: 0.75rem;
            cursor: pointer;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .filter-select:focus {
            border-color: hsl(var(--primary));
        }

        /* Email list */
        .email-list {
            flex: 1;
            overflow-y: auto;
        }

        .email-item {
            padding: 1rem;
            border-bottom: 1px solid hsl(var(--border));
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .email-item:hover {
            background-color: hsl(var(--accent));
        }

        .email-item.unread {
            background-color: hsl(var(--muted));
        }

        .email-item.active {
            background-color: hsl(var(--secondary));
            position: relative;
        }

        .email-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: hsl(var(--primary));
        }

        .email-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.25rem;
        }

        .email-from {
            font-weight: 600;
            font-size: 0.875rem;
            color: hsl(var(--foreground));
        }

        .email-time {
            font-size: 0.75rem;
            color: hsl(var(--muted-foreground));
        }

        .email-subject {
            font-size: 0.875rem;
            font-weight: 500;
            color: hsl(var(--foreground));
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .email-preview {
            font-size: 0.75rem;
            color: hsl(var(--muted-foreground));
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .email-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .email-badge {
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.625rem;
            font-weight: 500;
        }

        .badge-disposition {
            background-color: var(--badge-color, hsl(var(--muted)));
            color: white;
        }

        .badge-assigned {
            background-color: hsl(var(--secondary));
            color: hsl(var(--secondary-foreground));
        }

        .star-icon {
            color: hsl(var(--muted-foreground));
            font-size: 0.75rem;
        }

        .star-icon.starred {
            color: #facc15;
        }

        .paperclip-icon {
            color: hsl(var(--muted-foreground));
            font-size: 0.75rem;
        }

        /* Empty states */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            text-align: center;
            color: hsl(var(--muted-foreground));
        }

        .empty-state-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: hsl(var(--foreground));
            margin-bottom: 0.5rem;
        }

        .empty-state-description {
            font-size: 0.875rem;
        }

        /* Pagination */
        .pagination-wrapper {
            padding: 1rem;
            border-top: 1px solid hsl(var(--border));
            display: flex;
            justify-content: center;
        }

        /* Spinner */
        .spinner {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: hsl(var(--primary));
            animation: spin 0.8s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .inbox-layout {
                grid-template-columns: 1fr;
            }

            .email-detail {
                display: none;
            }
        }
    </style>
@endsection

@section('page-content')
        <!-- Inbox Layout -->
        <div class="inbox-layout">
            <!-- Email List Sidebar -->
            <div class="email-list-sidebar">
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-value">{{ $stats['total'] }}</div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $stats['unread'] }}</div>
                        <div class="stat-label">Unread</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $stats['starred'] }}</div>
                        <div class="stat-label">Starred</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $stats['unassigned'] }}</div>
                        <div class="stat-label">Unassigned</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-bar">
                    <form method="GET" action="{{ route('inbox.index') }}" id="filterForm">
                        <div class="search-bar">
                            <input type="text" name="search" class="search-input" placeholder="Search emails..." value="{{ request('search') }}">
                        </div>
                        
                        <div class="filters-grid">
                            <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all">All Status</option>
                                <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                                <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                                <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Replied</option>
                                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            
                            <select name="account" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all">All Accounts</option>
                                @foreach($emailAccounts as $account)
                                    <option value="{{ $account->id }}" {{ request('account') == $account->id ? 'selected' : '' }}>
                                        {{ $account->email_address }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <select name="disposition" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all">All Dispositions</option>
                                @foreach($dispositions as $disposition)
                                    <option value="{{ $disposition->id }}" {{ request('disposition') == $disposition->id ? 'selected' : '' }}>
                                        {{ $disposition->name }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <select name="assigned" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all">All Users</option>
                                <option value="unassigned" {{ request('assigned') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('assigned') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Email List -->
                <div class="email-list" id="emailList">
                    @forelse($emails as $email)
                        <div class="email-item {{ $email->status == 'unread' ? 'unread' : '' }}" 
                             data-email-id="{{ $email->id }}"
                             onclick="loadEmail({{ $email->id }})">
                            <div class="email-item-header">
                                <div class="email-from">{{ $email->from_name ?: $email->from_email }}</div>
                                <div class="email-time">{{ $email->received_at->diffForHumans() }}</div>
                            </div>
                            <div class="email-subject">{{ $email->subject }}</div>
                            <div class="email-preview">{{ $email->getPreviewText() }}</div>
                            <div class="email-meta">
                                @if($email->is_starred)
                                    <i class="fas fa-star star-icon starred"></i>
                                @endif
                                @if($email->has_attachments)
                                    <i class="fas fa-paperclip paperclip-icon"></i>
                                @endif
                                @if($email->disposition)
                                    <span class="email-badge badge-disposition" style="--badge-color: {{ $email->disposition->color }};">
                                        {{ $email->disposition->name }}
                                    </span>
                                @endif
                                @if($email->assignedUser)
                                    <span class="email-badge badge-assigned">
                                        {{ $email->assignedUser->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <svg class="empty-state-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <div class="empty-state-title">No emails found</div>
                            <div class="empty-state-description">Try adjusting your filters or search criteria</div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($emails->hasPages())
                    <div class="pagination-wrapper">
                        {{ $emails->links() }}
                    </div>
                @endif
            </div>

            <!-- Email Detail -->
            <div class="email-detail" id="emailDetail">
                <div class="empty-state">
                    <svg class="empty-state-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <div class="empty-state-title">Select an email to read</div>
                    <div class="empty-state-description">Choose an email from the list to view its contents</div>
                </div>
            </div>
        </div>
@endsection

@section('page-scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    let currentEmailId = null;

    // User menu dropdown
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userMenu = document.getElementById('userMenu');
    
    userMenuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        userMenu.classList.toggle('show');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        userMenu.classList.remove('show');
    });

    // Load email function
    function loadEmail(emailId) {
        currentEmailId = emailId;
        
        // Mark active email in list
        document.querySelectorAll('.email-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-email-id="${emailId}"]`).classList.add('active');
        
        // Show loading state
        document.getElementById('emailDetail').innerHTML = `
            <div class="empty-state">
                <div class="spinner"></div>
                <div class="loading-text">Loading email...</div>
            </div>
        `;
        
        // Fetch email details
        fetch(`/inbox/email/${emailId}`)
            .then(response => response.text())
            .then(html => {
                // Create a temporary div to parse the response
                const temp = document.createElement('div');
                temp.innerHTML = html;
                
                // Extract the email detail content
                const emailContent = temp.querySelector('#emailDetailContent');
                if (emailContent) {
                    document.getElementById('emailDetail').innerHTML = emailContent.innerHTML;
                }
                
                // Update email status in list if it was unread
                const emailItem = document.querySelector(`[data-email-id="${emailId}"]`);
                if (emailItem && emailItem.classList.contains('unread')) {
                    emailItem.classList.remove('unread');
                    
                    // Update unread count
                    const unreadStat = document.querySelector('.stat-value');
                    if (unreadStat) {
                        const currentCount = parseInt(unreadStat.textContent);
                        if (currentCount > 0) {
                            unreadStat.textContent = currentCount - 1;
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error loading email:', error);
                document.getElementById('emailDetail').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-title">Error loading email</div>
                        <div class="empty-state-description">Please try again later</div>
                    </div>
                `;
            });
    }

    // Star toggle function
    function toggleStar(emailId) {
        fetch(`/inbox/email/${emailId}/star`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            const starIcon = document.querySelector(`#star-${emailId}`);
            if (starIcon) {
                if (data.starred) {
                    starIcon.classList.add('starred');
                } else {
                    starIcon.classList.remove('starred');
                }
            }
        });
    }

    // Assign user function
    function assignUser(emailId) {
        const userId = document.querySelector('#assignUserSelect').value;
        
        fetch(`/inbox/email/${emailId}/assign`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the email to show updated assignment
                loadEmail(emailId);
            }
        });
    }

    // Set disposition function
    function setDisposition(emailId) {
        const dispositionId = document.querySelector('#dispositionSelect').value;
        
        fetch(`/inbox/email/${emailId}/disposition`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ disposition_id: dispositionId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the email to show updated disposition
                loadEmail(emailId);
            }
        });
    }

    // Reply function
    function showReplyForm(emailId) {
        const replySection = document.querySelector('.reply-section');
        if (replySection) {
            replySection.style.display = 'block';
            document.querySelector('#replyBody').focus();
        }
    }

    function sendReply(emailId) {
        const form = document.querySelector('#replyForm');
        const formData = new FormData(form);
        
        const data = {
            to_email: formData.get('to_email'),
            subject: formData.get('subject'),
            body_html: formData.get('body_html'),
            cc: formData.get('cc') ? formData.get('cc').split(',').map(e => e.trim()) : [],
            bcc: formData.get('bcc') ? formData.get('bcc').split(',').map(e => e.trim()) : []
        };
        
        fetch(`/inbox/email/${emailId}/reply`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the email to show the reply
                loadEmail(emailId);
            }
        });
    }

    // Update status function
    function updateStatus(emailId, status) {
        fetch(`/inbox/email/${emailId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI based on status
                const emailItem = document.querySelector(`[data-email-id="${emailId}"]`);
                if (status === 'unread') {
                    emailItem.classList.add('unread');
                } else {
                    emailItem.classList.remove('unread');
                }
                
                if (status === 'archived' || status === 'trash') {
                    // Remove from list
                    emailItem.remove();
                    // Clear detail view
                    document.getElementById('emailDetail').innerHTML = `
                        <div class="empty-state">
                            <svg class="empty-state-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <div class="empty-state-title">Select an email to read</div>
                            <div class="empty-state-description">Choose an email from the list to view its contents</div>
                        </div>
                    `;
                }
            }
        });
    }

    // Auto-submit search form on enter
    document.querySelector('.search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('filterForm').submit();
        }
    });

    // Load first email if on desktop
    window.addEventListener('load', function() {
        if (window.innerWidth > 768) {
            const firstEmail = document.querySelector('.email-item');
            if (firstEmail) {
                const emailId = firstEmail.getAttribute('data-email-id');
                if (emailId) {
                    loadEmail(emailId);
                }
            }
        }
    });
</script>
@endsection
