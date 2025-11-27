<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    /**
     * Display the password reset request form.
     */
    public function showForgotPasswordForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Send a password reset link to the user's email.
     */
    public function sendResetLink(ForgotPasswordRequest $request)
    {
        // Check if user exists and is active
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->status !== 'active') {
            return back()->with('error', 'We could not find an active account with that email address.');
        }

        // Send password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Password reset link sent! Check your email.')
            : back()->with('error', 'Unable to send password reset link. Please try again.');
    }

    /**
     * Display the password reset form.
     */
    public function showResetPasswordForm(string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Your password has been reset! You can now login.')
            : back()->with('error', 'Unable to reset password. The reset link may have expired.');
    }
}
