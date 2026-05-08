<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;


class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a per-request transaction id for tracing across logs and responses
        $transactionId = (string) Str::uuid();


        // make available on the Request object
        $request->attributes->set('transaction_id', $transactionId);

        // Log::info("EnsureUserIsAdmin invoked");





        if (!Auth::check()) {
            Log::info("User not authenticated, redirecting to admin login");
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'superadmin'])) {

            Log::warning("User with ID {$user->id} attempted to access admin route. User role: {$user->role}");
            if ($request->expectsJson()) {
                Log::warning("Request expects JSON, returning 403 Forbidden");
                return response()->json(['message' => 'Forbidden'], 403);
            }
            Log::info("Redirecting user with ID {$user->id} to home");
            return redirect()->route('home');
        }

        if (Auth::user()->adminAccount->isActive === false) {
            Log::warning("Inactive admin account with ID {$user->id} attempted to access admin route. User role: {$user->role}");

            if ($request->expectsJson()) {
                Log::warning("Request expects JSON, returning 403 Forbidden");
                return response()->json(['message' => 'Forbidden'], 403);
            }
            Log::info("Redirecting user with ID {$user->id} to homepage");
            return redirect()->route('home')->withErrors(['Your admin account is inactive. Please contact support.']);
        }

        // Attach transaction id to response headers for client-side tracing
        $response = $next($request);
        $response->headers->set('X-Transaction-Id', $transactionId);


        Log::info("EnsureUserIsAdmin passed, user authorized");

        return $response;
    }
}
