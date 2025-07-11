<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Pagination
        $perPage = $request->get('per_page', 25);
        $users = $query->paginate($perPage);
        
        AuditLog::logActivity('view', new User(), null, null, 'Viewed user listing via API');
        
        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 200);
    }
    
    public function show(User $user)
    {
        $user->load('role', 'notes', 'auditLogs');
        
        AuditLog::logActivity('view', $user, null, null, "Viewed user profile via API: {$user->name}");
        
        return response()->json([
            'user' => $user,
            'statistics' => [
                'total_notes' => $user->notes()->count(),
                'recent_notes' => $user->notes()->where('created_at', '>=', now()->subDays(30))->count(),
                'last_activity' => $user->auditLogs()->latest()->first(),
            ],
        ], 200);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);
        
        $userData = $request->all();
        $userData['password'] = Hash::make($request->password);
        
        $user = User::create($userData);
        
        AuditLog::logActivity('create', $user, null, $user->toArray(), "Created user via API: {$user->name}");
        
        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user->load('role'),
        ], 201);
    }
    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);
        
        $oldData = $user->toArray();
        $userData = $request->except('password', 'password_confirmation');
        
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        
        $user->update($userData);
        
        AuditLog::logActivity('update', $user, $oldData, $user->toArray(), "Updated user via API: {$user->name}");
        
        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->load('role'),
        ], 200);
    }
    
    public function destroy(User $user)
    {
        // Prevent deletion of the last admin
        if ($user->isAdmin()) {
            $adminCount = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('is_active', true)->count();
            
            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'Cannot delete the last active administrator.',
                ], 403);
            }
        }
        
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }
        
        $oldData = $user->toArray();
        $user->delete();
        
        AuditLog::logActivity('delete', $user, $oldData, null, "Deleted user via API: {$user->name}");
        
        return response()->json([
            'message' => 'User deleted successfully.',
        ], 200);
    }
}