@extends('layouts.app')

@section('title', 'Welcome - ' . ($tenant->name ?? 'Tenant'))

@section('styles')
    <style>
        .welcome-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .tenant-badge {
            display: inline-block;
            background-color: #e0f2ff;
            color: #0066cc;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .welcome-actions {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .welcome-actions a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3b82f6;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .welcome-actions a:hover {
            background-color: #2563eb;
        }
    </style>
@endsection

@section('content')
    <div class="welcome-container">
        <h1>Welcome to CollaborInbox <span class="tenant-badge">{{ $tenant->name ?? 'Tenant' }}</span></h1>
        
        <p class="welcome-message">
            Thank you for choosing CollaborInbox for your collaborative inbox management needs. 
            Your tenant has been set up and is ready to use.
        </p>
        
        <div class="tenant-info">
            <p><strong>Tenant Name:</strong> {{ $tenant->name ?? 'Unknown' }}</p>
            <p><strong>Domain:</strong> {{ $tenant->domain ?? request()->getHost() }}</p>
            <p><strong>Active Since:</strong> {{ isset($tenant->created_at) ? $tenant->created_at->format('M d, Y') : 'Today' }}</p>
        </div>
        
        <div class="welcome-actions">
            <a href="{{ route('tenant.dashboard') }}">Go to Dashboard</a>
            <a href="{{ url('/user/profile') }}">Setup Profile</a>
        </div>
    </div>
@endsection 