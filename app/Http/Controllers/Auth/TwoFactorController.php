<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TwoFactorService;
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
        if (!$request->session()->has('login.id')) {
            return redirect()->route('login');
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

        $userId = $request->session()->get('login.id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $throttleKey = 'two-factor:' . $userId;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($throttleKey) . ' detik.',
            ]);
        }

        $user = User::findOrFail($userId);
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

    protected function resolvePendingUser(Request $request): User
    {
        $userId = $request->session()->get('login.id');

        if (!$userId) {
            return redirect()->route('login');
        }

        return User::findOrFail($userId);
    }

    protected function completeLogin(Request $request, User $user)
    {
        $remember = $request->session()->get('login.remember', false);
        $redirect = $request->session()->get('login.redirect', '/');

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->put('two_factor_verified', true);

        $request->session()->forget(['login.id', 'login.remember', 'login.redirect']);

        if ($remember) {
            config(['session.lifetime' => 30 * 24 * 60]);
            $request->session()->put('remember_until', now()->addDays(30)->timestamp);
        }

        AuditLog::record('user.login');

        if (!$user->isJamaah() && $redirect === '/') {
            $redirect = $user->getAdminPanelUrl();
        }

        return redirect($redirect)->with('success', 'Selamat datang, ' . $user->name . '!');
    }
}
