<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Services\TenantResolver;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Services\TenantManager;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebSocketController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AttachmentController;
// use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Auth\SignupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Define central.home route first to prevent "Route not defined" errors
Route::get('/central', function () {
    return redirect('/');
})->name('central.home');

// Default welcome page route
Route::get('/', function (Request $request) {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    
    return redirect('/login');
});

// Explicit welcome page route
Route::get('/welcome', function () {
    return view('welcome');
});

// Simple test route to check if routing is working
Route::get('/hello', [WelcomeController::class, 'hello']);

// Test direct route without controller
Route::get('/test', function() {
    return 'Test route is working!';
});

// Debug route to test subdomain extraction
Route::get('/debug/subdomain', function (Request $request, TenantResolver $resolver) {
    $subdomain = $resolver->extractSubdomain($request);
    $tenant = $subdomain ? $resolver->findTenant($subdomain) : null;
    
    return response()->json([
        'host' => $request->getHost(),
        'extracted_subdomain' => $subdomain,
        'tenant_found' => !is_null($tenant),
        'tenant_details' => $tenant ? [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'domains' => $tenant->domains->map(function($domain) {
                return ['domain' => $domain->domain];
            }),
        ] : null,
    ]);
});

// Test route to check tenant identification
Route::get('/check-tenant', function (TenantResolver $resolver) {
    $request = request();
    $subdomain = $resolver->extractSubdomain($request);
    $tenant = $subdomain ? $resolver->findTenant($subdomain) : null;
    
    return response()->json([
        'host' => $request->getHost(),
        'subdomain' => $subdomain,
        'tenant' => $tenant ? [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'domain' => $tenant->domain,
        ] : null,
        'wildcard_domain' => config('tenancy.domain.wildcard_domain'),
    ]);
});

// List all registered tenants (for testing purposes)
Route::get('/tenants/list', function () {
    $tenants = \App\Models\Tenant::all();
    
    return response()->json([
        'count' => $tenants->count(),
        'tenants' => $tenants->map(function ($tenant) {
            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                'created_at' => $tenant->created_at->format('Y-m-d H:i:s'),
            ];
        }),
    ]);
});

// Test route to verify tenant middleware functionality
Route::get('/test-tenant-middleware', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Tenant middleware test',
        'is_tenant_route' => false,
        'host' => request()->getHost(),
        'app_url' => config('app.url'),
    ]);
});

// Route to provide connection information
Route::get('/connection-info', function() {
    return response()->json([
        'host' => request()->getHost(),
        'app_url' => config('app.url'),
        'database' => [
            'name' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
        ],
        'tenancy' => [
            'wildcard_domain' => config('tenancy.domain.wildcard_domain', '*.collaborinbox.test')
        ],
        'http_host' => $_SERVER['HTTP_HOST'] ?? 'not available',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'not available'
    ]);
});

// Route to check database connection
Route::get('/debug/database', function () {
    try {
        // Test the database connection
        $connection = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Database connection successful',
            'connection_details' => [
                'database_name' => $dbName,
                'driver' => DB::connection()->getDriverName(),
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed',
            'error' => $e->getMessage(),
            'config' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
                'database' => config('database.connections.mysql.database'),
                'username' => config('database.connections.mysql.username'),
                // Not showing password for security reasons
            ],
            'troubleshooting_steps' => [
                'Ensure MySQL server is running (check in XAMPP/WAMP/MAMP control panel)',
                'Verify the database "collaborinbox" exists',
                'Check if MySQL service is listening on port 3306',
                'Make sure there are no firewalls blocking the connection',
                'Try using "localhost" instead of "127.0.0.1" in the DB_HOST setting',
            ]
        ], 500);
    }
});

