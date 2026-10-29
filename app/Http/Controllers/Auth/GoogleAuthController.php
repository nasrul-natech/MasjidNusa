<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\NotificationService;
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

        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser && !$existingUser->isJamaah()) {
            AuditLog::record('user.google_login_blocked', $existingUser, [], [
                'reason' => 'admin_account',
            ]);

            return redirect()->route('login')
                ->with('error', 'Akun admin harus login dengan email/password dan verifikasi 2FA.');
        }

        $isNewUser = false;

        if ($existingUser) {
            if (!$existingUser->is_active) {
                return redirect()->route('login')
                    ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            }

            if ($existingUser->google_id && (string) $existingUser->google_id !== (string) $googleUser->getId()) {
                return redirect()->route('login')
                    ->with('error', 'Akun ini sudah tertaut ke identitas Google lain.');
            }

            $updates = [];
            if (!$existingUser->google_id) {
                $updates['google_id'] = $googleUser->getId();
            }
            if ($avatar = $this->storeAvatar($googleUser->getAvatar())) {
                $updates['avatar'] = $avatar;
            }
            if ($updates) {
                $existingUser->update($updates);
            }

            $user = $existingUser;
        } else {
            $isNewUser = true;
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
        }

        Auth::login($user, true);
        $request->session()->regenerate();

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
