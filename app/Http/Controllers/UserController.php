<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Storage;

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
        
        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        if (in_array($sortField, ['name', 'email', 'created_at', 'last_login_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }
        
        $users = $query->paginate(25);
        $roles = Role::active()->get();
        
        AuditLog::logActivity('view', new User(), null, null, 'Viewed user listing');
        
        return view('users.index', compact('users', 'roles'));
    }
    
    public function show(User $user)
    {
        $user->load('role', 'notes', 'auditLogs');
        
        // Get user statistics
        $stats = [
            'total_notes' => $user->notes()->count(),
            'recent_notes' => $user->notes()->where('created_at', '>=', now()->subDays(30))->count(),
            'last_activity' => $user->auditLogs()->latest()->first(),
        ];
        
        AuditLog::logActivity('view', $user, null, null, "Viewed user profile: {$user->name}");
        
        return view('users.show', compact('user', 'stats'));
    }
    
    public function create()
    {
        $roles = Role::active()->get();
        
        return view('users.create', compact('roles'));
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
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);
        
        $userData = $request->all();
        $userData['password'] = Hash::make($request->password);
        
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo_path'] = $path;
        }
        
        $user = User::create($userData);
        
        AuditLog::logActivity('create', $user, null, $user->toArray(), "Created user: {$user->name}");
        
        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }
    
    public function edit(User $user)
    {
        $roles = Role::active()->get();
        
        return view('users.edit', compact('user', 'roles'));
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
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);
        
        $oldData = $user->toArray();
        $userData = $request->except('password', 'password_confirmation');
        
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo_path'] = $path;
        }
        
        $user->update($userData);
        
        AuditLog::logActivity('update', $user, $oldData, $user->toArray(), "Updated user: {$user->name}");
        
        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }
    
    public function destroy(User $user)
    {
        // Prevent deletion of the last admin
        if ($user->isAdmin()) {
            $adminCount = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('is_active', true)->count();
            
            if ($adminCount <= 1) {
                return redirect()->route('users.index')
                    ->with('error', 'Cannot delete the last active administrator.');
            }
        }
        
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }
        
        $oldData = $user->toArray();
        
        // Delete profile photo if exists
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }
        
        $user->delete();
        
        AuditLog::logActivity('delete', $user, $oldData, null, "Deleted user: {$user->name}");
        
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
    
    public function toggleStatus(User $user)
    {
        // Prevent deactivation of the last admin
        if ($user->isAdmin() && $user->is_active) {
            $activeAdminCount = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('is_active', true)->count();
            
            if ($activeAdminCount <= 1) {
                return redirect()->route('users.index')
                    ->with('error', 'Cannot deactivate the last active administrator.');
            }
        }
        
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }
        
        $oldData = $user->toArray();
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        AuditLog::logActivity('update', $user, $oldData, $user->toArray(), "User {$status}: {$user->name}");
        
        return redirect()->route('users.index')
            ->with('success', "User {$status} successfully.");
    }
}