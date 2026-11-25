<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\GoogleAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register', [
            'googleEnabled' => GoogleAuth::isConfigured(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $user->assignRole('jamaah');

        // Send welcome notification
        try {
            app(NotificationService::class)->sendWelcome($user);
        } catch (\Exception $e) {
            // Silently fail, don't block registration
        }

        Auth::login($user);

        return redirect('/')->with('success', 'Akun berhasil dibuat. Selamat datang, '.$user->name.'!');
    }
}
