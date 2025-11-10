<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Update last login information
        Auth::user()->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Determine redirect based on user role
        $user = Auth::user();

        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return redirect()->intended('/dashboard');
        } elseif ($user->hasRole('guard')) {
            return redirect()->intended('/guard/scanner');
        } elseif ($user->hasRole('employee')) {
            return redirect()->intended('/passes');
        } elseif ($user->hasRole('requester')) {
            return redirect()->intended('/my-passes');
        }

        return redirect()->intended('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get the authenticated user with roles and permissions.
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles', 'permissions'),
        ]);
    }

    /**
     * Check if user is authenticated.
     */
    public function check(Request $request)
    {
        return response()->json([
            'authenticated' => Auth::check(),
        ]);
    }
}
