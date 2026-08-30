<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@masjidnusa.id',
                'phone' => '08111000001',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin Operasional',
                'email' => 'operasional@masjidnusa.id',
                'phone' => '08111000002',
                'password' => Hash::make('password'),
                'role' => 'admin_operasional',
            ],
            [
                'name' => 'Admin Keuangan',
                'email' => 'keuangan@masjidnusa.id',
                'phone' => '08111000003',
                'password' => Hash::make('password'),
                'role' => 'admin_keuangan',
            ],
            [
                'name' => 'Admin Media IT',
                'email' => 'mediait@masjidnusa.id',
                'phone' => '08111000004',
                'password' => Hash::make('password'),
                'role' => 'admin_media_it',
            ],
            [
                'name' => 'Jamaah Demo',
                'email' => 'jamaah@masjidnusa.id',
                'phone' => '08111000005',
                'password' => Hash::make('password'),
                'role' => 'jamaah',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['email_verified_at' => now()])
            );

            $user->syncRoles([$role]);
        }
    }
}
