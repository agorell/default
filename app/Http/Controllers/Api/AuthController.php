<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('API Token')->plainTextToken;

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Log the login
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'login',
                'description' => 'User logged in via API',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Login successful',
                'user' => $user->load('role'),
                'token' => $token,
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    public function logout(Request $request)
    {
        // Log the logout
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'logout',
            'description' => 'User logged out via API',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('role'),
        ]);
    }
}