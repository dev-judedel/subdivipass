<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\LoginActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    protected LoginActivityService $loginActivityService;

    public function __construct(LoginActivityService $loginActivityService)
    {
        $this->loginActivityService = $loginActivityService;
    }

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
        $request->authenticate($this->loginActivityService);

        $request->session()->regenerate();

        $user = Auth::user();

        // Update last login information
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Log successful login activity
        $this->loginActivityService->logActivity(
            $user,
            $request->email,
            'success',
            $request
        );

        // Check for suspicious login (new device/location)
        if ($this->loginActivityService->isNewDevice($user, $request->userAgent())) {
            session()->flash('info', 'Login from a new device detected. If this wasn\'t you, please change your password.');
        }

        // Determine redirect based on user role
        // Use route names for better reliability
        if ($user->hasRole('guard')) {
            return redirect()->route('guard.scanner');
        } elseif ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return redirect()->route('dashboard');
        } elseif ($user->hasRole('employee')) {
            return redirect()->route('passes.index');
        } elseif ($user->hasRole('requester')) {
            return redirect()->route('requester.passes');
        }

        // Default fallback for users without specific roles
        return redirect()->route('dashboard');
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
