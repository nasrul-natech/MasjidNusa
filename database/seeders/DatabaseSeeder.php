<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            MosqueProfileSeeder::class,
            DonationSeeder::class,
            ContentSeeder::class,
            DoaSeeder::class,
            ZikirPagiPetangSeeder::class,
        ]);
    }
}
