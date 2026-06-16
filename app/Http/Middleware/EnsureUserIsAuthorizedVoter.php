<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class EnsureUserIsAuthorizedVoter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {




        $transactionId = (string) Str::uuid();


        // make available on the Request object
        $request->attributes->set('transaction_id', $transactionId);

        Log::info("EnsureUserIsAuthorizedVoter invoked");






        if (!Auth::check()) {
            Log::info("User not authenticated, redirecting to user login");
            return redirect()->route('user.login');
        }
        $user = Auth::user();

        if (!in_array($user->role, ['stockholder', 'corp-rep', 'non-member'])) {

            Log::warning("User with ID {$user->id} attempted to access voting options route. User role: {$user->role}");
            if ($request->expectsJson()) {
                Log::warning("Request expects JSON, returning 403 Forbidden");
                return response()->json(['message' => 'Forbidden'], 403);
            }
            Log::info("Redirecting user with ID {$user->id} to home");
            return redirect()->route('home');
        }


        Log::info("EnsureUserIsAuthorizedVoter passed, user authorized");

        return $next($request);
    }
}
