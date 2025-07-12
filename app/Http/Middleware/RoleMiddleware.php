<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        // Handle multiple roles separated by comma
        $roles = explode(',', $role);
        $hasRequiredRole = false;
        
        foreach ($roles as $roleCheck) {
            if ($request->user()->hasRole(trim($roleCheck))) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole) {
            abort(403, 'Unauthorized. Required role: ' . $role);
        }

        return $next($request);
    }
}