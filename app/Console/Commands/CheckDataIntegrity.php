<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDataIntegrity extends Command
{
    protected $signature = 'system:check-data-integrity';

    protected $description = 'Check duplicate key groups before adding data integrity constraints (read-only)';

    public function handle(): int
    {
        $checks = [
            'payment_proofs.transaction_id' => DB::table('payment_proofs')
                ->whereNotNull('transaction_id')->groupBy('transaction_id'),
            'quran_bookmarks.non_last_read' => DB::table('quran_bookmarks')
                ->where('is_last_read', false)->groupBy('user_id', 'surah_id', 'verse_number'),
            'quran_bookmarks.last_read_per_user' => DB::table('quran_bookmarks')
                ->where('is_last_read', true)->groupBy('user_id'),
            'quran_khatam_progress.read_range' => DB::table('quran_khatam_progress')
                ->groupBy('program_id', 'read_date', 'surah_number', 'verse_from', 'verse_to'),
            'prayer_locations.equran_province_equran_city' => DB::table('prayer_locations')
                ->whereNotNull('equran_province')->whereNotNull('equran_city')
                ->groupBy('equran_province', 'equran_city'),
        ];

        $hasConflicts = false;

        foreach ($checks as $label => $query) {
            // Count conflicting groups in SQL without retrieving record values.
            $count = DB::query()->fromSub($query->selectRaw('1')->havingRaw('COUNT(*) > 1'), 'conflicts')->count();
            $this->line($label.': '.$count);
            $hasConflicts = $hasConflicts || $count > 0;
        }

        return $hasConflicts ? self::FAILURE : self::SUCCESS;
    }
}
