<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirect based on user role (guard first for priority)
                if ($user?->hasRole('guard')) {
                    return redirect()->route('guard.scanner');
                } elseif ($user?->hasRole('super-admin') || $user?->hasRole('admin')) {
                    return redirect()->route('dashboard');
                } elseif ($user?->hasRole('employee')) {
                    return redirect()->route('passes.index');
                } elseif ($user?->hasRole('requester')) {
                    return redirect()->route('requester.passes');
                }

                // Default fallback
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
