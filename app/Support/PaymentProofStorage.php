<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PaymentProofStorage
{
    /**
     * Pindahkan bukti lama dari disk publik ke disk privat.
     * Salinan publik dihapus hanya setelah salinan privat berukuran sama.
     */
    public static function migrateToPrivate(string $path): bool
    {
        $local = Storage::disk('local');
        $public = Storage::disk('public');

        if (!$public->exists($path)) {
            return $local->exists($path);
        }

        if (!$local->exists($path)) {
            $local->put($path, $public->get($path));
        }

        if ($local->exists($path) && $local->size($path) === $public->size($path)) {
            $public->delete($path);

            return true;
        }

        return false;
    }
}
