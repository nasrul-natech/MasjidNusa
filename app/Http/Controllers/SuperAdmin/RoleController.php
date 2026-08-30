<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(fn($p) => explode('_', $p->name)[0]);
        return view('super-admin.roles.index', compact('roles', 'permissions'));
    }

    public function assign(Request $request, int $id)
    {
        $role = Role::findOrFail($id);
        $permissions = $request->get('permissions', []);
        $role->syncPermissions($permissions);
        return back()->with('success', "Permissions role {$role->name} berhasil diperbarui.");
    }
}
