<?php

namespace Tests\Feature;

use App\Models\SystemModule;
use App\Models\User;
use App\Support\AttendanceFeaturePermissions;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FeatureProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seeding_assigns_feature_permissions_after_migrations(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $expected = [
            'admin_operasional' => ['manage_activity_attendance', 'view_attendance_rewards', 'manage_attendance_rewards', 'finalize_attendance_rewards'],
            'admin_media_it' => ['configure_activity_attendance', 'manage_mosque_display'],
        ];
        foreach ($expected as $roleName => $permissions) {
            foreach ($permissions as $permission) {
                $this->assertTrue(Role::findByName($roleName)->hasPermissionTo($permission));
                $this->assertTrue(Role::findByName('super_admin')->hasPermissionTo($permission));
                $this->assertFalse(Role::findByName('jamaah')->hasPermissionTo($permission));
            }
        }
        $this->assertSame(0, SystemModule::whereIn('slug', ['activity_attendance', 'attendance_rewards', 'mosque_display'])->where('is_active', true)->count());
    }

    public function test_provisioning_is_idempotent_and_preserves_accounts_permissions_and_flags(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $extra = Permission::findOrCreate('custom_operator_permission', 'web');
        $role = Role::findByName('admin_operasional');
        $role->givePermissionTo($extra);
        $user = User::factory()->create();
        $user->assignRole($role);
        $password = $user->password;
        SystemModule::where('slug', 'activity_attendance')->update(['is_active' => true]);
        $permissionCount = Permission::count();

        AttendanceFeaturePermissions::provision();
        $this->artisan('features:provision-attendance-display')->assertSuccessful();

        $this->assertSame($permissionCount, Permission::count());
        $this->assertTrue($role->fresh()->hasPermissionTo($extra));
        $this->assertSame($password, $user->fresh()->password);
        $this->assertTrue($user->fresh()->hasRole('admin_operasional'));
        $this->assertTrue(SystemModule::where('slug', 'activity_attendance')->firstOrFail()->is_active);
        $this->assertFalse(SystemModule::where('slug', 'attendance_rewards')->firstOrFail()->is_active);
    }
}
