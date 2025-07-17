@extends('layouts.dashboard')

@section('title', 'Inbox - CollaborInbox')

@section('body-class', 'inbox-page')

@section('page-styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Quill.js Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
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
            padding: 0;
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: hsl(var(--foreground));
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 1rem;
        }

        /* Stats row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fafafa;
        }

        .stat-card {
            text-align: center;
            padding: 0.75rem 0.5rem;
            background: white;
            border-radius: 6px;
            border: 1px solid #f1f5f9;
            transition: all 0.2s ease;
            position: relative;
        }

        .stat-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-color: #e2e8f0;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.125rem;
        }

        .stat-label {
            font-size: 0.7rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Filters */
        .filters-bar {
            padding: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fafafa;
        }

        .search-bar {
            margin-bottom: 1rem;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 0.875rem;
            outline: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .filter-select {
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 0.8rem;
            cursor: pointer;
            outline: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .filter-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
            transition: all 0.2s ease;
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .email-item:hover {
            background-color: #f8fafc;
            transform: translateX(2px);
        }

        .email-item.unread {
            background-color: #fefefe;
            border-left: 2px solid #3b82f6;
        }

        .email-item.unread .email-from {
            font-weight: 700;
            color: #1f2937;
        }

        .email-item.unread .email-subject {
            font-weight: 600;
            color: #1f2937;
        }

        .email-item.unread::before {
            content: '';
            position: absolute;
            left: 6px;
            top: 1.2rem;
            width: 4px;
            height: 4px;
            background-color: #3b82f6;
            border-radius: 50%;
            z-index: 10;
        }

        .email-item.read {
            background-color: #ffffff;
        }

        .email-item.read .email-from {
            font-weight: 500;
            color: #6b7280;
        }

        .email-item.read .email-subject {
            font-weight: 400;
            color: #6b7280;
        }

        .email-item.active {
            background-color: #e0f2fe;
            border-left: 3px solid #0891b2;
        }

        .email-item.active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: #0891b2;
        }

        /* Avatar styles */
        .email-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            flex-shrink: 0;
            margin-left: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .email-content {
            flex: 1;
            min-width: 0;
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

        /* Dropdown styles */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            display: none;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item:hover {
            background-color: #f3f4f6;
        }

        /* Button hover effects */
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-primary:hover {
            background-color: #1d4ed8 !important;
        }

        .btn-outline:hover {
            background-color: #f3f4f6 !important;
        }

        /* Star icon styling */
        .star-icon.starred {
            color: #fbbf24 !important;
        }

        /* Quill.js Editor Customization */
        .ql-toolbar {
            border-top: 1px solid #d1d5db;
            border-left: 1px solid #d1d5db;
            border-right: 1px solid #d1d5db;
            border-bottom: none;
            border-radius: 6px 6px 0 0;
            background: #f9fafb;
            padding: 0.5rem;
        }
        
        .ql-container {
            border: 1px solid #d1d5db;
            border-radius: 0 0 6px 6px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .ql-editor {
            padding: 1rem;
            min-height: 150px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .ql-editor.ql-blank::before {
            color: #9ca3af;
            font-style: normal;
        }
        
        .ql-toolbar .ql-formats {
            margin-right: 0.75rem;
        }
        
        .ql-toolbar button {
            border-radius: 3px;
            padding: 0.25rem;
            margin: 0.125rem;
        }
        
        .ql-toolbar button:hover {
            background: #e5e7eb;
        }
        
        .ql-toolbar button.ql-active {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .ql-picker-label {
            border-radius: 3px;
            padding: 0.25rem 0.5rem;
        }
        
        .ql-picker-label:hover {
            background: #e5e7eb;
        }
        
        .ql-picker-options {
            border-radius: 6px;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .ql-picker-item:hover {
            background: #f3f4f6;
        }
        
        .reply-editor {
            transition: all 0.15s ease;
        }
        
        .reply-editor:focus-within {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
                    <!-- Auto-refresh indicator -->
                    <div style="margin-bottom: 0.75rem; text-align: center;">
                        <span style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">
                            <i class="fas fa-sync-alt" style="margin-right: 0.25rem; animation: spin 2s linear infinite;"></i>
                            Auto-refreshing emails...
                        </span>
                    </div>
                    
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
                        <div class="email-item {{ $email->status == 'unread' ? 'unread' : 'read' }}" 
                             data-email-id="{{ $email->id }}"
                             onclick="loadEmail({{ $email->id }})">
                            <!-- Avatar -->
                            <div class="email-avatar" style="background: {{ $email->getAvatarColor() }};">
                                {{ $email->getAvatarInitials() }}
                            </div>
                            
                            <!-- Email Content -->
                            <div class="email-content">
                                <div class="email-item-header">
                                    <div class="email-from">{{ $email->from_name ?: $email->from_email }}</div>
                                    <div class="email-time">{{ $email->received_at ? $email->received_at->diffForHumans() : 'Unknown' }}</div>
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

    // Load email function - make it globally accessible
    window.loadEmail = function(emailId) {
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

    // Global object to store Quill instances
    window.quillInstances = {};

    // Reply functions - now implemented in main page with Quill.js integration
    function showReplyForm(emailId) {
        console.log('Showing reply form for email:', emailId);
        const form = document.getElementById(`reply-form-${emailId}`);
        if (form) {
            form.style.display = 'block';
            
            // Initialize Quill editor if not already initialized
            if (!window.quillInstances[emailId]) {
                setTimeout(() => {
                    const editorElement = document.getElementById(`reply-editor-${emailId}`);
                    if (editorElement) {
                        window.quillInstances[emailId] = new Quill(`#reply-editor-${emailId}`, {
                            theme: 'snow',
                            placeholder: 'Type your reply...',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline', 'strike'],
                                    ['blockquote', 'code-block'],
                                    [{ 'header': 1 }, { 'header': 2 }],
                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                    [{ 'script': 'sub'}, { 'script': 'super' }],
                                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                                    [{ 'direction': 'rtl' }],
                                    [{ 'size': ['small', false, 'large', 'huge'] }],
                                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                                    [{ 'color': [] }, { 'background': [] }],
                                    [{ 'font': [] }],
                                    [{ 'align': [] }],
                                    ['clean'],
                                    ['link']
                                ]
                            }
                        });
                        
                        // Focus the editor
                        window.quillInstances[emailId].focus();
                    }
                }, 100);
            } else {
                // Focus existing editor
                window.quillInstances[emailId].focus();
            }
        } else {
            console.error('Reply form not found for email:', emailId);
        }
    }

    function hideReplyForm(emailId) {
        console.log('Hiding reply form for email:', emailId);
        const form = document.getElementById(`reply-form-${emailId}`);
        if (form) {
            form.style.display = 'none';
            
            // Clear Quill editor content
            if (window.quillInstances[emailId]) {
                window.quillInstances[emailId].setContents([]);
            }
            
            // Clear hidden input
            const hiddenInput = document.getElementById(`reply-body-${emailId}`);
            if (hiddenInput) {
                hiddenInput.value = '';
            }
        }
    }

    function sendReply(event, emailId) {
        event.preventDefault();
        
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Get content from Quill editor
        const quillEditor = window.quillInstances[emailId];
        if (!quillEditor) {
            showNotification('Editor not initialized. Please try again.', 'error');
            return;
        }
        
        const htmlContent = quillEditor.root.innerHTML;
        const textContent = quillEditor.getText().trim();
        
        // Check if there's any content
        if (!textContent) {
            showNotification('Please enter a reply message.', 'error');
            return;
        }
        
        // Update hidden input with HTML content
        const hiddenInput = document.getElementById(`reply-body-${emailId}`);
        if (hiddenInput) {
            hiddenInput.value = htmlContent;
        }
        
        // Get selected account ID
        const fromAccountSelect = document.getElementById(`reply-from-${emailId}`);
        const fromAccountId = fromAccountSelect ? fromAccountSelect.value : null;
        
        // Disable submit button and show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        fetch(`/inbox/email/${emailId}/reply`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                reply_body: htmlContent,
                from_account_id: fromAccountId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Reply sent successfully!');
                hideReplyForm(emailId);
                // Reload the email detail to show the reply
                loadEmail(emailId);
            } else {
                showNotification('Failed to send reply. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error sending reply:', error);
            showNotification('Failed to send reply. Please try again.', 'error');
        })
        .finally(() => {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Send Reply';
        });
    }

    // Add notification function for the main page
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

    // Auto-refresh function - silently fetch new emails every 30 seconds
    function autoRefreshEmails() {
        fetch('/inbox/fetch-emails', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.results) {
                let totalNew = 0;
                data.results.forEach(result => {
                    totalNew += result.count || 0;
                });
                
                // If there are new emails, reload the page
                if (totalNew > 0) {
                    // Show notification (optional)
                    console.log(`${totalNew} new email(s) received`);
                    
                    // Reload the page to show new emails
                    window.location.reload();
                }
            }
        })
        .catch(error => {
            console.error('Auto-refresh error:', error);
        });
    }
    
    // Set up auto-refresh every 30 seconds
    setInterval(autoRefreshEmails, 30000);
    
    // Auto-submit search form on enter
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('filterForm').submit();
            }
        });
    }

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
