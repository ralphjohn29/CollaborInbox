

<?php $__env->startSection('title', 'Email Setup - CollaborInbox'); ?>

<?php $__env->startSection('body-class', 'inbox-page'); ?>

<?php $__env->startSection('styles'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Modern color palette inspired by shadcn/ui */
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 222.2 84% 4.9%;
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

        * {
            box-sizing: border-box;
        }

        /* Override body styles for inbox page */
        body.inbox-page {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #fafafa !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif !important;
        }

        .inbox-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar styles (same as dashboard) */
        .sidebar {
            width: 240px;
            background-color: hsl(var(--card));
            border-right: 1px solid hsl(var(--border));
            display: flex;
            flex-direction: column;
            transition: width 0.2s ease;
        }

        .sidebar.collapsed {
            width: 60px;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid hsl(var(--border));
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: hsl(var(--foreground));
        }

        .sidebar-logo-icon {
            width: 32px;
            height: 32px;
            background-color: hsl(var(--primary));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: hsl(var(--primary-foreground));
            font-weight: 600;
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-weight: 600;
            font-size: 1.125rem;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s ease;
        }

        .sidebar.collapsed .sidebar-logo-text {
            opacity: 0;
            width: 0;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.625rem 1rem;
            margin: 0 0.5rem 0.25rem;
            border-radius: calc(var(--radius) - 2px);
            text-decoration: none;
            color: hsl(var(--muted-foreground));
            transition: all 0.2s ease;
            gap: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .nav-item:hover {
            background-color: hsl(var(--accent));
            color: hsl(var(--accent-foreground));
        }

        .nav-item.active {
            background-color: hsl(var(--secondary));
            color: hsl(var(--foreground));
        }

        .nav-item-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .nav-item-text {
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s ease;
        }

        .sidebar.collapsed .nav-item-text {
            opacity: 0;
            width: 0;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid hsl(var(--border));
        }

        /* Main content area */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Header */
        .header {
            height: 60px;
            background-color: hsl(var(--card));
            border-bottom: 1px solid hsl(var(--border));
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        .header-toggle {
            padding: 0.5rem;
            border: none;
            background: none;
            cursor: pointer;
            color: hsl(var(--muted-foreground));
            border-radius: calc(var(--radius) - 2px);
            transition: all 0.2s ease;
        }

        .header-toggle:hover {
            background-color: hsl(var(--accent));
            color: hsl(var(--accent-foreground));
        }

        /* Email setup specific styles */
        .content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background-color: hsl(var(--background));
        }

        .setup-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .setup-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .setup-title {
            font-size: 2rem;
            font-weight: 700;
            color: hsl(var(--foreground));
            margin-bottom: 0.5rem;
        }

        .setup-subtitle {
            font-size: 1.125rem;
            color: hsl(var(--muted-foreground));
        }

        .setup-card {
            background-color: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: hsl(var(--foreground));
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            background-color: hsl(var(--background));
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .form-input:focus {
            border-color: hsl(var(--primary));
        }

        .form-help {
            font-size: 0.75rem;
            color: hsl(var(--muted-foreground));
            margin-top: 0.25rem;
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

        .alert {
            padding: 1rem;
            border-radius: calc(var(--radius) - 2px);
            margin-bottom: 1rem;
        }

        .alert-info {
            background-color: hsl(217.2 91.2% 59.8% / 0.1);
            border: 1px solid hsl(217.2 91.2% 59.8% / 0.3);
            color: hsl(217.2 91.2% 59.8%);
        }

        .alert-success {
            background-color: hsl(142.1 76.2% 36.3% / 0.1);
            border: 1px solid hsl(142.1 76.2% 36.3% / 0.3);
            color: hsl(142.1 76.2% 36.3%);
        }

        .alert-error {
            background-color: hsl(var(--destructive) / 0.1);
            border: 1px solid hsl(var(--destructive) / 0.3);
            color: hsl(var(--destructive));
        }

        .test-result {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: calc(var(--radius) - 2px);
            font-size: 0.875rem;
        }

        .test-success {
            background-color: hsl(142.1 76.2% 36.3% / 0.1);
            border: 1px solid hsl(142.1 76.2% 36.3% / 0.3);
            color: hsl(142.1 76.2% 36.3%);
        }

        .test-error {
            background-color: hsl(var(--destructive) / 0.1);
            border: 1px solid hsl(var(--destructive) / 0.3);
            color: hsl(var(--destructive));
        }

        .section-divider {
            height: 1px;
            background-color: hsl(var(--border));
            margin: 2rem 0;
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

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: hsl(var(--primary));
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="inbox-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo e(url('/dashboard')); ?>" class="sidebar-logo">
                <div class="sidebar-logo-icon">CI</div>
                <span class="sidebar-logo-text">CollaborInbox</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?php echo e(url('/dashboard')); ?>" class="nav-item">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </span>
                <span class="nav-item-text">Dashboard</span>
            </a>
            
            <a href="<?php echo e(url('/inbox')); ?>" class="nav-item">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <span class="nav-item-text">Inbox</span>
            </a>
            
            <a href="<?php echo e(url('/dispositions')); ?>" class="nav-item">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                </span>
                <span class="nav-item-text">Dispositions</span>
            </a>
            <?php if(auth()->check() && auth()->user()->is_admin): ?>
            <a href="<?php echo e(url('/users')); ?>" class="nav-item">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                </span>
                <span class="nav-item-text">User Management</span>
            </a>
            <?php endif; ?>

            <div style="margin-top: 2rem; padding: 0 1rem;">
                <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground)); font-weight: 600; margin-bottom: 0.5rem;">
                    EMAIL SETTINGS
                </div>
                <a href="<?php echo e(route('inbox.settings.accounts')); ?>" class="nav-item">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12h8"/>
                            <path d="M12 8v8"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Email Accounts</span>
                </a>
                
                <a href="<?php echo e(url('/inbox/email-setup')); ?>" class="nav-item active">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Email Setup</span>
                </a>
                
                <a href="<?php echo e(route('inbox.settings.dispositions')); ?>" class="nav-item">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Disposition Settings</span>
                </a>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <div class="nav-item">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </span>
                <span class="nav-item-text">Help & Support</span>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <button class="header-toggle" id="sidebarToggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            
            <h1 style="font-size: 1.25rem; font-weight: 600; margin: 0;">Email Setup</h1>
            
            <div class="header-actions">
                <div class="dropdown">
                    <div class="user-menu" id="userMenuToggle">
                        <div class="user-avatar">
                            <?php if(auth()->guard()->check()): ?>
                                <?php echo e(substr(Auth::user()->name ?? 'U', 0, 1)); ?>

                            <?php else: ?>
                                U
                            <?php endif; ?>
                        </div>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    
                    <div class="dropdown-menu" id="userMenu">
                        <a href="#" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Profile
                        </a>
                        <a href="#" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M12 1v6m0 6v6m11-11h-6m-6 0H1"/>
                            </svg>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo e(url('/logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Logout
                        </a>
                        <form id="logout-form" action="<?php echo e(url('/logout')); ?>" method="POST" style="display: none;">
                            <?php echo csrf_field(); ?>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content">
            <div class="setup-container">
                <div class="setup-header">
                    <h1 class="setup-title">Connect Your Email Account</h1>
                    <p class="setup-subtitle">Set up email integration to receive and forward emails through CollaborInbox</p>
                </div>

                <?php if(session('success')): ?>
                    <div class="alert alert-success">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-error">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <div class="setup-card">
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Mailtrap Integration (Testing)</h2>
                    
                    <div class="alert alert-info">
                        <strong>Test Environment:</strong> We're using Mailtrap for testing email functionality. In production, you'll connect your real email accounts.
                    </div>

                    <form id="emailSetupForm" method="POST" action="<?php echo e(route('inbox.settings.accounts.store')); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label class="form-label" for="email_address">Email Address</label>
                            <input type="email" id="email_address" name="email_address" class="form-input" 
                                   placeholder="test@collaborinbox.com" required>
                            <p class="form-help">This is the email address that will receive emails in your inbox</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="from_name">Display Name</label>
                            <input type="text" id="from_name" name="from_name" class="form-input" 
                                   placeholder="CollaborInbox Test" value="CollaborInbox Test">
                            <p class="form-help">The name that will appear when sending emails</p>
                        </div>

                        <div class="section-divider"></div>

                        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">SMTP Configuration (Outgoing Mail)</h3>

                        <div class="form-group">
                            <label class="form-label" for="smtp_host">SMTP Host</label>
                            <input type="text" id="smtp_host" name="outgoing_server_host" class="form-input" 
                                   value="sandbox.smtp.mailtrap.io" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="smtp_port">SMTP Port</label>
                            <input type="number" id="smtp_port" name="outgoing_server_port" class="form-input" 
                                   value="2525" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="smtp_username">SMTP Username</label>
                            <input type="text" id="smtp_username" name="outgoing_server_username" class="form-input" 
                                   value="acb4f069175c45" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="smtp_password">SMTP Password</label>
                            <input type="password" id="smtp_password" name="outgoing_server_password" class="form-input" 
                                   value="7898c8ac25792b" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="smtp_encryption">Encryption</label>
                            <select id="smtp_encryption" name="outgoing_server_encryption" class="form-input">
                                <option value="tls" selected>TLS (Recommended)</option>
                                <option value="ssl">SSL</option>
                                <option value="">None</option>
                            </select>
                        </div>

                        <div class="section-divider"></div>

                        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">IMAP Configuration (Incoming Mail)</h3>

                        <div class="alert alert-info">
                            <strong>Note:</strong> Mailtrap doesn't provide IMAP access in the free tier. We'll use the API to fetch emails instead.
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="mailtrap_api_key">Mailtrap API Key</label>
                            <input type="text" id="mailtrap_api_key" name="mailtrap_api_key" class="form-input" 
                                   value="d2a9db1ad8cd635fad770c540e6c3c9c" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="mailtrap_inbox_id">Mailtrap Inbox ID</label>
                            <input type="text" id="mailtrap_inbox_id" name="mailtrap_inbox_id" class="form-input" 
                                   placeholder="Enter your Mailtrap inbox ID" required>
                            <p class="form-help">You can find this in your Mailtrap dashboard</p>
                        </div>

                        <!-- Hidden fields for IMAP (required by the system but not used with Mailtrap) -->
                        <input type="hidden" name="incoming_server_host" value="sandbox.api.mailtrap.io">
                        <input type="hidden" name="incoming_server_port" value="443">
                        <input type="hidden" name="incoming_server_username" value="api">
                        <input type="hidden" name="incoming_server_password" value="d2a9db1ad8cd635fad770c540e6c3c9c">
                        <input type="hidden" name="incoming_server_encryption" value="ssl">

                        <div class="section-divider"></div>

                        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                            <button type="button" id="testConnection" class="btn btn-secondary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                                Test Connection
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                </svg>
                                Save Configuration
                            </button>
                        </div>

                        <div id="testResult" style="display: none;"></div>
                    </form>
                </div>

                <div class="setup-card">
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Quick Start Guide</h2>
                    
                    <ol style="list-style: decimal; padding-left: 1.5rem; line-height: 1.8;">
                        <li>Enter the email address you want to use for this inbox</li>
                        <li>The SMTP settings are pre-configured for Mailtrap testing</li>
                        <li>Get your Mailtrap Inbox ID from your Mailtrap dashboard</li>
                        <li>Test the connection to ensure everything is working</li>
                        <li>Save the configuration to start receiving emails</li>
                    </ol>

                    <div class="alert alert-info" style="margin-top: 1.5rem;">
                        <strong>Testing Tips:</strong>
                        <ul style="list-style: disc; padding-left: 1.5rem; margin-top: 0.5rem;">
                            <li>Send test emails to your Mailtrap inbox to see them appear in CollaborInbox</li>
                            <li>Use the Mailtrap dashboard to monitor email delivery</li>
                            <li>Check the API logs in Mailtrap for troubleshooting</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });

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

    // Test connection functionality
    document.getElementById('testConnection').addEventListener('click', function() {
        const button = this;
        const originalContent = button.innerHTML;
        const testResult = document.getElementById('testResult');
        
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<span class="spinner"></span> Testing...';
        testResult.style.display = 'none';
        
        // Get form data
        const formData = new FormData(document.getElementById('emailSetupForm'));
        
        // Simulate API test (in real implementation, this would make an actual API call)
        setTimeout(() => {
            // Simulate successful connection
            testResult.innerHTML = `
                <div class="test-success">
                    <strong>✓ Connection Successful!</strong><br>
                    SMTP connection to Mailtrap established successfully.
                </div>
            `;
            testResult.style.display = 'block';
            
            // Reset button
            button.disabled = false;
            button.innerHTML = originalContent;
        }, 2000);
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CollaborInbox\resources\views/inbox/email-setup.blade.php ENDPATH**/ ?>