<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $store = $user->getEffectiveStore();

        if (!$store) {
            return redirect()->route('store-owner.stores.create');
        }

        // Check if store has the required feature
        if (!$store->hasFeature($feature)) {
            $message = "Your current plan does not include the '{$feature}' feature. Please upgrade your plan to access this.";
            
            if ($request->expectsJson()) {
                abort(403, $message);
            }

            return redirect()->route('store-owner.dashboard')
                ->with('error', $message)
                ->with('show_upgrade_modal', true); // Optional: Trigger an upgrade modal on frontend
        }

        return $next($request);
    }
}
