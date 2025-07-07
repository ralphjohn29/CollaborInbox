@extends('layouts.app')

@section('title', 'Tenant Dashboard - CollaborInbox')

@section('styles')
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

        .tenant-pill {
            display: inline-block;
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            white-space: nowrap;
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
            @if(isset($tenant))
            <span class="tenant-pill">{{ $tenant->name ?? 'Tenant' }}</span>
            @endif
        </div>
        
        <div class="sidebar-user">
            <div class="user-avatar">
                {{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'U' }}
            </div>
            <div class="user-info">
                <h4>{{ auth()->user() ? auth()->user()->name : 'User' }}</h4>
                <p>{{ auth()->user() ? auth()->user()->email : 'user@example.com' }}</p>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="{{ route('tenant.dashboard') }}" class="sidebar-menu-item active">
                <i class="fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-tasks"></i>
                <span class="menu-text">Tasks</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Users</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
            </a>
            
            <a href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
               class="sidebar-menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Logout</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
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
                        {{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'U' }}
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
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-wrapper">
            <div class="page-title">
                <div>
                    <h1>Tenant Dashboard</h1>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ul>
                </div>
                <div>
                    <button class="btn btn-primary" style="background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px;">
                        <i class="fas fa-plus"></i> New Task
                    </button>
                </div>
            </div>

            <!-- Tenant Information Card -->
            @if(isset($tenant))
            <div class="card">
                <div class="card-header">
                    Tenant Information
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                        <div style="flex: 1; min-width: 300px;">
                            <p><strong>Name:</strong> {{ $tenant->name ?? 'N/A' }}</p>
                            <p><strong>Domain:</strong> <a href="{{ strpos($tenant->domain ?? '', 'http') === 0 ? $tenant->domain : 'https://' . $tenant->domain }}" target="_blank" style="color: var(--primary-color); text-decoration: none;">{{ $tenant->domain ?? 'N/A' }} <i class="fas fa-external-link-alt fa-xs"></i></a></p>
                        </div>
                        <div style="flex: 1; min-width: 300px;">
                            <p><strong>Status:</strong> <span style="padding: 0.25rem 0.5rem; background-color: rgba(34, 197, 94, 0.1); color: var(--success-color); border-radius: 20px; font-size: 0.75rem;">Active</span></p>
                            <p><strong>Created:</strong> {{ isset($tenant->created_at) ? $tenant->created_at->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p id="totalUsers">0</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Completed Tasks</h3>
                        <p id="completedTasks">0</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pending Tasks</h3>
                        <p id="pendingTasks">0</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Projects</h3>
                        <p id="totalProjects">0</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <div>Recent Activity</div>
                </div>
                <div class="card-body">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead style="text-align: left;">
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <th style="padding: 0.75rem;">User</th>
                                <th style="padding: 0.75rem;">Activity</th>
                                <th style="padding: 0.75rem;">Time</th>
                            </tr>
                        </thead>
                        <tbody id="recentActivity">
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <td colspan="3" style="padding: 0.75rem; text-align: center;">Loading activity data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            
            // Mobile Sidebar
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
        
        // Fetch dashboard data
        fetchDashboardData();
    });
    
    function fetchDashboardData() {
        // Simulate data loading (replace with actual API calls)
        setTimeout(() => {
            document.getElementById('totalUsers').textContent = '24';
            document.getElementById('completedTasks').textContent = '156';
            document.getElementById('pendingTasks').textContent = '42';
            document.getElementById('totalProjects').textContent = '8';
            
            // Populate recent activity
            const activityData = [
                { user: 'John Doe', action: 'Created a new task', time: '10 minutes ago' },
                { user: 'Jane Smith', action: 'Completed Project X', time: '2 hours ago' },
                { user: 'Bob Johnson', action: 'Updated user profile', time: '3 hours ago' },
                { user: 'Alice Williams', action: 'Commented on Task #123', time: '5 hours ago' }
            ];
            
            const activityTable = document.getElementById('recentActivity');
            if (activityTable) {
                activityTable.innerHTML = '';
                activityData.forEach(item => {
                    activityTable.innerHTML += `
                        <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <td style="padding: 0.75rem;">${item.user}</td>
                            <td style="padding: 0.75rem;">${item.action}</td>
                            <td style="padding: 0.75rem;">${item.time}</td>
                        </tr>
                    `;
                });
            }
        }, 1000);
    }
</script>
@endsection 