<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth routes (with tenant middleware but not requiring authentication)
Route::middleware(['tenant.resolve'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes (requiring authentication)
Route::middleware(['auth:sanctum', 'tenant.resolve'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Agent Management Routes (protected by authentication)
Route::middleware(['auth:sanctum', 'tenancy'])->group(function () {
    // Agent CRUD operations
    Route::get('/agents', [AgentController::class, 'index']);
    Route::post('/agents', [AgentController::class, 'store']);
    Route::get('/agents/{id}', [AgentController::class, 'show']);
    Route::put('/agents/{id}', [AgentController::class, 'update']);
    Route::delete('/agents/{id}', [AgentController::class, 'destroy']);
    
    // Additional agent operations
    Route::patch('/agents/{id}/toggle-status', [AgentController::class, 'toggleStatus']);
    Route::post('/agents/bulk', [AgentController::class, 'bulk']);
    
    // Role management routes (assuming these are already defined elsewhere)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    Route::put('/roles/{id}/permissions', [RoleController::class, 'updatePermissions']);
});

// User profile routes
Route::middleware(['auth:sanctum', 'tenant.user'])->group(function () {
    // Profile management
    Route::get('/profile', [UserProfileController::class, 'getProfile']);
    Route::put('/profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/profile/change-password', [UserProfileController::class, 'changePassword']);
    
    // Profile picture management
    Route::post('/profile/picture', [UserProfileController::class, 'uploadProfilePicture']);
    Route::get('/profile/picture/{userId?}', [UserProfileController::class, 'getProfilePicture']);
    Route::delete('/profile/picture', [UserProfileController::class, 'deleteProfilePicture']);
}); 