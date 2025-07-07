<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenant\MailboxConfigurationController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here is where you can register tenant-specific routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the tenant middleware group. Make something great!
|
*/

// All tenant routes are automatically wrapped with these middleware by the RouteServiceProvider
// You do not need to re-apply InitializeTenancyByDomain middleware here

Route::get('/', function () {
    if (!tenant()) {
        return response()->json([
            'error' => 'Tenant not found',
            'host' => request()->getHost()
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Tenant subdomain accessed successfully',
        'tenant' => [
            'id' => tenant()->id,
            'name' => tenant()->name,
            'domain' => tenant()->domain,
        ]
    ]);
});

// Example tenant dashboard route
Route::get('/dashboard', function () {
    return view('tenant.dashboard', [
        'tenant' => tenant()
    ]);
})->name('tenant.dashboard');

// Tenant information endpoint
Route::get('/tenant-info', function () {
    if (!tenant()) {
        return response()->json([
            'error' => 'Tenant not found',
            'host' => request()->getHost()
        ], 404);
    }
    
    return response()->json([
        'tenant' => [
            'id' => tenant()->id,
            'name' => tenant()->name ?? 'Unnamed Tenant',
            'domain' => tenant()->domains->first()->domain ?? 'No domain',
        ]
    ]);
});

// Additional tenant-specific routes would go here
Route::get('/welcome', function () {
    return view('tenant.welcome', [
        'tenant' => tenant(),
    ]);
});

// Test route to verify tenant middleware functionality on tenant domains
Route::get('/test-tenant-middleware', function () {
    if (!tenant()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Tenant not found',
            'is_tenant_route' => true,
            'host' => request()->getHost(),
        ], 404);
    }
    
    return response()->json([
        'status' => 'success',
        'message' => 'Tenant middleware test',
        'is_tenant_route' => true,
        'tenant' => [
            'id' => tenant()->id,
            'name' => tenant()->name ?? 'Unnamed Tenant',
        ],
        'host' => request()->getHost(),
    ]);
});

// Tenant API Routes (consider adding auth middleware)
Route::prefix('api')->middleware('api')->group(function () { // Assuming tenant API uses stateless guards
    Route::apiResource('mailboxes', MailboxConfigurationController::class);
    // TODO: Add route for testing connection
    // Route::post('mailboxes/{mailboxConfiguration}/test', [MailboxConfigurationController::class, 'testConnection'])->name('mailboxes.test');
}); 