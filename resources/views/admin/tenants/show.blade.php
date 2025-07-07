@extends('layouts.admin')

@section('title', 'Tenant Details: ' . $tenant->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-700">Tenant: {{ $tenant->name }}</h1>
        <a href="{{ route('admin.tenants.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Back to List
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tenant Information</h3>
                <dl class="divide-y divide-gray-200">
                    <div class="py-3 flex justify-between text-sm font-medium">
                        <dt class="text-gray-500">ID</dt>
                        <dd class="text-gray-900">{{ $tenant->id }}</dd>
                    </div>
                    <div class="py-3 flex justify-between text-sm font-medium">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="text-gray-900">{{ $tenant->name ?? 'N/A' }}</dd>
                    </div>
                    <div class="py-3 flex justify-between text-sm font-medium">
                        <dt class="text-gray-500">Created At</dt>
                        <dd class="text-gray-900">{{ $tenant->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                    <div class="py-3 flex justify-between text-sm font-medium">
                        <dt class="text-gray-500">Updated At</dt>
                        <dd class="text-gray-900">{{ $tenant->updated_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                    {{-- Add other relevant tenant fields here --}}
                     <div class="py-3 flex justify-between text-sm font-medium">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="text-gray-900">{{-- TODO: Display tenant status (Active/Inactive) --}} Active</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Domains</h3>
                <ul class="divide-y divide-gray-200">
                    @forelse ($tenant->domains as $domain)
                        <li class="py-3 text-sm font-medium text-gray-900">
                            {{ $domain->domain }}
                        </li>
                    @empty
                        <li class="py-3 text-sm text-gray-500">No domains configured.</li>
                    @endforelse
                </ul>
                 {{-- TODO: Add form to add/remove domains --}}
            </div>
        </div>

         <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.tenants.edit', $tenant) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition duration-150">
                Edit Tenant
            </a>
            <form action="{{ route('admin.tenants.destroy', $tenant) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tenant and all associated data? This cannot be undone.');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition duration-150">Delete Tenant</button>
            </form>
             {{-- TODO: Add Activate/Deactivate Buttons --}}
         </div>
    </div>
</div>
@endsection 