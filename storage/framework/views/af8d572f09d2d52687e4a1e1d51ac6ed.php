<nav class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo e(route('dashboard')); ?>" class="sidebar-logo">
            <div class="sidebar-logo-icon">CI</div>
            <span class="sidebar-logo-text">CollaborInbox</span>
        </a>
    </div>

    <div class="sidebar-nav">
        <a href="<?php echo e(route('dashboard')); ?>" class="nav-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
            <span class="nav-item-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 2.5L2 7.5V17.5C2 17.7652 2.10536 18.0196 2.29289 18.2071C2.48043 18.3946 2.73478 18.5 3 18.5H7V12.5H11V18.5H17C17.2652 18.5 17.5196 18.3946 17.7071 18.2071C17.8946 18.0196 18 17.7652 18 17.5V7.5L10 2.5Z" fill="currentColor"/>
                </svg>
            </span>
            <span class="nav-item-text">Dashboard</span>
        </a>

        <a href="<?php echo e(route('inbox.index')); ?>" class="nav-item <?php echo e(request()->routeIs('inbox.*') && !request()->routeIs('inbox.settings.dispositions*') ? 'active' : ''); ?>">
            <span class="nav-item-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 5C3 4.73478 3.10536 4.48043 3.29289 4.29289C3.48043 4.10536 3.73478 4 4 4H16C16.2652 4 16.5196 4.10536 16.7071 4.29289C16.8946 4.48043 17 4.73478 17 5V15C17 15.2652 16.8946 15.5196 16.7071 15.7071C16.5196 15.8946 16.2652 16 16 16H4C3.73478 16 3.48043 15.8946 3.29289 15.7071C3.10536 15.5196 3 15.2652 3 15V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 10.5L3 5V15H17V5L10 10.5Z" fill="currentColor" fill-opacity="0.2"/>
                    <path d="M3 5L10 10.5L17 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="nav-item-text">Inbox</span>
        </a>

        <a href="<?php echo e(route('dispositions.dashboard')); ?>" class="nav-item <?php echo e(request()->routeIs('dispositions.*') || request()->routeIs('inbox.settings.dispositions*') ? 'active' : ''); ?>">
            <span class="nav-item-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="3" width="14" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M7 10L9 12L13 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="nav-item-text">Dispositions</span>
        </a>

        <?php if(Auth::user() && Auth::user()->isAdmin()): ?>
        <a href="<?php echo e(route('users.index')); ?>" class="nav-item <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>">
            <span class="nav-item-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 18V16C17 14.9391 16.5786 13.9217 15.8284 13.1716C15.0783 12.4214 14.0609 12 13 12H7C5.93913 12 4.92172 12.4214 4.17157 13.1716C3.42143 13.9217 3 14.9391 3 16V18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="10" cy="6" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="nav-item-text">User Management</span>
        </a>
        <?php endif; ?>

        <?php if(request()->routeIs('inbox.*')): ?>
        <div style="margin-top: 2rem; padding: 0 1rem;">
            <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground)); font-weight: 600; margin-bottom: 0.5rem;">
                EMAIL SETTINGS
            </div>
            <a href="<?php echo e(route('inbox.settings.accounts')); ?>" class="nav-item <?php echo e(request()->routeIs('inbox.settings.accounts*') ? 'active' : ''); ?>">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="10" cy="10" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 10h4M10 8v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="nav-item-text">Email Accounts</span>
            </a>
            
            <a href="<?php echo e(route('inbox.email-setup')); ?>" class="nav-item <?php echo e(request()->routeIs('inbox.email-setup') ? 'active' : ''); ?>">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.567 10.898a.5.5 0 1 0 .866-.5l1.134-1.96a.5.5 0 0 0-.684-.684l-1.96 1.134a.5.5 0 0 0 .644 1.01ZM7.6 5.6a5 5 0 1 1 4.8 8.4L7 19l1-6-6 1 5-5.4a5 5 0 0 1 .6-3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="nav-item-text">Email Setup</span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <div class="dropdown">
            <div class="user-menu" onclick="toggleDropdown('userDropdown')">
                <div class="user-avatar">
                    <?php echo e(strtoupper(substr(Auth::user()->name ?? 'U', 0, 1))); ?>

                </div>
                <span class="nav-item-text"><?php echo e(Auth::user()->name ?? 'User'); ?></span>
            </div>
            <div class="dropdown-menu" id="userDropdown">
                <a href="#" class="dropdown-item">Profile</a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; border: none; background: none;">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH D:\CollaborInbox\resources\views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>