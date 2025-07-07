@extends('layouts.app')

@section('title', 'Manage Agents - CollaborInbox')

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
        /* Add any other styles from dashboard.css if needed */
    </style>
@endsection

@section('content')
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h2>
                <i class="fas fa-inbox"></i> 
                <span class="brand-text">CollaborInbox</span>
            </h2>
            {{-- Assuming $tenant is available or fetch it --}}
            @if(isset($tenant) || optional(auth()->user())->tenant)
            <span class="tenant-pill">{{ $tenant->name ?? auth()->user()->tenant->name ?? 'Tenant' }}</span>
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
            <a href="{{ route('tenant.dashboard') }}" class="sidebar-menu-item"> {{-- Remove active class --}}
                <i class="fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            
            {{-- Example: Add Agent link and mark as active --}}
            <a href="{{ route('agents.index') }}" class="sidebar-menu-item active"> {{-- Add active class --}}
                <i class="fas fa-users-cog"></i> {{-- Different icon maybe? --}}
                <span class="menu-text">Manage Agents</span>
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
                <span class="badge">3</span> {{-- Example badge --}}
            </div>
            
            <div class="topbar-icon">
                <i class="fas fa-envelope"></i>
                <span class="badge">5</span> {{-- Example badge --}}
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

    <main class="main-content">
        <div class="content-wrapper">
            <div class="page-title">
                <div>
                    <h1>Manage Agents</h1>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li> {{-- Link to dashboard --}}
                        <li class="breadcrumb-item active">Agents</li>
                    </ul>
                </div>
                <div>
                    <a href="{{ route('agents.create') }}" class="btn btn-primary" style="background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px;"> 
                        <i class="fas fa-plus"></i> Add New Agent
                    </a>
                </div>
            </div>
            
            <div class="card mt-4"> {{-- Added mt-4 for spacing --}}
                <div class="card-header">
                    Agent List
                </div>
                <div class="card-body">
                    @if($agents->isEmpty())
                        <p class="text-muted">No agents found for this tenant.</p>
                    @else
                        <table class="table table-striped table-hover"> {{-- Added table-hover --}}
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agents as $agent)
                                    <tr>
                                        <td>{{ $agent->id }}</td>
                                        <td>{{ $agent->name }}</td>
                                        <td>{{ $agent->email }}</td>
                                        <td>{{ $agent->role ? $agent->role->name : 'N/A' }}</td>
                                        <td>
                                            {{-- Assuming is_active exists on User model --}}
                                            <span class="badge {{ $agent->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $agent->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- Placeholder links --}}
                                            <a href="#" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> Edit</a>
                                            <a href="#" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
        
                        {{-- Pagination Links --}}
                        <div class="mt-3">
                            {{ $agents->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
{{-- Add any specific scripts for this page here if needed --}}
@endsection