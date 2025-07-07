@extends('layouts.app')

@section('title', 'Edit Tenant')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Tenant: {{ $tenant->name }}</h1>
        <a href="{{ route('tenants.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Tenants
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('tenants.update', $tenant->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h4 class="h5 mb-3">Tenant Information</h4>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Tenant Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name', $tenant->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subdomain" class="form-label">Subdomain</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('subdomain') is-invalid @enderror" 
                                    id="subdomain" name="subdomain" value="{{ old('subdomain', $tenant->domain) }}" 
                                    required {{ $tenant->id ? 'readonly' : '' }}>
                                <span class="input-group-text">.collaborinbox.test</span>
                            </div>
                            @error('subdomain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Subdomain cannot be changed after creation.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                                <option value="active" {{ old('status', $tenant->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $tenant->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="database_name" class="form-label">Database Name</label>
                            <input type="text" class="form-control" 
                                value="{{ $tenant->database }}" readonly disabled>
                            <small class="text-muted">
                                Database name cannot be changed after creation.
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="h5 mb-3">Admin User Information</h4>
                        
                        <div class="mb-3">
                            <label for="admin_name" class="form-label">Admin Name</label>
                            <input type="text" class="form-control @error('admin_name') is-invalid @enderror" 
                                id="admin_name" name="admin_name" value="{{ old('admin_name', $admin->name ?? '') }}" required>
                            @error('admin_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Admin Email</label>
                            <input type="email" class="form-control @error('admin_email') is-invalid @enderror" 
                                id="admin_email" name="admin_email" value="{{ old('admin_email', $admin->email ?? '') }}" required>
                            @error('admin_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_password" class="form-label">Admin Password</label>
                            <input type="password" class="form-control @error('admin_password') is-invalid @enderror" 
                                id="admin_password" name="admin_password">
                            @error('admin_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Leave blank to keep current password. Must be at least 8 characters.
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Tenant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 