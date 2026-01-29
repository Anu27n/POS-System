<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugSession
{
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('Request received', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'session_id' => $request->session()->getId(),
            'has_session' => $request->hasSession(),
            'auth_check' => auth()->check(),
            'auth_id' => auth()->id(),
            'cookies' => array_keys($request->cookies->all()),
        ]);

        $response = $next($request);

        \Log::info('Response sent', [
            'status' => $response->getStatusCode(),
            'session_id' => $request->session()->getId(),
            'auth_check_after' => auth()->check(),
        ]);

        return $response;
    }
}
