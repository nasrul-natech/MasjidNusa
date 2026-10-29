<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\TableSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');
        TableSort::apply($query, ['name', 'email', 'created_at', 'is_active'], 'created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->role) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->paginate(20);
        $roles = Role::all();

        return view('super-admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        $user = null;
        return view('super-admin.users.form', compact('user', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'min:8'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($data['role']);
        AuditLog::record('user.created', $user);

        return redirect()->route('super-admin.users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('super-admin.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string'],
            'role' => ['required', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        $willBeActive = $request->has('is_active')
            ? $request->boolean('is_active')
            : $user->is_active;
        $isLastActiveSuperAdmin = $user->is_active
            && $user->hasRole('super_admin')
            && User::role('super_admin')->where('is_active', true)->count() <= 1;

        if ($isLastActiveSuperAdmin && (!$willBeActive || $data['role'] !== 'super_admin')) {
            return back()->with('error', 'Tidak bisa menonaktifkan atau menurunkan super admin terakhir yang masih aktif.')->withInput();
        }

        $wasActive = $user->is_active;

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'is_active' => $data['is_active'] ?? $user->is_active,
        ]);

        if ($wasActive && !$user->is_active) {
            $user->revokeAccess();
        }

        $user->syncRoles([$data['role']]);
        AuditLog::record('user.updated', $user);

        return redirect()->route('super-admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    public function toggleActive(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        if ($user->is_active && $user->hasRole('super_admin')) {
            $activeSuperAdmins = User::role('super_admin')->where('is_active', true)->count();
            if ($activeSuperAdmins <= 1) {
                return back()->with('error', 'Tidak bisa menonaktifkan super admin terakhir yang masih aktif.');
            }
        }

        $activating = ! $user->is_active;
        $user->update(['is_active' => $activating]);
        if (! $activating) {
            $user->revokeAccess();
        }
        AuditLog::record($activating ? 'user.activated' : 'user.deactivated', $user);

        $message = $activating ? 'User diaktifkan kembali.' : 'User dinonaktifkan.';

        return back()->with('success', $message);
    }

    public function resetPassword(int $id)
    {
        $user = User::findOrFail($id);
        $newPassword = Str::password(16);
        $user->update(['password' => $newPassword]);
        $user->revokeAccess();
        AuditLog::record('user.password_reset_by_admin', $user);

        return back()->with('success', "Password baru, simpan sekarang karena hanya tampil sekali: {$newPassword}");
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = collect($data['user_ids'])
            ->unique()
            ->reject(fn ($id) => (int) $id === auth()->id())
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Tidak ada user valid yang dipilih (akun sendiri tidak bisa diproses).');
        }

        $users = User::with('roles')->whereIn('id', $ids)->get();
        $processed = 0;
        $skipped = 0;

        if ($data['action'] === 'deactivate') {
            foreach ($users as $user) {
                if (! $user->is_active) {
                    $skipped++;

                    continue;
                }
                if ($user->hasRole('super_admin')) {
                    $activeSuperAdmins = User::role('super_admin')->where('is_active', true)->count();
                    if ($activeSuperAdmins <= 1) {
                        $skipped++;

                        continue;
                    }
                }
                $user->update(['is_active' => false]);
                $user->revokeAccess();
                AuditLog::record('user.deactivated', $user);
                $processed++;
            }

            $message = "{$processed} user dinonaktifkan.";
            if ($skipped > 0) {
                $message .= " {$skipped} dilewati.";
            }

            return back()->with('success', $message);
        }

        if ($data['action'] === 'activate') {
            foreach ($users as $user) {
                if ($user->is_active) {
                    $skipped++;

                    continue;
                }
                $user->update(['is_active' => true]);
                AuditLog::record('user.activated', $user);
                $processed++;
            }

            $message = "{$processed} user diaktifkan.";
            if ($skipped > 0) {
                $message .= " {$skipped} dilewati.";
            }

            return back()->with('success', $message);
        }

        foreach ($users as $user) {
            if ($user->hasRole('super_admin')) {
                $superAdminCount = User::role('super_admin')->count();
                if ($superAdminCount <= 1) {
                    $skipped++;

                    continue;
                }
            }
            AuditLog::record('user.deleted', $user);
            $user->delete();
            $processed++;
        }

        $message = "{$processed} user dihapus.";
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati.";
        }

        return back()->with('success', $message);
    }
}
