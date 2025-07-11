<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.']
                ]
            ], 401);
        }

        $user = Auth::user();
        
        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'message' => 'Account deactivated.',
                'errors' => [
                    'email' => ['Your account has been deactivated.']
                ]
            ], 401);
        }

        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Log the login activity
        AuditLog::logLogin($user);

        // Create API token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
                'permissions' => $user->role->permissions->pluck('name'),
                'last_login_at' => $user->last_login_at,
            ],
            'token' => $token,
        ], 200);
    }

    public function register(Request $request)
    {
        // Only allow registration if no users exist (initial setup)
        if (User::count() > 0) {
            return response()->json([
                'message' => 'Registration is disabled.',
                'errors' => [
                    'email' => ['User registration is not allowed.']
                ]
            ], 403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate([
            'name' => 'admin'
        ], [
            'display_name' => 'Administrator',
            'description' => 'Full system access with all permissions',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $adminRole->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        AuditLog::logActivity('create', $user, null, $user->toArray(), "Initial admin user created via API: {$user->name}");

        return response()->json([
            'message' => 'Admin account created successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Log the logout activity
        AuditLog::logLogout($user);

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('role.permissions');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role->name,
                'role_display_name' => $user->role->display_name,
                'permissions' => $user->role->permissions->pluck('name'),
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
            ],
        ], 200);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('api-token')->plainTextToken;

        AuditLog::logActivity('update', $user, null, null, "API token refreshed");

        return response()->json([
            'message' => 'Token refreshed successfully.',
            'token' => $token,
        ], 200);
    }
}