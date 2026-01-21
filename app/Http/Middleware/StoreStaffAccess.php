<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreStaffAccess
{
    /**
     * Handle an incoming request.
     * Allow access for store owners and their staff.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Store owners have access
        if ($user->isStoreOwner() && $user->store) {
            return $next($request);
        }

        // Staff have access
        if ($user->isStaff() && $user->worksAtStore) {
            return $next($request);
        }

        abort(403, 'You do not have access to this store panel.');
    }
}