// Detailed debug route to diagnose tenant identification issues
Route::get('/debug/tenant-identification', function (App\Services\TenantResolver $resolver) {
    $request = request();
    $host = $request->getHost();
    $wildcardDomain = config('tenancy.domain.wildcard_domain', '*.collaborinbox.test');
    $baseHost = \Illuminate\Support\Str::after($wildcardDomain, '*.');
    $subdomain = $resolver->extractSubdomain($request);
    
    // Try to find tenant
    $tenant = null;
    $tenantQueryResult = null;
    
    if ($subdomain) {
        $domain = $subdomain . '.' . $baseHost;
        
        // Get query result for debugging
        $tenantQueryResult = \App\Models\Tenant::query()
            ->whereHas('domains', function ($query) use ($domain) {
                $query->where('domain', $domain);
            })
            ->get()
            ->toArray();
            
        $tenant = $resolver->findTenant($subdomain);
    }
    
    return response()->json([
        'diagnostics' => [
            'host' => $host,
            'wildcard_domain' => $wildcardDomain,
            'base_host' => $baseHost,
            'extracted_subdomain' => $subdomain,
            'constructed_domain' => $subdomain ? $subdomain . '.' . $baseHost : null,
            'tenant_found' => !is_null($tenant),
            'tenant_query_results' => $tenantQueryResult,
            'env_app_url' => config('app.url'),
            'request_url' => $request->url(),
            'request_scheme' => $request->getScheme(),
            'middleware_groups' => array_keys(app('Illuminate\Contracts\Http\Kernel')->getMiddlewareGroups()),
        ]
    ]);
});

