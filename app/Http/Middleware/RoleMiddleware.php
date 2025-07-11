<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated.');
        }

        // Check if user has any of the required roles
        $userRole = $user->role->name ?? '';
        
        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized. You do not have permission to access this page.');
        }

        return $next($request);
    }
}