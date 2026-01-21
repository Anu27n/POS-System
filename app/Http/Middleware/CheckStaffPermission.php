<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStaffPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Store owners have all permissions for their store
        if ($user->isStoreOwner()) {
            return $next($request);
        }

        // Check if user is staff
        if (!$user->isStaff()) {
            abort(403, 'You do not have permission to access this resource.');
        }

        // Check if staff has any of the required permissions
        if (empty($permissions)) {
            return $next($request);
        }

        if ($user->hasAnyStaffPermission($permissions)) {
            return $next($request);
        }

        abort(403, 'You do not have the required permissions.');
    }
}
