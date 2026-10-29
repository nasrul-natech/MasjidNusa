<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TwoFactorService;
use App\Support\RememberLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactor
    ) {}

    public function showChallenge(Request $request)
    {
        $user = $this->resolvePendingUser($request);
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        return view('auth.two-factor-challenge', [
            'redirect' => $request->session()->get('login.redirect', '/'),
        ]);
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $this->resolvePendingUser($request);
        if (!$user) {
            return redirect()->route('login');
        }

        $throttleKey = 'two-factor:' . $user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($throttleKey) . ' detik.',
            ]);
        }
        $code = str_replace([' ', '-'], '', $request->input('code'));

        $valid = strlen($code) === 6
            ? $this->twoFactor->verifyCode($user, $code)
            : $this->twoFactor->verifyRecoveryCode($user, strtoupper($request->input('code')));

        if (!$valid) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi tidak valid.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        return $this->completeLogin($request, $user);
    }

    public function showSetup(Request $request)
    {
        $user = $this->resolvePendingUser($request);
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        $secret = $request->session()->get('two_factor.setup_secret');
        if (!$secret) {
            $secret = $this->twoFactor->generateSecret();
            $request->session()->put('two_factor.setup_secret', $secret);
        }

        return view('auth.two-factor-setup', [
            'user' => $user,
            'secret' => $secret,
            'qrCode' => $this->twoFactor->getQrCodeSvg($user, $secret),
            'redirect' => $request->session()->get('login.redirect', '/'),
        ]);
    }

    public function confirmSetup(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $this->resolvePendingUser($request);
        if (!$user) {
            return redirect()->route('login');
        }

        $secret = $request->session()->get('two_factor.setup_secret');

        if (!$secret) {
            return redirect()->route('two-factor.setup');
        }

        if (!$this->twoFactor->enable($user, $secret, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi tidak valid. Pastikan waktu perangkat sudah benar.',
            ]);
        }

        $request->session()->forget('two_factor.setup_secret');
        AuditLog::record('user.two_factor_enabled', $user);

        $recoveryCodes = $this->twoFactor->getRecoveryCodes($user);

        $request->session()->flash('recovery_codes', $recoveryCodes->all());

        return $this->completeLogin($request, $user);
    }

    protected function resolvePendingUser(Request $request): ?User
    {
        $userId = $request->session()->get('login.id');

        if ($userId) {
            return User::find($userId);
        }

        $user = $request->user();
        if ($user && $user->requiresTwoFactor() && !$request->session()->get('two_factor_verified')) {
            return $user;
        }

        return null;
    }

    protected function completeLogin(Request $request, User $user)
    {
        if (!$user->is_active) {
            $request->session()->forget(['login.id', 'login.remember', 'login.redirect', 'two_factor.setup_secret']);

            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        $remember = (bool) $request->session()->get('login.remember', false)
            || $request->session()->has('remember_until');
        $redirect = $this->safeInternalPath($request->session()->get('login.redirect', '/'));

        RememberLogin::start($remember);
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->put('two_factor_verified', true);
        RememberLogin::persist($request, $remember);
        if (!$remember) {
            RememberLogin::forget();
        }

        if ($user->requiresTwoFactor()) {
            $user->forceFill([
                'two_factor_remembered_at' => $remember ? now() : null,
            ])->save();
        }

        $request->session()->forget(['login.id', 'login.remember', 'login.redirect']);

        AuditLog::record('user.login');

        if (!$user->isJamaah() && $redirect === '/') {
            $redirect = $user->getAdminPanelUrl();
        }

        return redirect($redirect)->with('success', 'Selamat datang, ' . $user->name . '!');
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
