@extends('layouts.app')

@section('title', 'Dashboard - CollaborInbox')

@section('body-class', 'dashboard-page')

@section('styles')
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

        /* Override body styles for dashboard page */
        body.dashboard-page {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #fafafa !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif !important;
        }

        .modern-dashboard {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            color: hsl(var(--foreground)) !important;
            background-color: #fafafa !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
        }

        /* Layout */
        .dashboard-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
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

        /* Main Content */
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

        .header-search {
            flex: 1;
            max-width: 400px;
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

        .header-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-button {
            padding: 0.5rem;
            border: 1px solid transparent;
            background: none;
            cursor: pointer;
            color: hsl(var(--muted-foreground));
            border-radius: calc(var(--radius) - 2px);
            transition: all 0.2s ease;
            position: relative;
        }

        .header-button:hover {
            background-color: hsl(var(--accent));
            color: hsl(var(--accent-foreground));
        }

        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background-color: hsl(var(--destructive));
            border-radius: 50%;
            border: 2px solid hsl(var(--card));
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

        /* Content Area */
        .content {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .page-header {
            margin-bottom: 1.5rem;
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

        /* Cards */
        .card {
            background-color: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: hsl(var(--foreground));
            margin: 0;
        }

        .card-description {
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background-color: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            padding: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: calc(var(--radius) - 2px);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon.primary {
            background-color: hsl(var(--primary) / 0.1);
            color: hsl(var(--primary));
        }

        .stat-icon.success {
            background-color: hsl(142.1 76.2% 36.3% / 0.1);
            color: hsl(142.1 76.2% 36.3%);
        }

        .stat-icon.warning {
            background-color: hsl(38 92% 50% / 0.1);
            color: hsl(38 92% 50%);
        }

        .stat-icon.info {
            background-color: hsl(217.2 91.2% 59.8% / 0.1);
            color: hsl(217.2 91.2% 59.8%);
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: hsl(var(--muted-foreground));
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: hsl(var(--foreground));
            line-height: 1;
        }

        .stat-change {
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change.positive {
            color: hsl(142.1 76.2% 36.3%);
        }

        .stat-change.negative {
            color: hsl(var(--destructive));
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            text-align: left;
            padding: 0.75rem;
            font-weight: 500;
            color: hsl(var(--muted-foreground));
            border-bottom: 1px solid hsl(var(--border));
            font-size: 0.875rem;
        }

        .table td {
            padding: 0.75rem;
            border-bottom: 1px solid hsl(var(--border));
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover {
            background-color: hsl(var(--muted) / 0.5);
        }

        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1;
        }

        .badge.success {
            background-color: hsl(142.1 76.2% 36.3% / 0.1);
            color: hsl(142.1 76.2% 36.3%);
        }

        .badge.warning {
            background-color: hsl(38 92% 50% / 0.1);
            color: hsl(38 92% 50%);
        }

        .badge.danger {
            background-color: hsl(var(--destructive) / 0.1);
            color: hsl(var(--destructive));
        }

        .badge.info {
            background-color: hsl(217.2 91.2% 59.8% / 0.1);
            color: hsl(217.2 91.2% 59.8%);
        }

        /* Buttons */
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

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 50;
                transform: translateX(-100%);
                transition: transform 0.2s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header-search {
                display: none;
            }
        }

        /* Dropdown Menu */
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

        /* Charts placeholder */
        .chart-container {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: hsl(var(--muted) / 0.3);
            border-radius: calc(var(--radius) - 2px);
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
        }

        /* Activity Feed */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .activity-item {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background-color: hsl(var(--muted));
            color: hsl(var(--muted-foreground));
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-size: 0.875rem;
            color: hsl(var(--foreground));
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.75rem;
            color: hsl(var(--muted-foreground));
        }
    </style>
@endsection

@section('content')
<!-- MODERN DASHBOARD MARKER -->
<div class="modern-dashboard">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ url('/dashboard') }}" class="sidebar-logo">
                    <div class="sidebar-logo-icon">CI</div>
                    <span class="sidebar-logo-text">CollaborInbox</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <a href="{{ url('/dashboard') }}" class="nav-item active">
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
                
                <a href="{{ url('/dispositions') }}" class="nav-item">
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
                
                {{-- Analytics - Coming Soon --}}
                {{-- <a href="#" class="nav-item">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 20h.01"/>
                            <path d="M7 20v-4"/>
                            <path d="M12 20v-8"/>
                            <path d="M17 20V8"/>
                            <path d="M22 4v16"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Analytics</span>
                </a> --}}
                
                @if(auth()->check() && auth()->user()->is_admin)
                <a href="{{ url('/users') }}" class="nav-item">
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
                @endif
                
                {{-- Other Features - Coming Soon --}}
                {{-- <a href="#" class="nav-item">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Products</span>
                </a>
                
                <a href="#" class="nav-item">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Orders</span>
                </a>
                
                <a href="#" class="nav-item">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6m11-11h-6m-6 0H1"/>
                            <path d="m20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Settings</span>
                </a> --}}
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
                
                <div class="header-search">
                    <input type="text" class="search-input" placeholder="Search...">
                </div>
                
                <div class="header-actions">
                    <button class="header-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="notification-badge"></span>
                    </button>
                    
                    <div class="dropdown">
                        <div class="user-menu" id="userMenuToggle">
                            <div class="user-avatar">
                                @auth
                                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                                @else
                                    U
                                @endauth
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
                            <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Logout
                            </a>
                            <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-description">Welcome back @auth{{ Auth::user()->name }}@endauth! Here's what's happening with your store today.</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <line x1="12" y1="8" x2="12" y2="16"/>
                                <line x1="8" y1="12" x2="16" y2="12"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Total Sales</div>
                            <div class="stat-value">$12,456</div>
                            <div class="stat-change positive">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                    <polyline points="17 6 23 6 23 12"/>
                                </svg>
                                12% from last month
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="8.5" cy="7" r="4"/>
                                <line x1="20" y1="8" x2="20" y2="14"/>
                                <line x1="23" y1="11" x2="17" y2="11"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">New Customers</div>
                            <div class="stat-value">1,234</div>
                            <div class="stat-change positive">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                    <polyline points="17 6 23 6 23 12"/>
                                </svg>
                                8% from last month
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Pending Orders</div>
                            <div class="stat-value">23</div>
                            <div class="stat-change negative">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
                                    <polyline points="17 18 23 18 23 12"/>
                                </svg>
                                3% from last month
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon info">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Revenue</div>
                            <div class="stat-value">$45,231</div>
                            <div class="stat-change positive">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                    <polyline points="17 6 23 6 23 12"/>
                                </svg>
                                15% from last month
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Sales Overview</h3>
                            <button class="btn btn-outline">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                Export
                            </button>
                        </div>
                        <div class="chart-container">
                            Chart will be displayed here
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Activity</h3>
                        </div>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="21" r="1"/>
                                        <circle cx="20" cy="21" r="1"/>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                    </svg>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">New order #1234</div>
                                    <div class="activity-time">2 minutes ago</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">New customer registered</div>
                                    <div class="activity-time">1 hour ago</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                        <polyline points="22 4 12 14.01 9 11.01"/>
                                    </svg>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Order #1233 completed</div>
                                    <div class="activity-time">3 hours ago</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Table -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">Recent Orders</h3>
                            <p class="card-description">A list of your recent orders.</p>
                        </div>
                        <a href="#" class="btn btn-primary">View All</a>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#3210</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div class="user-avatar" style="width: 28px; height: 28px; font-size: 0.75rem;">O</div>
                                            <div>
                                                <div style="font-weight: 500;">Olivia Martin</div>
                                                <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">olivia.martin@email.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge success">Completed</span></td>
                                    <td>2024-01-15</td>
                                    <td>$125.00</td>
                                    <td>
                                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"/>
                                                <circle cx="12" cy="5" r="1"/>
                                                <circle cx="12" cy="19" r="1"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#3209</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div class="user-avatar" style="width: 28px; height: 28px; font-size: 0.75rem;">J</div>
                                            <div>
                                                <div style="font-weight: 500;">Jackson Lee</div>
                                                <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">jackson.lee@email.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge warning">Processing</span></td>
                                    <td>2024-01-14</td>
                                    <td>$89.00</td>
                                    <td>
                                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"/>
                                                <circle cx="12" cy="5" r="1"/>
                                                <circle cx="12" cy="19" r="1"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#3208</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div class="user-avatar" style="width: 28px; height: 28px; font-size: 0.75rem;">I</div>
                                            <div>
                                                <div style="font-weight: 500;">Isabella Nguyen</div>
                                                <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">isabella.nguyen@email.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge danger">Cancelled</span></td>
                                    <td>2024-01-13</td>
                                    <td>$299.00</td>
                                    <td>
                                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"/>
                                                <circle cx="12" cy="5" r="1"/>
                                                <circle cx="12" cy="19" r="1"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#3207</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div class="user-avatar" style="width: 28px; height: 28px; font-size: 0.75rem;">S</div>
                                            <div>
                                                <div style="font-weight: 500;">Sophia Anderson</div>
                                                <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">sophia.anderson@email.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge success">Completed</span></td>
                                    <td>2024-01-12</td>
                                    <td>$450.00</td>
                                    <td>
                                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"/>
                                                <circle cx="12" cy="5" r="1"/>
                                                <circle cx="12" cy="19" r="1"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            
            // Mobile behavior
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('open');
            }
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
        
        // Mobile sidebar overlay
        if (window.innerWidth <= 768) {
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 40; display: none;';
            document.body.appendChild(overlay);
            
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.style.display = 'none';
            });
            
            // Show/hide overlay when sidebar opens/closes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
                    }
                });
            });
            
            observer.observe(sidebar, { attributes: true });
        }
    });
</script>
@endsection
