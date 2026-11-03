<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated. Please log in to access the administrator portal.',
                ], 401);
            }

            return redirect()->guest(route('admin.login'))
                ->with('error', 'Please log in to access the admin portal.');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Your administrator account has been deactivated. Please contact the portal administrator.',
                ], 403);
            }

            return redirect()->route('admin.login')
                ->with('error', 'Your administrator account has been deactivated. Please contact the portal administrator.');
        }

        return $next($request);
    }
}
