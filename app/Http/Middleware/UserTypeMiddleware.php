<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // If it's an API request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            // For web requests, redirect to login
            return redirect()->route('auth.login');
        }

        // Check if user type is allowed
        if (!in_array(Auth::user()->user_type, $types)) {
            // If it's an API request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Access denied.'
                ], 403);
            }
            // For web requests, show 403 error page
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}