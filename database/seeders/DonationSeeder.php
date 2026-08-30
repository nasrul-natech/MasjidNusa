<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\DonationCategory;
use App\Models\DonationCampaign;
use App\Models\PaymentMethod;
use App\Models\PaymentConfiguration;
use App\Models\DuitkuConfig;
use Illuminate\Database\Seeder;

class DonationSeeder extends Seeder
{
    public function run(): void
    {
        // Bank accounts
        BankAccount::firstOrCreate(
            ['account_number' => '1234567890'],
            [
                'bank_name' => 'Bank Mandiri',
                'account_name' => 'Masjid Nusa',
                'account_number' => '1234567890',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        BankAccount::firstOrCreate(
            ['account_number' => '0987654321'],
            [
                'bank_name' => 'Bank BCA',
                'account_name' => 'Masjid Nusa',
                'account_number' => '0987654321',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // Payment methods
        PaymentMethod::firstOrCreate(
            ['code' => 'manual_transfer'],
            ['name' => 'Transfer Manual', 'type' => 'manual', 'code' => 'manual_transfer', 'is_active' => true, 'sort_order' => 1]
        );

        PaymentMethod::firstOrCreate(
            ['code' => 'duitku'],
            ['name' => 'Duitku Payment Gateway', 'type' => 'gateway', 'code' => 'duitku', 'is_active' => true, 'sort_order' => 2]
        );

        // Payment config
        PaymentConfiguration::updateOrCreate(
            ['key' => 'active_payment_method'],
            ['value' => 'both', 'group' => 'payment', 'type' => 'select']
        );

        // Donation categories
        $categories = [
            ['name' => 'Renovasi Masjid', 'slug' => 'renovasi-masjid', 'description' => 'Donasi untuk renovasi dan pembangunan masjid'],
            ['name' => 'Operasional Masjid', 'slug' => 'operasional-masjid', 'description' => 'Donasi untuk operasional masjid sehari-hari'],
            ['name' => 'Infaq & Shadaqah', 'slug' => 'infaq-shadaqah', 'description' => 'Donasi infaq dan shadaqah umum'],
            ['name' => 'Zakat', 'slug' => 'zakat', 'description' => 'Pembayaran zakat'],
            ['name' => 'Kegiatan Sosial', 'slug' => 'kegiatan-sosial', 'description' => 'Donasi untuk kegiatan sosial'],
        ];

        foreach ($categories as $cat) {
            DonationCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Sample campaigns
        $opsCat = DonationCategory::where('slug', 'operasional-masjid')->first();
        $renovasiCat = DonationCategory::where('slug', 'renovasi-masjid')->first();

        DonationCampaign::firstOrCreate(
            ['slug' => 'operasional-masjid-2025'],
            [
                'title' => 'Operasional Masjid 2025',
                'slug' => 'operasional-masjid-2025',
                'description' => 'Bantu kami memenuhi kebutuhan operasional masjid selama tahun 2025.',
                'content' => '<p>Dana operasional digunakan untuk listrik, air, kebersihan, dan gaji imam serta muadzin.</p>',
                'target_amount' => 50000000,
                'collected_amount' => 18500000,
                'category_id' => $opsCat?->id,
                'status' => 'active',
                'is_featured' => true,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ]
        );

        DonationCampaign::firstOrCreate(
            ['slug' => 'renovasi-lantai-masjid'],
            [
                'title' => 'Renovasi Lantai Masjid',
                'slug' => 'renovasi-lantai-masjid',
                'description' => 'Program renovasi lantai dan karpet masjid agar lebih nyaman untuk beribadah.',
                'target_amount' => 120000000,
                'collected_amount' => 45000000,
                'category_id' => $renovasiCat?->id,
                'status' => 'active',
                'is_featured' => true,
                'start_date' => '2025-06-01',
                'end_date' => '2025-12-31',
            ]
        );
    }
}
