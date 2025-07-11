<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        // Only allow registration if no users exist (initial setup)
        if (User::count() > 0) {
            return redirect()->route('login')->with('error', 'User registration is disabled.');
        }
        
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // Only allow registration if no users exist (initial setup)
        if (User::count() > 0) {
            return redirect()->route('login')->with('error', 'User registration is disabled.');
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

        AuditLog::logActivity('create', $user, null, $user->toArray(), "Initial admin user created: {$user->name}");

        return redirect()->route('login')->with('success', 'Admin account created successfully. Please log in.');
    }
}