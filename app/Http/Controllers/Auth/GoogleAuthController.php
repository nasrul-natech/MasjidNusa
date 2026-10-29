<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\RememberLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        if ($request->query('intent') === 'link') {
            $user = $request->user();
            if (!$user) {
                return redirect()->route('login');
            }
            if (!$user->isJamaah()) {
                return redirect()->route('user.profile')->with('error', 'Akun admin tidak dapat ditautkan ke Google.');
            }
            $request->session()->put('google.link', $user->id);
        } elseif ($request->user()) {
            return redirect()->route('user.profile');
        }

        $request->session()->put('google.redirect', $request->query('redirect', '/'));

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Login Google gagal. Silakan coba lagi.');
        }

        if (!$googleUser->getEmail()) {
            return redirect()->route('login')
                ->with('error', 'Akun Google tidak memiliki email. Gunakan akun Gmail lain.');
        }

        $linked = $this->linkAuthenticatedAccount($request, $googleUser);
        if ($linked) {
            return $linked;
        }

        $byGoogle = User::where('google_id', $googleUser->getId())->first();
        if ($byGoogle) {
            if (!$byGoogle->isJamaah()) {
                AuditLog::record('user.google_login_blocked', $byGoogle, [], [
                    'reason' => 'admin_account',
                ]);

                return redirect()->route('login')
                    ->with('error', 'Akun admin harus login dengan email/password dan verifikasi 2FA.');
            }

            if (!$byGoogle->is_active) {
                return redirect()->route('login')
                    ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            }

            $email = $googleUser->getEmail();
            $emailTaken = User::where('email', $email)->whereKeyNot($byGoogle->id)->exists();
            if (!$emailTaken && strcasecmp($byGoogle->email, $email) !== 0) {
                $byGoogle->email = $email;
            }
            if ($avatar = $this->storeAvatar($googleUser->getAvatar())) {
                $byGoogle->avatar = $avatar;
            }
            $byGoogle->save();

            return $this->loginGoogleUser($request, $byGoogle, false);
        }

        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser && !$existingUser->isJamaah()) {
            AuditLog::record('user.google_login_blocked', $existingUser, [], [
                'reason' => 'admin_account',
            ]);

            return redirect()->route('login')
                ->with('error', 'Akun admin harus login dengan email/password dan verifikasi 2FA.');
        }

        if ($existingUser) {
            if (!$existingUser->is_active) {
                return redirect()->route('login')
                    ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            }

            $message = $existingUser->google_id
                ? 'Akun ini sudah tertaut ke identitas Google lain.'
                : 'Akun sudah terdaftar. Masuk dengan password, lalu tautkan Google dari halaman profil.';

            return redirect()->route('login')->with('error', $message);
        }

        $user = User::create([
            'name' => $googleUser->getName() ?? Str::before($googleUser->getEmail(), '@'),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $this->storeAvatar($googleUser->getAvatar()),
            'password' => Hash::make(Str::random(64)),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole('jamaah');
        AuditLog::record('user.registered_google', $user);

        try {
            app(NotificationService::class)->sendWelcome($user);
        } catch (\Exception) {
            // Don't block registration
        }

        return $this->loginGoogleUser($request, $user, true);
    }

    private function linkAuthenticatedAccount(Request $request, $googleUser): ?\Illuminate\Http\RedirectResponse
    {
        $linkId = $request->session()->pull('google.link');
        if (!$linkId) {
            return null;
        }

        $account = $request->user();
        if (!$account || (int) $account->id !== (int) $linkId || !$account->isJamaah()) {
            return redirect()->route('login')->with('error', 'Penautan Google dibatalkan. Masuk lagi lalu coba dari profil.');
        }

        $taken = User::where('google_id', $googleUser->getId())->whereKeyNot($account->id)->exists();
        if ($taken) {
            return redirect()->route('user.profile')->with('error', 'Identitas Google ini sudah dipakai akun lain.');
        }

        $account->update(['google_id' => $googleUser->getId()]);
        AuditLog::record('user.google_linked', $account);

        return redirect()->route('user.profile')->with('success', 'Akun Google berhasil ditautkan.');
    }

    private function loginGoogleUser(Request $request, User $user, bool $isNewUser): \Illuminate\Http\RedirectResponse
    {
        RememberLogin::start(true);
        Auth::login($user, true);
        $request->session()->regenerate();
        RememberLogin::persist($request, true);

        AuditLog::record('user.login_google', $user);

        $redirect = $this->safeInternalPath($request->session()->pull('google.redirect', '/'));

        $message = $isNewUser
            ? 'Akun berhasil dibuat. Selamat datang, ' . $user->name . '!'
            : 'Selamat datang kembali, ' . $user->name . '!';

        return redirect($redirect)->with('success', $message);
    }

    protected function storeAvatar(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get($url);
            if (!$response->successful()) {
                return null;
            }

            $path = 'avatars/google/' . Str::uuid() . '.jpg';
            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Exception) {
            return null;
        }
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
