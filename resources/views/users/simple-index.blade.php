@extends('layouts.app')

@section('title', 'User Management - CollaborInbox')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.875rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem 0;">User Management</h1>
        <p style="color: #6b7280; font-size: 0.875rem;">Manage system users, roles, and permissions</p>
    </div>

    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <h3 style="margin: 0; font-size: 1.125rem; font-weight: 600;">All Users</h3>
        </div>

        <div style="padding: 1.5rem;">
            @if (session('success'))
                <div style="padding: 1rem; background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 0.5rem; margin-bottom: 1rem;">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div style="padding: 1rem; background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 0.5rem; margin-bottom: 1rem;">
                    {{ session('error') }}
                </div>
            @endif

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding: 0.75rem; font-weight: 500; color: #6b7280; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">ID</th>
                            <th style="text-align: left; padding: 0.75rem; font-weight: 500; color: #6b7280; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">Name</th>
                            <th style="text-align: left; padding: 0.75rem; font-weight: 500; color: #6b7280; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">Email</th>
                            <th style="text-align: left; padding: 0.75rem; font-weight: 500; color: #6b7280; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">Role</th>
                            <th style="text-align: left; padding: 0.75rem; font-weight: 500; color: #6b7280; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">Status</th>
                            <th style="text-align: left; padding: 0.75rem; font-weight: 500; color: #6b7280; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">Admin</th>
                            <th style="text-align: left; padding: 0.75rem; font-weight: 500; color: #6b7280; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $users = \App\Models\User::with(['role'])->paginate(10);
                        @endphp
                        
                        @forelse ($users as $user)
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 0.75rem;">{{ $user->id }}</td>
                                <td style="padding: 0.75rem;">{{ $user->name }}</td>
                                <td style="padding: 0.75rem;">{{ $user->email }}</td>
                                <td style="padding: 0.75rem;">
                                    @if($user->role)
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #dbeafe; color: #1e40af;">
                                            {{ $user->role->name }}
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #e5e7eb; color: #374151;">
                                            No Role
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem;">
                                    @if($user->is_active)
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #d1fae5; color: #065f46;">
                                            Active
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fee2e2; color: #991b1b;">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem;">
                                    @if($user->is_admin)
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #dbeafe; color: #1d4ed8;">
                                            Yes
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #e5e7eb; color: #374151;">
                                            No
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem;">{{ $user->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 2rem; text-align: center; color: #6b7280;">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $users->links() }}
            </div>

            <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                <a href="{{ route('dashboard') }}" style="padding: 0.5rem 1rem; background-color: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center;">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
