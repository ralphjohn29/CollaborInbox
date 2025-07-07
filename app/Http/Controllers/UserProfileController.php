<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    /**
     * Get authenticated user's profile
     */
    public function getProfile()
    {
        $user = Auth::user()->load('role.permissions');
        
        return response()->json([
            'user' => $user,
            'has_profile_image' => Storage::disk('tenant')->exists("users/{$user->id}/profile.jpg")
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $user->update($request->only(['name', 'email']));
            
            // Log profile update
            Log::info('User profile updated', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
            ]);
            
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->refresh()->load('role.permissions')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }
        
        try {
            $user->password = Hash::make($request->new_password);
            $user->save();
            
            // Log password change
            Log::info('User password changed', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
            ]);
            
            return response()->json([
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to change user password', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|max:2048', // 2MB max
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $path = Storage::disk('tenant')->putFileAs(
                "users/{$user->id}",
                $request->file('profile_picture'),
                'profile.jpg'
            );
            
            // Log profile picture upload
            Log::info('User profile picture uploaded', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'path' => $path
            ]);
            
            return response()->json([
                'message' => 'Profile picture uploaded successfully',
                'path' => $path
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload profile picture', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Failed to upload profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get profile picture
     */
    public function getProfilePicture($userId = null)
    {
        $targetUserId = $userId ?? Auth::id();
        
        // Check if requesting another user's picture
        if ($targetUserId != Auth::id()) {
            // Verify user has permission to view other users
            if (!Auth::user()->hasPermission('view agents')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            // Check if user exists and belongs to same tenant
            $targetUser = User::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $targetUserId)
                ->first();
                
            if (!$targetUser) {
                return response()->json(['message' => 'User not found'], 404);
            }
        }
        
        $path = "users/{$targetUserId}/profile.jpg";
        
        if (!Storage::disk('tenant')->exists($path)) {
            return response()->json(['message' => 'No profile picture found'], 404);
        }
        
        $file = Storage::disk('tenant')->get($path);
        $type = Storage::disk('tenant')->mimeType($path);
        
        return response($file, 200)->header('Content-Type', $type);
    }
    
    /**
     * Delete profile picture
     */
    public function deleteProfilePicture()
    {
        $user = Auth::user();
        $path = "users/{$user->id}/profile.jpg";
        
        if (!Storage::disk('tenant')->exists($path)) {
            return response()->json(['message' => 'No profile picture found'], 404);
        }
        
        try {
            Storage::disk('tenant')->delete($path);
            
            // Log profile picture deletion
            Log::info('User profile picture deleted', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id
            ]);
            
            return response()->json([
                'message' => 'Profile picture deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete profile picture', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Failed to delete profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 