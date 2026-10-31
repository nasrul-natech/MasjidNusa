<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class AttendanceFeaturePermissions
{
    public static function provision(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach ([
            'admin_operasional' => ['manage_activity_attendance', 'view_attendance_rewards', 'finalize_attendance_rewards', 'manage_attendance_rewards'],
            'admin_media_it' => ['configure_activity_attendance', 'manage_mosque_display'],
        ] as $roleName => $permissions) {
            foreach ($permissions as $name) {
                $permission = Permission::findOrCreate($name, 'web');
                Role::where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($permission);
                Role::where('name', 'super_admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
