<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\TableSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
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
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

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
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string'],
            'role' => ['required', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        $willBeActive = $request->has('is_active')
            ? $request->boolean('is_active')
            : $user->is_active;

        $blocked = false;
        DB::transaction(function () use ($user, $data, $willBeActive, &$blocked) {
            $this->lockSuperAdmins();
            $fresh = User::query()->whereKey($user->id)->lockForUpdate()->first();
            if (! $fresh) {
                return;
            }
            if ($this->removesLastActiveSuperAdmin($fresh, $willBeActive && $data['role'] === 'super_admin')) {
                $blocked = true;

                return;
            }

            $wasActive = $fresh->is_active;

            $fresh->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'is_active' => $willBeActive,
            ]);

            if ($wasActive && ! $fresh->is_active) {
                $fresh->revokeAccess();
            }

            $fresh->syncRoles([$data['role']]);
            AuditLog::record('user.updated', $fresh);
        });

        if ($blocked) {
            return back()->with('error', 'Tidak bisa menonaktifkan atau menurunkan super admin terakhir yang masih aktif.')->withInput();
        }

        return redirect()->route('super-admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->hasAttendanceHistory()) {
            return back()->with('error', 'Akun memiliki riwayat kehadiran/hadiah. Gunakan nonaktifkan akun, bukan hapus.');
        }
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $blocked = false;
        DB::transaction(function () use ($user, &$blocked) {
            $this->lockSuperAdmins();
            $fresh = User::query()->whereKey($user->id)->lockForUpdate()->first();
            if ($fresh && ($this->removesLastActiveSuperAdmin($fresh, false) || $fresh->hasAttendanceHistory(true))) {
                $blocked = true;

                return;
            }

            AuditLog::record('user.deleted', $fresh);
            $fresh?->delete();
        });

        if ($blocked) {
            return back()->with('error', 'Tidak bisa menghapus super admin terakhir atau akun dengan riwayat kehadiran/hadiah. Gunakan nonaktifkan akun.');
        }

        return back()->with('success', 'User berhasil dihapus.');
    }

    public function toggleActive(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        $blocked = false;
        $activating = false;
        DB::transaction(function () use ($user, &$blocked, &$activating) {
            $this->lockSuperAdmins();
            $fresh = User::query()->whereKey($user->id)->lockForUpdate()->first();
            if (! $fresh) {
                return;
            }
            $activating = ! $fresh->is_active;
            if (! $activating && $this->removesLastActiveSuperAdmin($fresh, false)) {
                $blocked = true;

                return;
            }

            $fresh->update(['is_active' => $activating]);
            if (! $activating) {
                $fresh->revokeAccess();
            }
            AuditLog::record($activating ? 'user.activated' : 'user.deactivated', $fresh);
        });

        if ($blocked) {
            return back()->with('error', 'Tidak bisa menonaktifkan super admin terakhir yang masih aktif.');
        }

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
            DB::transaction(function () use ($users, &$processed, &$skipped) {
                $this->lockSuperAdmins();
                $active = User::role('super_admin')->where('is_active', true)->count();

                foreach ($users as $user) {
                    $fresh = User::query()->whereKey($user->id)->lockForUpdate()->first();
                    if (! $fresh || ! $fresh->is_active) {
                        $skipped++;

                        continue;
                    }
                    if ($fresh->hasRole('super_admin')) {
                        if ($active <= 1) {
                            $skipped++;

                            continue;
                        }
                        $active--;
                    }
                    $fresh->update(['is_active' => false]);
                    $fresh->revokeAccess();
                    AuditLog::record('user.deactivated', $fresh);
                    $processed++;
                }
            });

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

        DB::transaction(function () use ($users, &$processed, &$skipped) {
            $this->lockSuperAdmins();
            $active = User::role('super_admin')->where('is_active', true)->count();

            foreach ($users as $user) {
                $fresh = User::query()->whereKey($user->id)->lockForUpdate()->first();
                if (! $fresh || $fresh->hasAttendanceHistory(true)) {
                    $skipped++;

                    continue;
                }
                if ($fresh->hasRole('super_admin') && $fresh->is_active) {
                    if ($active <= 1) {
                        $skipped++;

                        continue;
                    }
                    $active--;
                }
                AuditLog::record('user.deleted', $fresh);
                $fresh->delete();
                $processed++;
            }
        });

        $message = "{$processed} user dihapus.";
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati.";
        }

        return back()->with('success', $message);
    }

    private function lockSuperAdmins(): void
    {
        $roleTable = config('permission.table_names.roles', 'roles');
        $pivotTable = config('permission.table_names.model_has_roles', 'model_has_roles');

        $ids = DB::table($pivotTable)
            ->join($roleTable, "{$roleTable}.id", '=', "{$pivotTable}.role_id")
            ->where("{$roleTable}.name", 'super_admin')
            ->where("{$pivotTable}.model_type", User::class)
            ->orderBy("{$pivotTable}.model_id")
            ->pluck("{$pivotTable}.model_id");

        if ($ids->isNotEmpty()) {
            User::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        }
    }

    private function removesLastActiveSuperAdmin(?User $user, bool $willRemainActiveSuperAdmin): bool
    {
        if (! $user || ! $user->is_active || ! $user->hasRole('super_admin')) {
            return false;
        }

        $active = User::role('super_admin')->where('is_active', true)->count();

        return $active <= 1 && ! $willRemainActiveSuperAdmin;
    }
}
