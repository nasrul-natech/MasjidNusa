<?php

namespace App\Console\Commands;

use App\Support\PaymentProofStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrivatizePaymentProofs extends Command
{
    protected $signature = 'proofs:privatize';

    protected $description = 'Pindahkan bukti transfer lama dari disk publik ke disk privat setelah salinan tervalidasi';

    public function handle(): int
    {
        $files = Storage::disk('public')->allFiles('payment-proofs');
        $moved = 0;
        $kept = 0;

        foreach ($files as $path) {
            if (str_ends_with($path, '.htaccess')) {
                continue;
            }

            if (PaymentProofStorage::migrateToPrivate($path)) {
                $moved++;
            } else {
                $kept++;
                $this->warn("Dibiarkan di publik karena salinan privat belum cocok: {$path}");
            }
        }

        Storage::disk('public')->put("payment-proofs/.htaccess", "Require all denied\nDeny from all\n");

        $this->info("Dipindahkan: {$moved}. Masih publik: {$kept}.");

        return self::SUCCESS;
    }
}
