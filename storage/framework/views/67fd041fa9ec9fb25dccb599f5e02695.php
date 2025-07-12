

<?php $__env->startSection('title', 'Inbox - CollaborInbox'); ?>

<?php $__env->startSection('body-class', 'inbox-page'); ?>

<?php $__env->startSection('page-styles'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* Inbox specific styles */
        .inbox-layout {
            display: flex;
            height: calc(100vh - 60px);
        }

        /* Email list sidebar */
        .email-list-sidebar {
            width: 400px;
            background-color: hsl(var(--card));
            border-right: 1px solid hsl(var(--border));
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Filters bar */
        .filters-bar {
            padding: 1rem;
            border-bottom: 1px solid hsl(var(--border));
            background-color: hsl(var(--background));
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .filter-select {
            width: 100%;
            padding: 0.375rem 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            background-color: hsl(var(--background));
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .filter-select:focus {
            border-color: hsl(var(--primary));
        }

        .search-bar {
            margin-bottom: 0.75rem;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            background-color: hsl(var(--background));
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
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
            position: relative;
        }

        .email-item:hover {
            background-color: hsl(var(--accent));
        }

        .email-item.active {
            background-color: hsl(var(--secondary));
        }

        .email-item.unread {
            font-weight: 600;
        }

        .email-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .email-from {
            font-size: 0.875rem;
            color: hsl(var(--foreground));
        }

        .email-time {
            font-size: 0.75rem;
            color: hsl(var(--muted-foreground));
        }

        .email-subject {
            font-size: 0.875rem;
            color: hsl(var(--foreground));
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .email-preview {
            font-size: 0.813rem;
            color: hsl(var(--muted-foreground));
            font-weight: 400;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .email-meta {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .email-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1;
        }

        .badge-disposition {
            background-color: var(--badge-color, hsl(var(--muted)));
            color: white;
        }

        /* Email detail view */
        .email-detail {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: hsl(var(--background));
        }

        .email-detail-header {
            padding: 1.5rem;
            border-bottom: 1px solid hsl(var(--border));
            background-color: hsl(var(--card));
        }

        .email-actions {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: 1px solid transparent;
            border-radius: calc(var(--radius) - 2px);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
        }

        .btn-primary:hover {
            background-color: hsl(var(--primary) / 0.9);
        }

        .btn-secondary {
            background-color: hsl(var(--secondary));
            color: hsl(var(--secondary-foreground));
        }

        .btn-secondary:hover {
            background-color: hsl(var(--secondary) / 0.8);
        }

        .btn-outline {
            border-color: hsl(var(--border));
            background-color: transparent;
            color: hsl(var(--foreground));
        }

        .btn-outline:hover {
            background-color: hsl(var(--accent));
            color: hsl(var(--accent-foreground));
        }

        .btn-icon {
            padding: 0.5rem;
        }

        .email-detail-content {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .email-detail-subject {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .email-detail-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: hsl(var(--muted-foreground));
        }

        .email-detail-body {
            line-height: 1.6;
            color: hsl(var(--foreground));
        }

        /* Attachments */
        .attachments-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid hsl(var(--border));
        }

        .attachments-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .attachments-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            background-color: hsl(var(--card));
            text-decoration: none;
            color: hsl(var(--foreground));
            transition: all 0.2s ease;
        }

        .attachment-item:hover {
            background-color: hsl(var(--accent));
            border-color: hsl(var(--primary));
        }

        .attachment-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: hsl(var(--muted));
            border-radius: calc(var(--radius) - 4px);
            flex-shrink: 0;
        }

        .attachment-info {
            flex: 1;
            overflow: hidden;
        }

        .attachment-name {
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attachment-size {
            font-size: 0.75rem;
            color: hsl(var(--muted-foreground));
        }

        /* Reply section */
        .reply-section {
            padding: 1.5rem;
            border-top: 1px solid hsl(var(--border));
            background-color: hsl(var(--card));
        }

        .reply-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .reply-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            background-color: hsl(var(--background));
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s ease;
            font-family: inherit;
        }

        .reply-input:focus {
            border-color: hsl(var(--primary));
        }

        .reply-textarea {
            min-height: 150px;
            resize: vertical;
        }

        /* Star icon */
        .star-icon {
            cursor: pointer;
            color: hsl(var(--muted-foreground));
            transition: color 0.2s ease;
        }

        .star-icon.starred {
            color: #f59e0b;
        }

        .star-icon:hover {
            color: #f59e0b;
        }

        /* Empty state */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 2rem;
            text-align: center;
        }

        .empty-state-icon {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
            color: hsl(var(--muted-foreground));
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: hsl(var(--foreground));
        }

        .empty-state-description {
            color: hsl(var(--muted-foreground));
        }

        /* Stats cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.5rem;
            padding: 1rem;
            background-color: hsl(var(--background));
            border-bottom: 1px solid hsl(var(--border));
        }

        .stat-card {
            padding: 0.75rem;
            background-color: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            text-align: center;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: hsl(var(--foreground));
        }

        .stat-label {
            font-size: 0.75rem;
            color: hsl(var(--muted-foreground));
        }

        /* Bulk actions bar */
        .bulk-actions-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background-color: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
        }

        .bulk-actions-bar .btn {
            background-color: hsl(var(--primary-foreground));
            color: hsl(var(--primary));
        }

        /* Checkbox */
        .checkbox {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        /* Dropdown menu */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            min-width: 200px;
            background-color: hsl(var(--popover));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            display: none;
            z-index: 50;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            color: hsl(var(--foreground));
            text-decoration: none;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: hsl(var(--accent));
        }

        .dropdown-divider {
            height: 1px;
            margin: 0.25rem 0;
            background-color: hsl(var(--border));
        }

        /* User menu (same as dashboard) */
        .header-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: calc(var(--radius) - 2px);
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .user-menu:hover {
            background-color: hsl(var(--accent));
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .email-list-sidebar {
                width: 350px;
            }
        }

        @media (max-width: 768px) {
            .inbox-layout {
                position: relative;
            }

            .email-list-sidebar {
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 100%;
                z-index: 10;
            }

            .email-detail {
                margin-left: 0;
            }

            .email-detail.show {
                z-index: 20;
                position: absolute;
                left: 0;
                right: 0;
                top: 0;
                bottom: 0;
            }

            .back-button {
                display: inline-flex !important;
            }

            .sidebar {
                position: fixed;
                z-index: 50;
                transform: translateX(-100%);
                transition: transform 0.2s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }

        .back-button {
            display: none;
        }

        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
        <!-- Inbox Layout -->
        <div class="inbox-layout">
            <!-- Email List Sidebar -->
            <div class="email-list-sidebar">
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo e($stats['total']); ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo e($stats['unread']); ?></div>
                        <div class="stat-label">Unread</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo e($stats['starred']); ?></div>
                        <div class="stat-label">Starred</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo e($stats['unassigned']); ?></div>
                        <div class="stat-label">Unassigned</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-bar">
                    <form method="GET" action="<?php echo e(route('inbox.index')); ?>" id="filterForm">
                        <div class="search-bar">
                            <input type="text" name="search" class="search-input" placeholder="Search emails..." value="<?php echo e(request('search')); ?>">
                        </div>
                        
                        <div class="filters-grid">
                            <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all">All Status</option>
                                <option value="unread" <?php echo e(request('status') == 'unread' ? 'selected' : ''); ?>>Unread</option>
                                <option value="read" <?php echo e(request('status') == 'read' ? 'selected' : ''); ?>>Read</option>
                                <option value="replied" <?php echo e(request('status') == 'replied' ? 'selected' : ''); ?>>Replied</option>
                                <option value="archived" <?php echo e(request('status') == 'archived' ? 'selected' : ''); ?>>Archived</option>
                            </select>
                            
                            <select name="account" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all">All Accounts</option>
                                <?php $__currentLoopData = $emailAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($account->id); ?>" <?php echo e(request('account') == $account->id ? 'selected' : ''); ?>>
                                        <?php echo e($account->email_address); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            
                            <select name="disposition" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all">All Dispositions</option>
                                <?php $__currentLoopData = $dispositions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disposition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($disposition->id); ?>" <?php echo e(request('disposition') == $disposition->id ? 'selected' : ''); ?>>
                                        <?php echo e($disposition->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            
                            <select name="assigned" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="all">All Users</option>
                                <option value="unassigned" <?php echo e(request('assigned') == 'unassigned' ? 'selected' : ''); ?>>Unassigned</option>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>" <?php echo e(request('assigned') == $user->id ? 'selected' : ''); ?>>
                                        <?php echo e($user->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Email List -->
                <div class="email-list" id="emailList">
                    <?php $__empty_1 = true; $__currentLoopData = $emails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $email): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="email-item <?php echo e($email->status == 'unread' ? 'unread' : ''); ?>" 
                             data-email-id="<?php echo e($email->id); ?>"
                             onclick="loadEmail(<?php echo e($email->id); ?>)">
                            <div class="email-item-header">
                                <div class="email-from"><?php echo e($email->from_name ?: $email->from_email); ?></div>
                                <div class="email-time"><?php echo e($email->received_at->diffForHumans()); ?></div>
                            </div>
                            <div class="email-subject"><?php echo e($email->subject); ?></div>
                            <div class="email-preview"><?php echo e($email->getPreviewText()); ?></div>
                            <div class="email-meta">
                                <?php if($email->is_starred): ?>
                                    <i class="fas fa-star star-icon starred"></i>
                                <?php endif; ?>
                                <?php if($email->has_attachments): ?>
                                    <i class="fas fa-paperclip" style="color: hsl(var(--muted-foreground)); font-size: 0.75rem;"></i>
                                <?php endif; ?>
                                <?php if($email->disposition): ?>
                                    <span class="email-badge badge-disposition" style="--badge-color: <?php echo e($email->disposition->color); ?>;">
                                        <?php echo e($email->disposition->name); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if($email->assignedUser): ?>
                                    <span class="email-badge" style="background-color: hsl(var(--muted)); color: hsl(var(--muted-foreground));">
                                        <?php echo e($email->assignedUser->name); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty-state">
                            <svg class="empty-state-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <div class="empty-state-title">No emails found</div>
                            <div class="empty-state-description">Try adjusting your filters or search criteria</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if($emails->hasPages()): ?>
                    <div style="padding: 1rem;">
                        <?php echo e($emails->links()); ?>

                    </div>
                <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-scripts'); ?>
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
                <div style="margin-top: 1rem;">Loading email...</div>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CollaborInbox\resources\views/inbox/index.blade.php ENDPATH**/ ?>