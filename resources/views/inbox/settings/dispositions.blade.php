@extends('layouts.app')

@section('title', 'Dispositions - CollaborInbox')

@section('body-class', 'inbox-settings-page')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reuse the same styles from inbox */
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

        /* Reuse sidebar styles from inbox */
        .sidebar {
            width: 240px;
            background-color: hsl(var(--card));
            border-right: 1px solid hsl(var(--border));
            display: flex;
            flex-direction: column;
            transition: width 0.2s ease;
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

        /* Table styles */
        .table-container {
            background-color: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            text-align: left;
            padding: 1rem;
            font-weight: 500;
            color: hsl(var(--muted-foreground));
            border-bottom: 1px solid hsl(var(--border));
            font-size: 0.875rem;
            background-color: hsl(var(--muted) / 0.5);
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid hsl(var(--border));
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover {
            background-color: hsl(var(--muted) / 0.3);
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

        .btn-danger {
            background-color: hsl(var(--destructive));
            color: hsl(var(--destructive-foreground));
        }

        .btn-danger:hover {
            background-color: hsl(var(--destructive) / 0.9);
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.813rem;
        }

        /* Badge styles */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1;
        }

        .badge-success {
            background-color: hsl(142.1 76.2% 36.3% / 0.1);
            color: hsl(142.1 76.2% 36.3%);
        }

        .badge-danger {
            background-color: hsl(var(--destructive) / 0.1);
            color: hsl(var(--destructive));
        }

        /* Actions column */
        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: hsl(var(--muted));
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: hsl(142.1 76.2% 36.3%);
        }

        input:checked + .slider:before {
            transform: translateX(20px);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: hsl(var(--muted-foreground));
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: hsl(var(--foreground));
        }

        /* Color badge */
        .color-badge {
            display: inline-block;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid hsl(var(--border));
        }

        /* Drag handle */
        .drag-handle {
            cursor: move;
            color: hsl(var(--muted-foreground));
            padding: 0.5rem;
        }

        .drag-handle:hover {
            color: hsl(var(--foreground));
        }

        .sortable-chosen {
            opacity: 0.5;
        }

        .sortable-ghost {
            opacity: 0.2;
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
                <a href="{{ route('inbox.settings.accounts') }}" class="nav-item">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12h8"/>
                            <path d="M12 8v8"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Email Accounts</span>
                </a>
                
                <a href="{{ route('inbox.settings.dispositions') }}" class="nav-item active">
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
            <h1 style="font-size: 1.25rem; font-weight: 600; margin: 0;">Disposition Settings</h1>
        </header>

        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <h1 class="page-title">Email Dispositions</h1>
                <p class="page-description">Create custom dispositions to categorize and organize your emails</p>
            </div>

            <div style="margin-bottom: 1rem;">
                <a href="{{ route('inbox.settings.dispositions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Disposition
                </a>
            </div>

            @if(session('success'))
                <div style="padding: 1rem; background-color: hsl(142.1 76.2% 36.3% / 0.1); color: hsl(142.1 76.2% 36.3%); border-radius: var(--radius); margin-bottom: 1rem;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->has('error'))
                <div style="padding: 1rem; background-color: hsl(var(--destructive) / 0.1); color: hsl(var(--destructive)); border-radius: var(--radius); margin-bottom: 1rem;">
                    {{ $errors->first('error') }}
                </div>
            @endif

            <div class="table-container">
                @if($dispositions->count() > 0)
                    <table class="table" id="dispositionsTable">
                        <thead>
                            <tr>
                                <th width="40"></th>
                                <th>Name</th>
                                <th>Color</th>
                                <th>Description</th>
                                <th>Emails</th>
                                <th>Status</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="dispositionsList">
                            @foreach($dispositions as $disposition)
                                <tr data-id="{{ $disposition->id }}">
                                    <td>
                                        <span class="drag-handle">
                                            <i class="fas fa-grip-vertical"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;">{{ $disposition->name }}</div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span class="color-badge" style="background-color: {{ $disposition->color }};"></span>
                                            <span style="font-size: 0.813rem; color: hsl(var(--muted-foreground));">
                                                {{ $disposition->color }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ $disposition->description ?: '-' }}</td>
                                    <td>
                                        <span class="badge badge-info" style="background-color: hsl(217.2 91.2% 59.8% / 0.1); color: hsl(217.2 91.2% 59.8%);">
                                            {{ $disposition->emails()->count() }} emails
                                        </span>
                                    </td>
                                    <td>
                                        <label class="toggle-switch">
                                            <input type="checkbox" {{ $disposition->is_active ? 'checked' : '' }} 
                                                   onchange="toggleDisposition({{ $disposition->id }})">
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="{{ route('inbox.settings.dispositions.edit', $disposition->id) }}" 
                                               class="btn btn-outline btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @if($disposition->emails()->count() == 0)
                                                <form action="{{ route('inbox.settings.dispositions.destroy', $disposition->id) }}" 
                                                      method="POST" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Are you sure you want to delete this disposition?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="empty-state-title">No dispositions created</div>
                        <p>Create your first disposition to start categorizing emails</p>
                        <a href="{{ route('inbox.settings.dispositions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Disposition
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
function toggleDisposition(dispositionId) {
    fetch(`/inbox/settings/dispositions/${dispositionId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Toggle response:', data);
    })
    .catch(error => {
        console.error('Toggle error:', error);
    });
}

// Initialize sortable for drag and drop reordering
@if($dispositions->count() > 0)
const dispositionsList = document.getElementById('dispositionsList');
const sortable = Sortable.create(dispositionsList, {
    handle: '.drag-handle',
    animation: 150,
    onEnd: function (evt) {
        const dispositionIds = Array.from(dispositionsList.children).map(row => row.dataset.id);
        
        fetch('/inbox/settings/dispositions/reorder', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ dispositions: dispositionIds })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Reorder response:', data);
        })
        .catch(error => {
            console.error('Reorder error:', error);
        });
    }
});
@endif
</script>
@endsection