// Route to create a test tenant (for development only)
Route::get('/create-test-tenant', function () {
    try {
        // Check if the test tenant already exists
        $existingTenant = DB::table('tenants')
            ->where('name', 'Test Tenant')
            ->first();
            
        if ($existingTenant) {
            // Create a test user for this tenant if it doesn't exist
            $existingUser = DB::table('users')
                ->where('email', 'test@example.com')
                ->first();
                
            if (!$existingUser) {
                // Find or create admin role
                $role = DB::table('roles')->where('name', 'admin')->first();
                if (!$role) {
                    $roleId = DB::table('roles')->insertGetId([
                        'name' => 'admin',
                        'guard_name' => 'web',
                        'description' => 'Administrator Role',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $roleId = $role->id;
                }
                
                // Create user
                DB::table('users')->insert([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password'),
                    'tenant_id' => $existingTenant->id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            return response()->json([
                'message' => 'Test tenant already exists',
                'tenant' => [
                    'id' => $existingTenant->id,
                    'name' => $existingTenant->name,
                ],
                'login_details' => [
                    'email' => 'test@example.com',
                    'password' => 'password'
                ],
                'next_steps' => [
                    'Add an entry to your hosts file: 127.0.0.1 test.collaborinbox.test',
                    'Access the tenant at http://test.collaborinbox.test:8000/login',
                    'Login with email: test@example.com and password: password'
                ]
            ]);
        }
        
        // Create the tenant with domain specified directly
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Test Tenant',
            'domain' => 'test.collaborinbox.test', // Explicitly set domain to fix error
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create a domain entry directly
        DB::table('domains')->insert([
            'domain' => 'test.collaborinbox.test',
            'tenant_id' => $tenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Find or create admin role
        $role = DB::table('roles')->where('name', 'admin')->first();
        if (!$role) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'admin',
                'guard_name' => 'web',
                'description' => 'Administrator Role',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $roleId = $role->id;
        }
        
        // Create test user
        DB::table('users')->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenantId,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'message' => 'Test tenant and user created successfully',
            'tenant' => [
                'id' => $tenantId,
                'name' => 'Test Tenant',
                'domain' => 'test.collaborinbox.test',
            ],
            'login_details' => [
                'email' => 'test@example.com',
                'password' => 'password'
            ],
            'next_steps' => [
                'Add an entry to your hosts file: 127.0.0.1 test.collaborinbox.test',
                'Access the tenant at http://test.collaborinbox.test:8000/login',
                'Login with email: test@example.com and password: password'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create tenant',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'troubleshooting' => [
                'Make sure the database is connected properly',
                'Check if the "tenants" and "domains" tables exist in your database',
                'Run migrations if needed: php artisan migrate',
                'Run tenant migrations: php artisan tenants:migrate'
            ]
        ], 500);
    }
});

// Temporary route to create a test user for the tenant (REMOVE IN PRODUCTION)
Route::get('/create-test-user', function () {
    if (!app('App\Services\TenantManager')->getCurrentTenant()) {
        return response()->json([
            'error' => 'This endpoint must be accessed from a tenant subdomain',
            'example' => 'http://test.collaborinbox.test:8000/create-test-user'
        ], 400);
    }
    
    try {
        // Check if a test user already exists for this tenant
        $existingUser = DB::table('users')->where('email', 'test@example.com')->first();
        
        if ($existingUser) {
            return response()->json([
                'message' => 'Test user already exists',
                'user' => [
                    'email' => 'test@example.com',
                    'password' => '123456' // For testing only!
                ],
                'next_steps' => [
                    'Go to the login page: ' . url('/login'),
                    'Use the credentials above to log in'
                ]
            ]);
        }
        
        // Create a test user for the current tenant
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('123456'),
            'tenant_id' => app('App\Services\TenantManager')->getCurrentTenant()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'message' => 'Test user created successfully',
            'user' => [
                'email' => 'test@example.com',
                'password' => '123456' // For testing only!
            ],
            'next_steps' => [
                'Go to the login page: ' . url('/login'),
                'Use the credentials above to log in'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create test user',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Admin/Tenant Management Routes (Central Domain)
Route::middleware(['auth'])->group(function() {
    Route::resource('tenants', TenantController::class);
    // Route for toggling tenant status (activate/deactivate)
    Route::patch('tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');
});

// Agent Management Routes (protected by authentication and tenant middleware)
Route::get('/agents', [AgentController::class, 'index'])->middleware(['auth', 'tenancy'])->name('agents.index');

// Public Signup Routes
Route::get('/signup', [SignupController::class, 'show'])->name('signup');
Route::post('/signup', [SignupController::class, 'store']);
Route::post('/signup/oauth', [SignupController::class, 'oauth'])->name('signup.oauth');

// Authentication Routes
Route::get('/login', function (Request $request) {
    // Debug for login page access with more detail
    \Illuminate\Support\Facades\Log::debug('Login page accessed', [
        'host' => $request->getHost(),
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'user_agent' => $request->userAgent(),
        'app_url' => config('app.url'),
        'is_tenant_subdomain' => (app(TenantManager::class)->parseSubdomain($request->getHost())) ? 'yes' : 'no',
        'subdomain' => app(TenantManager::class)->parseSubdomain($request->getHost())
    ]);
    
    // If already authenticated, redirect to dashboard
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    
    // Try to auto-set tenant context from request if missing
    try {
        $tenantManager = app(TenantManager::class);
        
        // Get subdomain directly for debugging
        $subdomain = $tenantManager->parseSubdomain($request->getHost());
        
        // Log the current state
        \Illuminate\Support\Facades\Log::debug('Login page - tenant context check', [
            'host' => $request->getHost(),
            'current_tenant' => $tenantManager->getCurrentTenant() ? $tenantManager->getCurrentTenant()->name : 'none',
            'subdomain' => $subdomain
        ]);
        
        if (!$tenantManager->getCurrentTenant() && $subdomain) {
            // Direct lookup of tenant, bypassing middleware
            $tenant = $tenantManager->getTenantBySubdomain($subdomain);
            
            if ($tenant) {
                $tenantManager->setCurrentTenant($tenant);
                \Illuminate\Support\Facades\Log::debug('Set tenant from request', [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'tenant_domain' => $tenant->domain ?? 'not set'
                ]);
            } else {
                \Illuminate\Support\Facades\Log::debug('No tenant found for subdomain but continuing', [
                    'subdomain' => $subdomain,
                    'all_tenants' => \App\Models\Tenant::all()->pluck('name', 'id')
                ]);
            }
        }
    } catch (\Exception $e) {
        // Log the issue but continue - user can still log in
        \Illuminate\Support\Facades\Log::error('Error setting tenant context on login page', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    return view('auth.login');
})->name('login');

// Handle login form POST
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);
 
    $remember = $request->boolean('remember');

    if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();
 
        return redirect()->intended('/dashboard');
    }
 
    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
});

// Logout route
Route::post('/logout', function (Request $request) {
    Auth::logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect('/login');
})->name('logout');

// Emergency Logout Route
Route::get('/emergency-logout', function () {
    if (Auth::check()) {
        Auth::logout();
    }
    
    // Return the dedicated logout HTML page
    return response()->file(public_path('logout.html'));
})->name('emergency-logout');

// Dashboard - protected by authentication
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Inbox and Email Account Management Routes
Route::middleware(['auth'])->group(function () {
    // Main inbox route
    Route::get('/inbox', [\App\Http\Controllers\SimpleInboxController::class, 'index'])->name('inbox.index');
    
    // Individual email routes
    Route::get('/inbox/email/{id}', [\App\Http\Controllers\SimpleInboxController::class, 'show'])->name('inbox.email.show');
    Route::post('/inbox/email/{id}/star', [\App\Http\Controllers\SimpleInboxController::class, 'toggleStar'])->name('inbox.email.star');
    Route::post('/inbox/email/{id}/status', [\App\Http\Controllers\SimpleInboxController::class, 'updateStatus'])->name('inbox.email.status');
    Route::post('/inbox/email/{id}/assign', [\App\Http\Controllers\SimpleInboxController::class, 'assign'])->name('inbox.email.assign');
    Route::post('/inbox/email/{id}/disposition', [\App\Http\Controllers\SimpleInboxController::class, 'setDisposition'])->name('inbox.email.disposition');
    
    // Email account management
    Route::prefix('inbox/settings')->name('inbox.settings.')->group(function () {
        Route::get('/accounts', [\App\Http\Controllers\WorkspaceEmailAccountController::class, 'index'])->name('accounts');
        Route::get('/accounts/create', [\App\Http\Controllers\WorkspaceEmailAccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [\App\Http\Controllers\WorkspaceEmailAccountController::class, 'store'])->name('accounts.store');
        Route::delete('/accounts/{emailAccount}', [\App\Http\Controllers\WorkspaceEmailAccountController::class, 'destroy'])->name('accounts.destroy');
        Route::post('/accounts/{emailAccount}/toggle', [\App\Http\Controllers\WorkspaceEmailAccountController::class, 'toggle'])->name('accounts.toggle');
        Route::get('/dispositions', [\App\Http\Controllers\DispositionController::class, 'index'])->name('dispositions');
    });
    
    // Email setup route
    Route::get('/inbox/email-setup', function () {
        return view('inbox.email-setup');
    })->name('inbox.email-setup');
    
    // Email fetch route
    Route::post('/inbox/fetch-emails', [\App\Http\Controllers\EmailFetchController::class, 'fetchEmails'])->name('inbox.fetch-emails');
    
    // Outlook OAuth routes
    Route::get('/auth/outlook', [\App\Http\Controllers\OutlookAuthController::class, 'redirectToProvider'])->name('outlook.auth');
    Route::get('/auth/m365/callback', [\App\Http\Controllers\OutlookAuthController::class, 'handleProviderCallback'])->name('outlook.callback');
    Route::delete('/auth/outlook/{id}/disconnect', [\App\Http\Controllers\OutlookAuthController::class, 'disconnect'])->name('outlook.disconnect');
});

// User Management Routes - protected by authentication
Route::middleware(['auth'])->prefix('users')->group(function () {
    Route::get('/', [\App\Http\Controllers\UserManagementController::class, 'index'])->name('users.index');
    Route::get('/create', [\App\Http\Controllers\UserManagementController::class, 'create'])->name('users.create');
    Route::post('/', [\App\Http\Controllers\UserManagementController::class, 'store'])->name('users.store');
    Route::get('/{id}/edit', [\App\Http\Controllers\UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/{id}', [\App\Http\Controllers\UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/{id}', [\App\Http\Controllers\UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::get('/passwords', [\App\Http\Controllers\UserManagementController::class, 'showPasswords'])->name('users.passwords');
});

// Development-only direct dashboard access (REMOVE IN PRODUCTION)
Route::get('/direct-dashboard', function () {
    // This is a development-only route that bypasses authentication
    // It should NEVER be used in production
    
    if (!app()->environment('local')) {
        abort(404); // Only available in local environment
    }
    
    // Set a flash message
    session()->flash('warning', 'You are accessing the dashboard directly without authentication.');
    
    // Return the dashboard view
    return app(\App\Http\Controllers\DashboardController::class)->index();
})->name('direct-dashboard');

// Temporary direct route to create a test user without tenant context checks
Route::get('/create-direct-test-user', function () {
    try {
        // Check if a test user already exists
        $existingUser = \App\Models\User::where('email', 'test@example.com')->first();
        
        if ($existingUser) {
            return response()->json([
                'message' => 'Test user already exists',
                'user' => [
                    'id' => $existingUser->id,
                    'email' => 'test@example.com',
                    'password' => '123456' // For testing only!
                ],
                'next_steps' => [
                    'Go to the login page: ' . url('/login'),
                    'Use the credentials above to log in'
                ]
            ]);
        }
        
        // Get first tenant (or create one if none exists)
        $tenant = \App\Models\Tenant::first();
        if (!$tenant) {
            $tenant = \App\Models\Tenant::create([
                'name' => 'Test Tenant',
                'domain' => 'test.collaborinbox.test',
                'is_active' => true
            ]);
            
            // Ensure domain record exists
            $domain = new \Stancl\Tenancy\Database\Models\Domain();
            $domain->domain = 'test.collaborinbox.test';
            $domain->tenant_id = $tenant->id;
            $domain->save();
        }
        
        // Create role if needed
        $role = \App\Models\Role::where('name', 'admin')->first();
        if (!$role) {
            $role = \App\Models\Role::create([
                'name' => 'admin',
                'guard_name' => 'web',
                'description' => 'Administrator Role'
            ]);
        }
        
        // Create a test user with admin role
        $user = new \App\Models\User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('123456');
        $user->tenant_id = $tenant->id;
        $user->role_id = $role->id;
        $user->save();
        
        return response()->json([
            'message' => 'Test user created successfully',
            'user' => [
                'id' => $user->id,
                'email' => 'test@example.com',
                'password' => '123456' // For testing only!
            ],
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain
            ],
            'next_steps' => [
                'Go to the login page: ' . url('/login'),
                'Use the credentials above to log in'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create test user',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Tenant Management Routes - removed duplicate definitions (already defined above with resource controller)

// Disposition Dashboard Routes - protected by authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/dispositions', [\App\Http\Controllers\DispositionDashboardController::class, 'index'])->name('dispositions.dashboard');
    Route::get('/dispositions/create', [\App\Http\Controllers\DispositionDashboardController::class, 'create'])->name('dispositions.create');
    Route::post('/dispositions', [\App\Http\Controllers\DispositionDashboardController::class, 'store'])->name('dispositions.store');
    Route::get('/dispositions/{id}/edit', [\App\Http\Controllers\DispositionDashboardController::class, 'edit'])->name('dispositions.edit');
    Route::put('/dispositions/{id}', [\App\Http\Controllers\DispositionDashboardController::class, 'update'])->name('dispositions.update');
    Route::delete('/dispositions/{id}', [\App\Http\Controllers\DispositionDashboardController::class, 'destroy'])->name('dispositions.destroy');
    Route::post('/dispositions/{id}/toggle', [\App\Http\Controllers\DispositionDashboardController::class, 'toggle'])->name('dispositions.toggle');
    Route::post('/dispositions/reorder', [\App\Http\Controllers\DispositionDashboardController::class, 'reorder'])->name('dispositions.reorder');
});

// Inbox Routes - protected by authentication
Route::middleware(['auth'])->prefix('inbox')->name('inbox.')->group(function () {
    // Main inbox
    Route::get('/', [\App\Http\Controllers\SimpleInboxController::class, 'index'])->name('index');
    Route::get('/email/{id}', [\App\Http\Controllers\SimpleInboxController::class, 'show'])->name('show');
    Route::post('/email/{id}/star', [\App\Http\Controllers\SimpleInboxController::class, 'toggleStar'])->name('star');
    Route::post('/email/{id}/status', [\App\Http\Controllers\SimpleInboxController::class, 'updateStatus'])->name('status');
    Route::post('/email/{id}/assign', [\App\Http\Controllers\SimpleInboxController::class, 'assign'])->name('assign');
    Route::post('/email/{id}/disposition', [\App\Http\Controllers\SimpleInboxController::class, 'setDisposition'])->name('disposition');
    Route::post('/email/{id}/reply', [\App\Http\Controllers\SimpleInboxController::class, 'reply'])->name('reply');
    Route::get('/attachment/{id}/download', [\App\Http\Controllers\SimpleInboxController::class, 'downloadAttachment'])->name('attachment.download');
    Route::post('/bulk-action', [\App\Http\Controllers\SimpleInboxController::class, 'bulkAction'])->name('bulk-action');
    
    // Email Channel Management
    Route::prefix('channels')->name('channels.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EmailChannelController::class, 'index'])->name('index');
        Route::get('/connect', [\App\Http\Controllers\EmailChannelController::class, 'create'])->name('connect');
        Route::get('/gmail', [\App\Http\Controllers\EmailChannelController::class, 'gmailSetup'])->name('gmail');
        Route::get('/outlook', [\App\Http\Controllers\EmailChannelController::class, 'outlookSetup'])->name('outlook');
        Route::get('/other', [\App\Http\Controllers\EmailChannelController::class, 'otherSetup'])->name('other');
        Route::post('/gmail', [\App\Http\Controllers\EmailChannelController::class, 'storeGmail'])->name('gmail.store');
        Route::post('/outlook', [\App\Http\Controllers\EmailChannelController::class, 'storeOutlook'])->name('outlook.store');
        Route::post('/other', [\App\Http\Controllers\EmailChannelController::class, 'storeOther'])->name('other.store');
        Route::post('/test', [\App\Http\Controllers\EmailChannelController::class, 'testConnection'])->name('test');
        Route::delete('/{id}', [\App\Http\Controllers\EmailChannelController::class, 'destroy'])->name('destroy');
    });
    
    // Email Account Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        // Email Accounts
        Route::get('/accounts', [\App\Http\Controllers\EmailAccountController::class, 'index'])->name('accounts');
        Route::get('/accounts/create', [\App\Http\Controllers\EmailAccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [\App\Http\Controllers\EmailAccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{id}/edit', [\App\Http\Controllers\EmailAccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/accounts/{id}', [\App\Http\Controllers\EmailAccountController::class, 'update'])->name('accounts.update');
        Route::delete('/accounts/{id}', [\App\Http\Controllers\EmailAccountController::class, 'destroy'])->name('accounts.destroy');
        Route::post('/accounts/{id}/toggle', [\App\Http\Controllers\EmailAccountController::class, 'toggle'])->name('accounts.toggle');
        
        // Dispositions
        Route::get('/dispositions', [\App\Http\Controllers\DispositionController::class, 'index'])->name('dispositions');
        Route::get('/dispositions/create', [\App\Http\Controllers\DispositionController::class, 'create'])->name('dispositions.create');
        Route::post('/dispositions', [\App\Http\Controllers\DispositionController::class, 'store'])->name('dispositions.store');
        Route::get('/dispositions/{id}/edit', [\App\Http\Controllers\DispositionController::class, 'edit'])->name('dispositions.edit');
        Route::put('/dispositions/{id}', [\App\Http\Controllers\DispositionController::class, 'update'])->name('dispositions.update');
        Route::delete('/dispositions/{id}', [\App\Http\Controllers\DispositionController::class, 'destroy'])->name('dispositions.destroy');
        Route::post('/dispositions/{id}/toggle', [\App\Http\Controllers\DispositionController::class, 'toggle'])->name('dispositions.toggle');
        Route::post('/dispositions/reorder', [\App\Http\Controllers\DispositionController::class, 'reorder'])->name('dispositions.reorder');
    });
});
