<?php

namespace App\Services;

use App\Models\PrayerLocation;
use App\Models\PrayerTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PrayerScheduleService
{
    private const BASE_URL = 'https://api.aladhan.com/v1';

    private const TIMEOUT = 30;

    /**
     * Aladhan calculation method:
     * 20 = Kemenag (Kementerian Agama Republik Indonesia)
     */
    private const CALCULATION_METHOD = 20;

    /**
     * Fetch monthly prayer schedule from Aladhan API and save to prayer_times table.
     * Uses latitude/longitude from the PrayerLocation model.
     */
    public function fetchAndSave(PrayerLocation $location, int $month, int $year): int
    {
        if (! $location->latitude || ! $location->longitude) {
            throw new \RuntimeException(
                "Lokasi {$location->city_name} belum memiliki koordinat (latitude/longitude). "
                .'Silakan edit lokasi dan isi koordinatnya.'
            );
        }

        $response = Http::timeout(self::TIMEOUT)
            ->withHeaders(['Accept' => 'application/json'])
            ->get(self::BASE_URL.'/calendar/'.$year.'/'.$month, [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'method' => self::CALCULATION_METHOD,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Aladhan API mengembalikan status {$response->status()} untuk {$location->city_name}."
            );
        }

        $data = $response->json('data', []);

        if (empty($data)) {
            throw new \RuntimeException(
                "Aladhan API tidak mengembalikan data untuk {$location->city_name} ({$month}/{$year})."
            );
        }

        $saved = 0;

        DB::beginTransaction();
        try {
            foreach ($data as $dayData) {
                $timings = $dayData['timings'] ?? [];
                $dateInfo = $dayData['date'] ?? [];
                $gregorian = $dateInfo['gregorian'] ?? [];

                $dateStr = $gregorian['date'] ?? null;
                if (! $dateStr) {
                    continue;
                }

                // Aladhan returns date as "DD-MM-YYYY", convert to "YYYY-MM-DD"
                $date = $this->convertAladhanDate($dateStr);
                if (! $date) {
                    continue;
                }

                $timeData = [
                    'location_id' => $location->id,
                    'date' => $date,
                    'imsak' => $this->parseAladhanTime($timings['Imsak'] ?? null),
                    'subuh' => $this->parseAladhanTime($timings['Fajr'] ?? null),
                    'terbit' => $this->parseAladhanTime($timings['Sunrise'] ?? null),
                    'dhuha' => $this->estimateDhuha($timings['Sunrise'] ?? null),
                    'dzuhur' => $this->parseAladhanTime($timings['Dhuhr'] ?? null),
                    'ashar' => $this->parseAladhanTime($timings['Asr'] ?? null),
                    'maghrib' => $this->parseAladhanTime($timings['Maghrib'] ?? null),
                    'isya' => $this->parseAladhanTime($timings['Isha'] ?? null),
                ];

                if (in_array(null, array_intersect_key($timeData, array_flip(['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'])), true)) {
                    continue;
                }

                PrayerTime::updateOrCreate(
                    ['location_id' => $location->id, 'date' => $date],
                    $timeData
                );
                $saved++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $saved;
    }

    /**
     * Aladhan returns times like "04:33 (WIB)" — strip the timezone part.
     */
    private function parseAladhanTime(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $clean = trim(preg_replace('/\s*\(.*\)/', '', $value));

        if (preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/D', $clean, $m)) {
            return $m[1].':'.$m[2];
        }

        return null;
    }

    /**
     * Aladhan date format "DD-MM-YYYY" to "YYYY-MM-DD".
     */
    private function convertAladhanDate(string $dateStr): ?string
    {
        $parts = explode('-', $dateStr);
        if (count($parts) !== 3) {
            return null;
        }

        return $parts[2].'-'.$parts[1].'-'.$parts[0];
    }

    /**
     * Dhuha ≈ Sunrise + 15 minutes (Aladhan doesn't provide Dhuha directly).
     */
    private function estimateDhuha(mixed $sunriseRaw): ?string
    {
        $sunrise = $this->parseAladhanTime($sunriseRaw);
        if (! $sunrise) {
            return null;
        }

        try {
            $dt = Carbon::createFromFormat('H:i', $sunrise);

            return $dt->addMinutes(15)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Nama provinsi untuk dropdown lokasi.
     * Sumber: database/data/indonesian-regions.json
     *
     * @return list<string>
     */
    public function getProvinces(): array
    {
        return array_keys($this->regions());
    }

    /**
     * Kabupaten/kota satu provinsi. Bentuk tetap name, lat, lng
     * agar koordinat bisa dikirim ke Aladhan tanpa ubah kontrak API.
     *
     * @return list<array{name: string, lat: float, lng: float}>
     */
    public function getCities(string $province): array
    {
        $cities = $this->regions()[$province] ?? [];

        return array_values($cities);
    }

    /**
     * @return array<string, list<array{name: string, lat: float, lng: float}>>
     */
    private function regions(): array
    {
        static $regions = null;

        if (is_array($regions)) {
            return $regions;
        }

        $path = database_path('data/indonesian-regions.json');
        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded) || $decoded === []) {
            throw new \RuntimeException('Data wilayah Indonesia tidak dapat dibaca.');
        }

        $regions = $decoded;

        return $regions;
    }
}
