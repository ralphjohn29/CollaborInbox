<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
// use App\Jobs\SetupPostmarkInbound;
// use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Schema;

class SignupController extends Controller
{
    /**
     * Show the signup form
     */
    public function show()
    {
        return view('auth.signup');
    }

    /**
     * Handle signup form submission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'company_name' => ['required', 'string', 'max:255'],
            'accept_terms' => ['required', 'accepted'],
        ]);

        // For now, create user without workspace since tables might not be migrated
        $user = DB::transaction(function () use ($validated, $request) {
            // Check if workspaces table exists
            if (Schema::hasTable('workspaces')) {
                // Create workspace
                $workspace = Workspace::create([
                    'name' => $validated['company_name'],
                    'settings' => [
                        'onboarding_completed' => false,
                        'timezone' => $request->input('timezone', 'UTC'),
                    ],
                ]);

                // Create admin user
                $user = $workspace->users()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'is_workspace_creator' => true,
                ]);
            } else {
                // Create user without workspace for now
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);
            }

            return $user;
        });

        // Auto-login the user
        Auth::login($user, true);

        // Redirect to dashboard
        return redirect('/dashboard');
    }

    /**
     * Handle OAuth signup (Google/Microsoft)
     */
    public function oauth(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        if ($user->workspace_id) {
            // User already has a workspace
            return redirect('/dashboard');
        }

        $workspace = DB::transaction(function () use ($user, $validated, $request) {
            // Create workspace
            $workspace = Workspace::create([
                'name' => $validated['company_name'],
                'settings' => [
                    'onboarding_completed' => false,
                    'timezone' => $request->input('timezone', 'UTC'),
                ],
            ]);

            // Update user with workspace
            $user->update([
                'workspace_id' => $workspace->id,
                'is_workspace_creator' => true,
            ]);

            // Assign admin role
            // $user->assignRole('workspace-admin');

            // Setup Postmark inbound email
            // SetupPostmarkInbound::dispatch($workspace);

            // Send welcome email
            // $user->notify(new WelcomeNotification($workspace));

            return $workspace;
        });

        return redirect('/dashboard');
    }
}
