<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('user.profile', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'current_password' => ['required_with:password', 'nullable', 'string'],
            'password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        $update = [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'gender' => $data['gender'],
            'birth_date' => $data['birth_date'],
        ];

        if (!empty($data['password'])) {
            if (empty($data['current_password']) || !Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini wajib diisi dan harus sesuai.'])->withInput();
            }
            $update['password'] = $data['password'];
        }

        if ($request->hasFile('avatar')) {
            $update['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($update);
        AuditLog::record('user.profile_updated', $user);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
