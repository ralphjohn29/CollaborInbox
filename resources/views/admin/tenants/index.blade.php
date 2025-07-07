@extends('layouts.app')

@section('title', 'Tenant Management - CollaborInbox')

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
        
        /* Status Badge */
        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        
        .bg-success {
            background-color: var(--success-color);
        }
        
        .bg-danger {
            background-color: var(--danger-color);
        }
        
        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 0.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .table th {
            font-weight: 600;
            text-align: left;
            color: var(--secondary-color);
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
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
        </div>
        
        <div class="sidebar-user">
            <div class="user-avatar">
                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
            </div>
            <div class="user-info">
                <h4>{{ Auth::user()->name }}</h4>
                <p>{{ Auth::user()->role->name ?? 'Admin' }}</p>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="{{ url('/dashboard') }}" class="sidebar-menu-item">
                <i class="fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            
            <a href="{{ route('tenants.index') }}" class="sidebar-menu-item active">
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
            <input type="text" id="searchTenant" class="search-input" placeholder="Search tenants...">
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
                    <h1>Tenant Management</h1>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Tenants</li>
                    </ul>
                </div>
                <div>
                    <a href="{{ route('tenants.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Tenant
                    </a>
                </div>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success" style="background-color: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" style="background-color: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            
            <!-- Tenants List Card -->
            <div class="card">
                <div class="card-header">
                    <div>Tenants</div>
                    <div>
                        <button class="btn btn-outline" style="margin-right: 10px;">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button class="btn btn-outline">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($tenants) > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Subdomain</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tenants-list">
                                    @foreach($tenants as $tenant)
                                        <tr>
                                            <td>{{ $tenant->id }}</td>
                                            <td>{{ $tenant->name }}</td>
                                            <td>
                                                <a href="http://{{ $tenant->domain }}.collaborinbox.test:8000" 
                                                   target="_blank" style="color: var(--primary-color); text-decoration: none; display: flex; align-items: center;">
                                                    {{ $tenant->domain }}
                                                    <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge {{ $tenant->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($tenant->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <a href="{{ route('tenants.edit', $tenant->id) }}" 
                                                       class="action-btn btn-edit">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="http://{{ $tenant->domain }}.collaborinbox.test:8000" 
                                                       target="_blank" class="action-btn btn-view">
                                                        <i class="fas fa-external-link-alt"></i> Visit
                                                    </a>
                                                    <button type="button" class="action-btn btn-delete" 
                                                            onclick="confirmDelete('{{ $tenant->id }}', '{{ $tenant->name }}')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                    <form id="delete-form-{{ $tenant->id }}" action="{{ route('tenants.destroy', $tenant->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
                            {{ $tenants->links() }}
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem 0;">
                            <i class="fas fa-building" style="font-size: 3rem; color: var(--secondary-color); opacity: 0.5; margin-bottom: 1rem;"></i>
                            <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem;">No tenants found</h3>
                            <p style="color: var(--secondary-color); margin-bottom: 1.5rem;">Get started by creating your first tenant</p>
                            <a href="{{ route('tenants.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Add New Tenant
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Tenant Stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Tenants</h3>
                        <p>{{ $tenants->total() }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Active Tenants</h3>
                        <p>{{ $tenants->where('status', 'active')->count() }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <h3>New This Month</h3>
                        <p>{{ $tenants->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Storage Used</h3>
                        <p>{{ $tenants->count() * 15 }} MB</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        // Confirm delete function
        function confirmDelete(id, name) {
            if (confirm(`Are you sure you want to delete tenant "${name}"? This action cannot be undone and will remove all tenant data.`)) {
                document.getElementById(`delete-form-${id}`).submit();
            }
        }
    
        // Sidebar toggle
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
        
        // Mobile sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            if (window.innerWidth < 992) {
                document.getElementById('sidebar').classList.toggle('show');
            }
        });
        
        // User dropdown
        document.getElementById('userDropdown').addEventListener('click', function() {
            document.getElementById('userMenu').classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.user-dropdown')) {
                document.getElementById('userMenu').classList.remove('show');
            }
        });
        
        // Tenant search
        document.getElementById('searchTenant').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tenants-list tr');
            
            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                const domain = row.cells[2].textContent.toLowerCase();
                
                if (name.includes(searchTerm) || domain.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endsection 