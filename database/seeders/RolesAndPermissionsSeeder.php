<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Public
            'view_public',

            // User/Jamaah
            'manage_own_profile',
            'view_notifications',
            'create_donation',
            'view_own_donations',

            // Admin Operasional
            'access_admin_operasional',
            'manage_mosque_profile',
            'manage_mosque_history',
            'manage_management_history',
            'manage_organization_structure',
            'manage_prayer_schedule',
            'manage_general_info',
            'view_basic_statistics',

            // Admin Keuangan
            'access_admin_keuangan',
            'manage_donations',
            'verify_payments',
            'manage_payment_config',
            'manage_bank_accounts',
            'manage_duitku',
            'view_donation_reports',
            'export_reports',

            // Admin Media IT
            'access_admin_media_it',
            'manage_activities',
            'manage_articles',
            'manage_banners',
            'manage_popup_ads',
            'manage_stories',
            'manage_home_content',
            'publish_content',

            // Super Admin
            'manage_users',
            'manage_roles',
            'manage_smtp',
            'manage_email_templates',
            'manage_payment_rules',
            'manage_duitku_global',
            'view_audit_log',
            'manage_settings',
            'manage_modules',
            'manage_branding',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $jamaah = Role::firstOrCreate(['name' => 'jamaah']);
        $jamaah->syncPermissions(['view_public', 'manage_own_profile', 'view_notifications', 'create_donation', 'view_own_donations']);

        $adminOps = Role::firstOrCreate(['name' => 'admin_operasional']);
        $adminOps->syncPermissions([
            'view_public', 'access_admin_operasional', 'manage_mosque_profile', 'manage_mosque_history',
            'manage_management_history', 'manage_organization_structure', 'manage_prayer_schedule',
            'manage_general_info', 'view_basic_statistics',
        ]);

        $adminKeuangan = Role::firstOrCreate(['name' => 'admin_keuangan']);
        $adminKeuangan->syncPermissions([
            'view_public', 'access_admin_keuangan', 'manage_donations', 'verify_payments',
            'manage_payment_config', 'manage_bank_accounts', 'manage_duitku',
            'view_donation_reports', 'export_reports',
        ]);

        $adminMedia = Role::firstOrCreate(['name' => 'admin_media_it']);
        $adminMedia->syncPermissions([
            'view_public', 'access_admin_media_it', 'manage_activities', 'manage_articles',
            'manage_banners', 'manage_popup_ads', 'manage_stories', 'manage_home_content', 'publish_content',
        ]);

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());
    }
}
