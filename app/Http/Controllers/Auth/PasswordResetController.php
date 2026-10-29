<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $emailKey = 'password-reset:' . strtolower($request->email);
        $user = RateLimiter::tooManyAttempts($emailKey, 3)
            ? null
            : User::where('email', $request->email)->first();

        if ($user) {
            RateLimiter::hit($emailKey, 15 * 60);
            // Generate token via Laravel's Password broker (stored in password_reset_tokens table)
            $token = Password::broker()->createToken($user);

            // Build reset URL using route() — respects APP_URL and forceRootUrl correctly
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            // Send via our NotificationService (uses DB SMTP + DB email template)
            app(NotificationService::class)->sendEmail($user->email, 'password_reset', [
                'name'      => $user->name,
                'reset_url' => $resetUrl,
            ]);

            AuditLog::record('user.password_reset_requested', $user);
        }

        // Always return the same message to prevent email enumeration
        return back()->with('success', 'Jika email terdaftar, link reset password akan dikirim ke email Anda.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->revokeAccess();

                event(new PasswordReset($user));
                AuditLog::record('user.password_reset', $user);

                // Send confirmation email via our NotificationService
                try {
                    app(NotificationService::class)->sendEmail($user->email, 'password_changed', [
                        'name' => $user->name,
                        'date' => now()->format('d M Y H:i'),
                    ]);
                } catch (\Exception $e) {
                    // Don't block the reset if email fails
                }
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login.')
            : back()->withErrors(['email' => __($status)]);
    }
}
