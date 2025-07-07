@extends('layouts.app')

@section('title', 'Dashboard - CollaborInbox')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #64748b;
            --success-color: #22c55e;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --light-bg: #f9fafb;
            --sidebar-width: 250px;
            --sidebar-collapsed: 70px;
            --topbar-height: 60px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }

        body {
            overflow-x: hidden;
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: var(--sidebar-width);
            background-color: #fff;
            box-shadow: var(--box-shadow);
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar-brand {
            padding: 1rem 1.5rem;
            height: auto;
            min-height: var(--topbar-height);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .sidebar-brand h2 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
        }

        .sidebar.collapsed .brand-text {
            display: none;
        }
        
        .sidebar-user {
            padding: 1rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.75rem;
        }
        
        .sidebar.collapsed .user-info {
            display: none;
        }
        
        .user-info h4 {
            margin: 0;
            font-size: 0.9rem;
        }
        
        .user-info p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--secondary-color);
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu-item {
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            color: var(--secondary-color);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu-item:hover, .sidebar-menu-item.active {
            background-color: rgba(79, 70, 229, 0.05);
            color: var(--primary-color);
            border-left: 3px solid var(--primary-color);
        }

        .sidebar-menu-item i {
            width: 20px;
            text-align: center;
            margin-right: 0.75rem;
        }
        
        .sidebar.collapsed .menu-text {
            display: none;
        }

        /* Topbar Styles */
        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--topbar-height);
            background-color: #fff;
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            transition: all 0.3s;
            z-index: 999;
        }

        .sidebar.collapsed ~ .topbar {
            left: var(--sidebar-collapsed);
        }

        .menu-toggle {
            background: none;
            border: none;
            color: var(--secondary-color);
            cursor: pointer;
            font-size: 1.25rem;
        }

        .topbar-search {
            margin-left: 1rem;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: rgba(0, 0, 0, 0.02);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .topbar-icon {
            color: var(--secondary-color);
            font-size: 1.25rem;
            padding: 0.5rem;
            position: relative;
            cursor: pointer;
        }

        .topbar-icon .badge {
            position: absolute;
            top: 0.2rem;
            right: 0.2rem;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: var(--danger-color);
            color: white;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-dropdown {
            margin-left: 1rem;
            position: relative;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #fff;
            min-width: 180px;
            box-shadow: var(--box-shadow);
            border-radius: 4px;
            padding: 0.5rem 0;
            display: none;
            z-index: 1000;
        }

        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            color: var(--secondary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .dropdown-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .dropdown-item i {
            width: 20px;
            margin-right: 0.5rem;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
            min-height: 100vh;
            transition: all 0.3s;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed);
        }

        .content-wrapper {
            padding: 1.5rem;
        }

        .page-title {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .breadcrumb {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            font-size: 0.9rem;
        }

        .breadcrumb-item {
            color: var(--secondary-color);
        }

        .breadcrumb-item.active {
            color: var(--primary-color);
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: '/';
            padding: 0 0.5rem;
            color: var(--secondary-color);
        }

        /* Card Styles */
        .card {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        .stat-info p {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .bg-primary { background-color: rgba(79, 70, 229, 0.1); color: var(--primary-color); }
        .bg-success { background-color: rgba(34, 197, 94, 0.1); color: var(--success-color); }
        .bg-warning { background-color: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
        .bg-info { background-color: rgba(59, 130, 246, 0.1); color: var(--info-color); }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .topbar, .main-content {
                left: 0 !important;
                margin-left: 0 !important;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h2>
                <i class="fas fa-inbox"></i> 
                <span class="brand-text">CollaborInbox</span>
            </h2>
            <!-- Optional tenant pill for dashboard if needed -->
        </div>
        
        <div class="sidebar-user">
            <div class="user-avatar">
                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
            </div>
            <div class="user-info">
                <h4>{{ Auth::user()->name }}</h4>
                <p>{{ Auth::user()->role->name ?? 'User' }}</p>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="{{ url('/dashboard') }}" class="sidebar-menu-item active">
                <i class="fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            
            <a href="{{ route('tenants.index') }}" class="sidebar-menu-item">
                <i class="fas fa-building"></i>
                <span class="menu-text">Manage Tenants</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-envelope"></i>
                <span class="menu-text">Inbox</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-ticket-alt"></i>
                <span class="menu-text">Tickets</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Team</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reports</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
            </a>
        </div>
    </aside>

    <!-- Topbar -->
    <header class="topbar">
        <button class="menu-toggle" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="topbar-search">
            <input type="text" class="search-input" placeholder="Search...">
        </div>
        
        <div class="topbar-right">
            <div class="topbar-icon">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </div>
            
            <div class="topbar-icon">
                <i class="fas fa-envelope"></i>
                <span class="badge">5</span>
            </div>
            
            <div class="user-dropdown">
                <div class="dropdown-toggle" id="userDropdown">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
                
                <div class="dropdown-menu" id="userMenu">
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <hr style="margin: 0.5rem 0; border-color: rgba(0,0,0,0.05);">
                    <a href="{{ url('/login') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-wrapper">
            <div class="page-title">
                <div>
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ul>
                </div>
                <div>
                    <button class="btn btn-primary" style="background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px;">
                        <i class="fas fa-plus"></i> New Report
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Messages</h3>
                        <p id="total-messages">245</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Open Tickets</h3>
                        <p id="open-tickets">15</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Team Members</h3>
                        <p id="team-members">8</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Resolved</h3>
                        <p id="resolved-tickets">142</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Tickets -->
            <div class="card">
                <div class="card-header">
                    <div>Recent Tickets</div>
                    <a href="#" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">View All</a>
                </div>
                <div class="card-body">
                    <div id="tickets-container">
                        <p>Loading tickets...</p>
                    </div>
                </div>
            </div>
            
            <!-- Activity + Stats Grid -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        Recent Activity
                    </div>
                    <div class="card-body">
                        <div id="activity-container">
                            <p>Loading activity...</p>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        Performance
                    </div>
                    <div class="card-body">
                        <div id="performance-container">
                            <p>Loading performance data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        // Toggle Sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
        
        // Mobile Sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            if (window.innerWidth < 992) {
                document.getElementById('sidebar').classList.toggle('show');
            }
        });
        
        // User Dropdown
        document.getElementById('userDropdown').addEventListener('click', function() {
            document.getElementById('userMenu').classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.user-dropdown')) {
                document.getElementById('userMenu').classList.remove('show');
            }
        });
        
        // Dashboard data loading
        document.addEventListener('DOMContentLoaded', function() {
            // Load tickets
            setTimeout(() => {
                document.getElementById('tickets-container').innerHTML = `
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="text-align: left;">
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <th style="padding: 0.75rem;">ID</th>
                                <th style="padding: 0.75rem;">Subject</th>
                                <th style="padding: 0.75rem;">Status</th>
                                <th style="padding: 0.75rem;">Assigned</th>
                                <th style="padding: 0.75rem;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <td style="padding: 0.75rem;">#TK-001</td>
                                <td style="padding: 0.75rem;">Email integration issue</td>
                                <td style="padding: 0.75rem;"><span style="padding: 0.25rem 0.5rem; background-color: #FEF3C7; color: #D97706; border-radius: 20px; font-size: 0.75rem;">Pending</span></td>
                                <td style="padding: 0.75rem;">Alex Smith</td>
                                <td style="padding: 0.75rem;">Today, 10:30 AM</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <td style="padding: 0.75rem;">#TK-002</td>
                                <td style="padding: 0.75rem;">Login authentication problem</td>
                                <td style="padding: 0.75rem;"><span style="padding: 0.25rem 0.5rem; background-color: #DCFCE7; color: #16A34A; border-radius: 20px; font-size: 0.75rem;">Solved</span></td>
                                <td style="padding: 0.75rem;">Sarah Jones</td>
                                <td style="padding: 0.75rem;">Yesterday</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <td style="padding: 0.75rem;">#TK-003</td>
                                <td style="padding: 0.75rem;">New feature request</td>
                                <td style="padding: 0.75rem;"><span style="padding: 0.25rem 0.5rem; background-color: #E0F2FE; color: #0284C7; border-radius: 20px; font-size: 0.75rem;">In Progress</span></td>
                                <td style="padding: 0.75rem;">Mike Johnson</td>
                                <td style="padding: 0.75rem;">Apr 21, 2023</td>
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem;">#TK-004</td>
                                <td style="padding: 0.75rem;">Dashboard statistics not updated</td>
                                <td style="padding: 0.75rem;"><span style="padding: 0.25rem 0.5rem; background-color: #FEE2E2; color: #DC2626; border-radius: 20px; font-size: 0.75rem;">Urgent</span></td>
                                <td style="padding: 0.75rem;">Lisa Brown</td>
                                <td style="padding: 0.75rem;">Apr 20, 2023</td>
                            </tr>
                        </tbody>
                    </table>
                `;
            }, 1000);
            
            // Load activity
            setTimeout(() => {
                document.getElementById('activity-container').innerHTML = `
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <p style="margin: 0; font-weight: 500;">New email received</p>
                                <p style="margin: 0; color: var(--secondary-color); font-size: 0.9rem;">From: client@example.com</p>
                                <p style="margin: 0; color: var(--secondary-color); font-size: 0.8rem;">10 minutes ago</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <p style="margin: 0; font-weight: 500;">Ticket #TK-002 resolved</p>
                                <p style="margin: 0; color: var(--secondary-color); font-size: 0.9rem;">By: Sarah Jones</p>
                                <p style="margin: 0; color: var(--secondary-color); font-size: 0.8rem;">1 hour ago</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div>
                                <p style="margin: 0; font-weight: 500;">New team member added</p>
                                <p style="margin: 0; color: var(--secondary-color); font-size: 0.9rem;">Mike Johnson joined the team</p>
                                <p style="margin: 0; color: var(--secondary-color); font-size: 0.8rem;">3 hours ago</p>
                            </div>
                        </div>
                    </div>
                `;
            }, 1200);
            
            // Load performance data
            setTimeout(() => {
                document.getElementById('performance-container').innerHTML = `
                    <div style="text-align: center; margin-bottom: 1rem;">
                        <div style="width: 120px; height: 120px; border-radius: 50%; border: 10px solid #E0F2FE; border-top-color: #0284C7; margin: 0 auto; position: relative;">
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.5rem; font-weight: bold;">85%</div>
                        </div>
                        <p style="margin-top: 0.5rem; font-weight: 500;">Resolution Rate</p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <div style="font-size: 1.75rem; font-weight: bold; color: var(--primary-color);">12min</div>
                            <p style="margin: 0; color: var(--secondary-color); font-size: 0.9rem;">Avg. Response Time</p>
                        </div>
                        <div>
                            <div style="font-size: 1.75rem; font-weight: bold; color: var(--success-color);">98%</div>
                            <p style="margin: 0; color: var(--secondary-color); font-size: 0.9rem;">Customer Satisfaction</p>
                        </div>
                    </div>
                `;
            }, 1400);
        });
    </script>
@endsection 