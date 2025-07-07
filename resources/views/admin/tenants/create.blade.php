@extends('layouts.app')

@section('title', 'Create New Tenant')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Create New Tenant</h1>
                <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary">
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
                    <form action="{{ route('tenants.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Tenant Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Tenant Information</h5>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tenant Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">The organization or company name</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="domain" class="form-label">Subdomain <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                                               id="domain" name="domain" value="{{ old('domain') }}" 
                                               pattern="[a-z0-9\-]+" required>
                                        <span class="input-group-text">.collaborinbox.test</span>
                                        @error('domain')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">
                                        Lowercase letters, numbers, and hyphens only. Cannot be changed later.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="database" class="form-label">Database Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('database') is-invalid @enderror" 
                                           id="database" name="database" value="{{ old('database') }}" 
                                           pattern="[a-z0-9_]+" required>
                                    @error('database')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Lowercase letters, numbers, and underscores only. Cannot be changed later.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Admin User Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Admin User</h5>
                                
                                <div class="mb-3">
                                    <label for="admin_name" class="form-label">Admin Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('admin_name') is-invalid @enderror" 
                                           id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required>
                                    @error('admin_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Admin Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('admin_email') is-invalid @enderror" 
                                           id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required>
                                    @error('admin_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">This will be used for the initial admin login</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="admin_password" class="form-label">Admin Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('admin_password') is-invalid @enderror" 
                                           id="admin_password" name="admin_password" required>
                                    @error('admin_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Minimum 8 characters</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-top pt-3 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Create Tenant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-generate domain from name
    document.getElementById('name').addEventListener('input', function() {
        const nameField = this;
        const domainField = document.getElementById('domain');
        const databaseField = document.getElementById('database');
        
        // Only auto-fill if domain hasn't been manually edited
        if (!domainField.dataset.manuallyEdited) {
            let value = nameField.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // Remove special characters except spaces and hyphens
                .replace(/\s+/g, '-');        // Replace spaces with hyphens
            
            domainField.value = value;
        }
        
        // Only auto-fill if database hasn't been manually edited
        if (!databaseField.dataset.manuallyEdited) {
            let value = nameField.value.toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')  // Remove special characters and spaces
                .replace(/\s+/g, '_');        // Replace spaces with underscores
            
            databaseField.value = 'tenant_' + value;
        }
    });
    
    // Mark fields as manually edited
    document.getElementById('domain').addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });
    
    document.getElementById('database').addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });
</script>
@endpush 