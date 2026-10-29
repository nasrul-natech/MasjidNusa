<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\GoogleAuth;
use App\Support\RememberLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showForm(Request $request)
    {
        return view('auth.login', [
            'redirect' => $request->query('redirect', '/'),
            'demoAccounts' => app()->environment('local') ? config('demo.accounts', []) : [],
            'googleEnabled' => GoogleAuth::isConfigured(),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = 'login:' . strtolower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $remember = $request->boolean('remember');
        $redirect = $this->safeInternalPath($request->input('redirect', '/'));

        if (!Auth::validate($credentials)) {
            RateLimiter::hit($throttleKey, 60);

            $user = User::where('email', $credentials['email'])->first();
            if ($user && $user->google_id) {
                throw ValidationException::withMessages([
                    'email' => 'Akun ini terdaftar via Google. Silakan gunakan tombol "Masuk dengan Google".',
                ]);
            }

            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        /** @var User $user */
        $user = User::where('email', $credentials['email'])->first();

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ]);
        }

        if ($user->requiresTwoFactor()) {
            $request->session()->put([
                'login.id' => $user->id,
                'login.remember' => $remember,
                'login.redirect' => $redirect,
            ]);

            if (!$user->hasTwoFactorEnabled()) {
                return redirect()->route('two-factor.setup')
                    ->with('info', 'Admin wajib mengaktifkan 2FA sebelum masuk ke sistem.');
            }

            return redirect()->route('two-factor.challenge');
        }

        RememberLogin::start($remember);
        Auth::login($user, $remember);
        $request->session()->regenerate();
        RememberLogin::persist($request, $remember);
        if (!$remember) {
            RememberLogin::forget();
        }

        AuditLog::record('user.login');

        if (!$user->isJamaah() && $redirect === '/') {
            return redirect($user->getAdminPanelUrl());
        }

        return redirect($redirect)->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        AuditLog::record('user.logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/?logout=1')->with('success', 'Berhasil logout.');
    }

    private function safeInternalPath(?string $redirect): string
    {
        $redirect = $redirect ?: '/';

        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//') || str_contains($redirect, '\\') || str_contains($redirect, ':')) {
            return '/';
        }

        return $redirect;
    }
}
