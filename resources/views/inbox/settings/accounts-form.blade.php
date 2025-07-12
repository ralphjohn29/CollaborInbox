@extends('layouts.app')

@section('title', isset($emailAccount) ? 'Edit Email Account' : 'Add Email Account')

@section('body-class', 'inbox-settings-page')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reuse styles from accounts page */
        :root {
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

        body.inbox-settings-page {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #fafafa !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif !important;
        }

        .settings-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar styles */
        .sidebar {
            width: 240px;
            background-color: hsl(var(--card));
            border-right: 1px solid hsl(var(--border));
            display: flex;
            flex-direction: column;
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

        /* Main content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .header {
            height: 60px;
            background-color: hsl(var(--card));
            border-bottom: 1px solid hsl(var(--border));
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        .content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background-color: hsl(var(--background));
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: hsl(var(--foreground));
            margin: 0 0 0.5rem 0;
        }

        .page-description {
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
        }

        /* Form styles */
        .form-container {
            background-color: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            padding: 2rem;
            max-width: 800px;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: hsl(var(--foreground));
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: hsl(var(--foreground));
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
            font-family: inherit;
        }

        .form-input:focus {
            border-color: hsl(var(--primary));
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .form-help {
            font-size: 0.813rem;
            color: hsl(var(--muted-foreground));
            margin-top: 0.25rem;
        }

        .form-error {
            font-size: 0.813rem;
            color: hsl(var(--destructive));
            margin-top: 0.25rem;
        }

        /* Button styles */
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

        .btn-outline {
            border-color: hsl(var(--border));
            background-color: transparent;
            color: hsl(var(--foreground));
        }

        .btn-outline:hover {
            background-color: hsl(var(--accent));
            color: hsl(var(--accent-foreground));
        }

        .form-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        /* Checkbox */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        /* Test connection button */
        .test-connection {
            margin-top: 0.5rem;
        }

        .test-result {
            margin-top: 0.5rem;
            padding: 0.75rem;
            border-radius: calc(var(--radius) - 2px);
            font-size: 0.813rem;
        }

        .test-result.success {
            background-color: hsl(142.1 76.2% 36.3% / 0.1);
            color: hsl(142.1 76.2% 36.3%);
        }

        .test-result.error {
            background-color: hsl(var(--destructive) / 0.1);
            color: hsl(var(--destructive));
        }
    </style>
@endsection

@section('content')
<div class="settings-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="{{ url('/dashboard') }}" class="sidebar-logo">
                <div class="sidebar-logo-icon">CI</div>
                <span class="sidebar-logo-text">CollaborInbox</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ url('/dashboard') }}" class="nav-item">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </span>
                <span class="nav-item-text">Dashboard</span>
            </a>
            
            <a href="{{ url('/inbox') }}" class="nav-item">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <span class="nav-item-text">Inbox</span>
            </a>

            <div style="margin-top: 2rem; padding: 0 1rem;">
                <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground)); font-weight: 600; margin-bottom: 0.5rem;">
                    SETTINGS
                </div>
                <a href="{{ route('inbox.settings.accounts') }}" class="nav-item active">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12h8"/>
                            <path d="M12 8v8"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Email Accounts</span>
                </a>
                
                <a href="{{ route('inbox.settings.dispositions') }}" class="nav-item">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Dispositions</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <h1 style="font-size: 1.25rem; font-weight: 600; margin: 0;">
                {{ isset($emailAccount) ? 'Edit Email Account' : 'Add Email Account' }}
            </h1>
        </header>

        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <h1 class="page-title">{{ isset($emailAccount) ? 'Edit Email Account' : 'Add Email Account' }}</h1>
                <p class="page-description">
                    {{ isset($emailAccount) ? 'Update the email account configuration' : 'Configure a new email account to receive emails' }}
                </p>
            </div>

            @if($errors->any())
                <div style="padding: 1rem; background-color: hsl(var(--destructive) / 0.1); color: hsl(var(--destructive)); border-radius: var(--radius); margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ isset($emailAccount) ? route('inbox.settings.accounts.update', $emailAccount->id) : route('inbox.settings.accounts.store') }}">
                @csrf
                @if(isset($emailAccount))
                    @method('PUT')
                @endif

                <div class="form-container">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h2 class="section-title">Basic Information</h2>
                        
                        <div class="form-grid">
                            @if(!isset($emailAccount))
                                <div class="form-group">
                                    <label class="form-label" for="email_prefix">Email Prefix <span style="color: hsl(var(--destructive));">*</span></label>
                                    <input type="text" id="email_prefix" name="email_prefix" class="form-input" 
                                           value="{{ old('email_prefix', $emailAccount->email_prefix ?? '') }}" 
                                           placeholder="e.g., sales, support, info"
                                           {{ isset($emailAccount) ? 'readonly' : 'required' }}>
                                    <div class="form-help">This will create an email like sales@yourdomain.com</div>
                                    @error('email_prefix')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="text" class="form-input" value="{{ $emailAccount->email_address }}" readonly>
                                </div>
                            @endif
                            
                            <div class="form-group">
                                <label class="form-label" for="display_name">Display Name</label>
                                <input type="text" id="display_name" name="display_name" class="form-input" 
                                       value="{{ old('display_name', $emailAccount->display_name ?? '') }}"
                                       placeholder="e.g., Sales Team">
                            </div>
                            
                            <div class="form-group full-width">
                                <label class="form-label" for="description">Description</label>
                                <textarea id="description" name="description" class="form-input form-textarea" 
                                          placeholder="Brief description of this email account">{{ old('description', $emailAccount->description ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Incoming Mail Server -->
                    <div class="form-section">
                        <h2 class="section-title">Incoming Mail Server (IMAP/POP3)</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="incoming_server_type">Server Type <span style="color: hsl(var(--destructive));">*</span></label>
                                <select id="incoming_server_type" name="incoming_server_type" class="form-input form-select" required>
                                    <option value="imap" {{ old('incoming_server_type', $emailAccount->incoming_server_type ?? 'imap') == 'imap' ? 'selected' : '' }}>IMAP</option>
                                    <option value="pop3" {{ old('incoming_server_type', $emailAccount->incoming_server_type ?? '') == 'pop3' ? 'selected' : '' }}>POP3</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="incoming_server_host">Server Host <span style="color: hsl(var(--destructive));">*</span></label>
                                <input type="text" id="incoming_server_host" name="incoming_server_host" class="form-input" 
                                       value="{{ old('incoming_server_host', $emailAccount->incoming_server_host ?? '') }}"
                                       placeholder="e.g., imap.gmail.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="incoming_server_port">Port <span style="color: hsl(var(--destructive));">*</span></label>
                                <input type="number" id="incoming_server_port" name="incoming_server_port" class="form-input" 
                                       value="{{ old('incoming_server_port', $emailAccount->incoming_server_port ?? '993') }}"
                                       placeholder="993" required>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="incoming_server_ssl" name="incoming_server_ssl" class="checkbox" value="1"
                                           {{ old('incoming_server_ssl', $emailAccount->incoming_server_ssl ?? true) ? 'checked' : '' }}>
                                    <label for="incoming_server_ssl" class="form-label" style="margin: 0;">Use SSL/TLS</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="incoming_server_username">Username <span style="color: hsl(var(--destructive));">*</span></label>
                                <input type="text" id="incoming_server_username" name="incoming_server_username" class="form-input" 
                                       value="{{ old('incoming_server_username', $emailAccount->incoming_server_username ?? '') }}"
                                       placeholder="your@email.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="incoming_server_password">
                                    Password 
                                    @if(!isset($emailAccount))
                                        <span style="color: hsl(var(--destructive));">*</span>
                                    @endif
                                </label>
                                <input type="password" id="incoming_server_password" name="incoming_server_password" class="form-input" 
                                       placeholder="{{ isset($emailAccount) ? 'Leave blank to keep current password' : 'Enter password' }}"
                                       {{ isset($emailAccount) ? '' : 'required' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Outgoing Mail Server -->
                    <div class="form-section">
                        <h2 class="section-title">Outgoing Mail Server (SMTP)</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="outgoing_server_host">Server Host <span style="color: hsl(var(--destructive));">*</span></label>
                                <input type="text" id="outgoing_server_host" name="outgoing_server_host" class="form-input" 
                                       value="{{ old('outgoing_server_host', $emailAccount->outgoing_server_host ?? '') }}"
                                       placeholder="e.g., smtp.gmail.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="outgoing_server_port">Port <span style="color: hsl(var(--destructive));">*</span></label>
                                <input type="number" id="outgoing_server_port" name="outgoing_server_port" class="form-input" 
                                       value="{{ old('outgoing_server_port', $emailAccount->outgoing_server_port ?? '587') }}"
                                       placeholder="587" required>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="outgoing_server_ssl" name="outgoing_server_ssl" class="checkbox" value="1"
                                           {{ old('outgoing_server_ssl', $emailAccount->outgoing_server_ssl ?? true) ? 'checked' : '' }}>
                                    <label for="outgoing_server_ssl" class="form-label" style="margin: 0;">Use SSL/TLS</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="outgoing_server_username">Username <span style="color: hsl(var(--destructive));">*</span></label>
                                <input type="text" id="outgoing_server_username" name="outgoing_server_username" class="form-input" 
                                       value="{{ old('outgoing_server_username', $emailAccount->outgoing_server_username ?? '') }}"
                                       placeholder="your@email.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="outgoing_server_password">
                                    Password 
                                    @if(!isset($emailAccount))
                                        <span style="color: hsl(var(--destructive));">*</span>
                                    @endif
                                </label>
                                <input type="password" id="outgoing_server_password" name="outgoing_server_password" class="form-input" 
                                       placeholder="{{ isset($emailAccount) ? 'Leave blank to keep current password' : 'Enter password' }}"
                                       {{ isset($emailAccount) ? '' : 'required' }}>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ isset($emailAccount) ? 'Update' : 'Create' }} Email Account
                        </button>
                        <a href="{{ route('inbox.settings.accounts') }}" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
