<?php

namespace App\Services;

use App\Models\PrayerLocation;
use App\Models\PrayerTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        if (!$location->latitude || !$location->longitude) {
            throw new \RuntimeException(
                "Lokasi {$location->city_name} belum memiliki koordinat (latitude/longitude). "
                . "Silakan edit lokasi dan isi koordinatnya."
            );
        }

        $response = Http::timeout(self::TIMEOUT)
            ->withHeaders(['Accept' => 'application/json'])
            ->get(self::BASE_URL . '/calendar/' . $year . '/' . $month, [
                'latitude'  => $location->latitude,
                'longitude' => $location->longitude,
                'method'    => self::CALCULATION_METHOD,
            ]);

        if (!$response->successful()) {
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
                if (!$dateStr) {
                    continue;
                }

                // Aladhan returns date as "DD-MM-YYYY", convert to "YYYY-MM-DD"
                $date = $this->convertAladhanDate($dateStr);
                if (!$date) {
                    continue;
                }

                $timeData = [
                    'location_id' => $location->id,
                    'date'        => $date,
                    'imsak'       => $this->parseAladhanTime($timings['Imsak'] ?? null),
                    'subuh'       => $this->parseAladhanTime($timings['Fajr'] ?? null),
                    'terbit'      => $this->parseAladhanTime($timings['Sunrise'] ?? null),
                    'dhuha'       => $this->estimateDhuha($timings['Sunrise'] ?? null),
                    'dzuhur'      => $this->parseAladhanTime($timings['Dhuhr'] ?? null),
                    'ashar'       => $this->parseAladhanTime($timings['Asr'] ?? null),
                    'maghrib'     => $this->parseAladhanTime($timings['Maghrib'] ?? null),
                    'isya'        => $this->parseAladhanTime($timings['Isha'] ?? null),
                ];

                if (empty($timeData['subuh']) || empty($timeData['dzuhur'])) {
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
    private function parseAladhanTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $clean = trim(preg_replace('/\s*\(.*\)/', '', $value));

        if (preg_match('/^(\d{2}):(\d{2})/', $clean, $m)) {
            return $m[1] . ':' . $m[2];
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
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }

    /**
     * Dhuha ≈ Sunrise + 15 minutes (Aladhan doesn't provide Dhuha directly).
     */
    private function estimateDhuha(?string $sunriseRaw): ?string
    {
        $sunrise = $this->parseAladhanTime($sunriseRaw);
        if (!$sunrise) {
            return null;
        }

        try {
            $dt = \Carbon\Carbon::createFromFormat('H:i', $sunrise);
            return $dt->addMinutes(15)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    // ===== Province / City lists (kept for backward compatibility but now using preset data) =====

    /**
     * Get predefined list of Indonesian provinces.
     */
    public function getProvinces(): array
    {
        return array_keys(self::INDONESIAN_CITIES);
    }

    /**
     * Get predefined list of cities for a given province.
     */
    public function getCities(string $province): array
    {
        return self::INDONESIAN_CITIES[$province] ?? [];
    }

    /**
     * Preset of major Indonesian cities with coordinates.
     * Used for location selection in admin panel.
     */
    public const INDONESIAN_CITIES = [
        'Aceh' => [
            ['name' => 'Banda Aceh', 'lat' => 5.5483, 'lng' => 95.3238],
            ['name' => 'Lhokseumawe', 'lat' => 5.1801, 'lng' => 97.1507],
            ['name' => 'Langsa', 'lat' => 4.4681, 'lng' => 97.9683],
            ['name' => 'Sabang', 'lat' => 5.8933, 'lng' => 95.3191],
            ['name' => 'Meulaboh', 'lat' => 4.1363, 'lng' => 96.1285],
        ],
        'Sumatera Utara' => [
            ['name' => 'Medan', 'lat' => 3.5952, 'lng' => 98.6722],
            ['name' => 'Binjai', 'lat' => 3.6001, 'lng' => 98.4854],
            ['name' => 'Pematang Siantar', 'lat' => 2.9498, 'lng' => 99.0498],
            ['name' => 'Padang Sidempuan', 'lat' => 1.3791, 'lng' => 99.2716],
            ['name' => 'Sibolga', 'lat' => 1.7427, 'lng' => 98.7792],
        ],
        'Sumatera Barat' => [
            ['name' => 'Padang', 'lat' => -0.9471, 'lng' => 100.4172],
            ['name' => 'Bukittinggi', 'lat' => -0.3066, 'lng' => 100.3691],
            ['name' => 'Payakumbuh', 'lat' => -0.2193, 'lng' => 100.6308],
            ['name' => 'Solok', 'lat' => -0.7992, 'lng' => 100.6544],
        ],
        'Riau' => [
            ['name' => 'Pekanbaru', 'lat' => 0.5071, 'lng' => 101.4478],
            ['name' => 'Dumai', 'lat' => 1.6667, 'lng' => 101.4500],
        ],
        'Kepulauan Riau' => [
            ['name' => 'Tanjung Pinang', 'lat' => 0.9188, 'lng' => 104.4591],
            ['name' => 'Batam', 'lat' => 1.0456, 'lng' => 104.0305],
        ],
        'Jambi' => [
            ['name' => 'Kota Jambi', 'lat' => -1.5998, 'lng' => 103.6070],
            ['name' => 'Sungai Penuh', 'lat' => -2.0590, 'lng' => 101.3928],
        ],
        'Sumatera Selatan' => [
            ['name' => 'Palembang', 'lat' => -2.9761, 'lng' => 104.7754],
            ['name' => 'Prabumulih', 'lat' => -3.4285, 'lng' => 104.2304],
            ['name' => 'Lubuk Linggau', 'lat' => -3.2965, 'lng' => 102.8613],
        ],
        'Bengkulu' => [
            ['name' => 'Kota Bengkulu', 'lat' => -3.8004, 'lng' => 102.2655],
        ],
        'Lampung' => [
            ['name' => 'Bandar Lampung', 'lat' => -5.3971, 'lng' => 105.2668],
            ['name' => 'Metro', 'lat' => -5.1135, 'lng' => 105.3068],
        ],
        'Bangka Belitung' => [
            ['name' => 'Pangkal Pinang', 'lat' => -2.1316, 'lng' => 106.1169],
        ],
        'DKI Jakarta' => [
            ['name' => 'Jakarta Pusat', 'lat' => -6.1862, 'lng' => 106.8340],
            ['name' => 'Jakarta Utara', 'lat' => -6.1384, 'lng' => 106.8638],
            ['name' => 'Jakarta Barat', 'lat' => -6.1673, 'lng' => 106.7637],
            ['name' => 'Jakarta Selatan', 'lat' => -6.2615, 'lng' => 106.8106],
            ['name' => 'Jakarta Timur', 'lat' => -6.2250, 'lng' => 106.9004],
            ['name' => 'Kepulauan Seribu', 'lat' => -5.6104, 'lng' => 106.5640],
        ],
        'Jawa Barat' => [
            ['name' => 'Bandung', 'lat' => -6.9175, 'lng' => 107.6191],
            ['name' => 'Bogor', 'lat' => -6.5971, 'lng' => 106.8060],
            ['name' => 'Bekasi', 'lat' => -6.2383, 'lng' => 106.9756],
            ['name' => 'Depok', 'lat' => -6.4025, 'lng' => 106.7942],
            ['name' => 'Cirebon', 'lat' => -6.7320, 'lng' => 108.5523],
            ['name' => 'Sukabumi', 'lat' => -6.9277, 'lng' => 106.9300],
            ['name' => 'Tasikmalaya', 'lat' => -7.3274, 'lng' => 108.2207],
            ['name' => 'Garut', 'lat' => -7.2275, 'lng' => 107.9089],
        ],
        'Banten' => [
            ['name' => 'Serang', 'lat' => -6.1142, 'lng' => 106.1503],
            ['name' => 'Tangerang', 'lat' => -6.1702, 'lng' => 106.6403],
            ['name' => 'Tangerang Selatan', 'lat' => -6.2886, 'lng' => 106.7170],
            ['name' => 'Cilegon', 'lat' => -6.0023, 'lng' => 105.9650],
        ],
        'Jawa Tengah' => [
            ['name' => 'Semarang', 'lat' => -6.9666, 'lng' => 110.4196],
            ['name' => 'Solo (Surakarta)', 'lat' => -7.5755, 'lng' => 110.8243],
            ['name' => 'Magelang', 'lat' => -7.4797, 'lng' => 110.2177],
            ['name' => 'Pekalongan', 'lat' => -6.8885, 'lng' => 109.6753],
            ['name' => 'Tegal', 'lat' => -6.8797, 'lng' => 109.1256],
            ['name' => 'Purwokerto', 'lat' => -7.4214, 'lng' => 109.2341],
            ['name' => 'Kudus', 'lat' => -6.8048, 'lng' => 110.8405],
        ],
        'DI Yogyakarta' => [
            ['name' => 'Yogyakarta', 'lat' => -7.7956, 'lng' => 110.3695],
            ['name' => 'Sleman', 'lat' => -7.7161, 'lng' => 110.3553],
            ['name' => 'Bantul', 'lat' => -7.8891, 'lng' => 110.3278],
        ],
        'Jawa Timur' => [
            ['name' => 'Surabaya', 'lat' => -7.2575, 'lng' => 112.7521],
            ['name' => 'Malang', 'lat' => -7.9786, 'lng' => 112.6317],
            ['name' => 'Kediri', 'lat' => -7.8480, 'lng' => 112.0178],
            ['name' => 'Madiun', 'lat' => -7.6298, 'lng' => 111.5230],
            ['name' => 'Mojokerto', 'lat' => -7.4724, 'lng' => 112.4340],
            ['name' => 'Jember', 'lat' => -8.1845, 'lng' => 113.6681],
            ['name' => 'Banyuwangi', 'lat' => -8.2190, 'lng' => 114.3691],
            ['name' => 'Gresik', 'lat' => -7.1570, 'lng' => 112.6535],
        ],
        'Bali' => [
            ['name' => 'Denpasar', 'lat' => -8.6500, 'lng' => 115.2167],
            ['name' => 'Singaraja', 'lat' => -8.1120, 'lng' => 115.0893],
        ],
        'Nusa Tenggara Barat' => [
            ['name' => 'Mataram', 'lat' => -8.5833, 'lng' => 116.1167],
            ['name' => 'Bima', 'lat' => -8.4605, 'lng' => 118.7270],
        ],
        'Nusa Tenggara Timur' => [
            ['name' => 'Kupang', 'lat' => -10.1772, 'lng' => 123.6070],
        ],
        'Kalimantan Barat' => [
            ['name' => 'Pontianak', 'lat' => -0.0263, 'lng' => 109.3425],
            ['name' => 'Singkawang', 'lat' => 0.9053, 'lng' => 108.9872],
        ],
        'Kalimantan Tengah' => [
            ['name' => 'Palangka Raya', 'lat' => -2.2070, 'lng' => 113.9108],
        ],
        'Kalimantan Selatan' => [
            ['name' => 'Banjarmasin', 'lat' => -3.3194, 'lng' => 114.5900],
            ['name' => 'Banjarbaru', 'lat' => -3.4414, 'lng' => 114.8431],
        ],
        'Kalimantan Timur' => [
            ['name' => 'Samarinda', 'lat' => -0.4948, 'lng' => 117.1436],
            ['name' => 'Balikpapan', 'lat' => -1.2379, 'lng' => 116.8529],
            ['name' => 'Bontang', 'lat' => 0.1333, 'lng' => 117.5000],
        ],
        'Kalimantan Utara' => [
            ['name' => 'Tarakan', 'lat' => 3.3000, 'lng' => 117.6333],
        ],
        'Sulawesi Utara' => [
            ['name' => 'Manado', 'lat' => 1.4748, 'lng' => 124.8421],
            ['name' => 'Bitung', 'lat' => 1.4404, 'lng' => 125.1217],
        ],
        'Gorontalo' => [
            ['name' => 'Kota Gorontalo', 'lat' => 0.5435, 'lng' => 123.0568],
        ],
        'Sulawesi Tengah' => [
            ['name' => 'Palu', 'lat' => -0.8917, 'lng' => 119.8707],
        ],
        'Sulawesi Selatan' => [
            ['name' => 'Makassar', 'lat' => -5.1477, 'lng' => 119.4327],
            ['name' => 'Parepare', 'lat' => -4.0135, 'lng' => 119.6254],
            ['name' => 'Palopo', 'lat' => -2.9924, 'lng' => 120.1969],
        ],
        'Sulawesi Tenggara' => [
            ['name' => 'Kendari', 'lat' => -3.9450, 'lng' => 122.4989],
            ['name' => 'Baubau', 'lat' => -5.4700, 'lng' => 122.6200],
        ],
        'Sulawesi Barat' => [
            ['name' => 'Mamuju', 'lat' => -2.6748, 'lng' => 118.8886],
        ],
        'Maluku' => [
            ['name' => 'Ambon', 'lat' => -3.6954, 'lng' => 128.1814],
            ['name' => 'Tual', 'lat' => -5.6333, 'lng' => 132.7333],
        ],
        'Maluku Utara' => [
            ['name' => 'Ternate', 'lat' => 0.7833, 'lng' => 127.3833],
        ],
        'Papua' => [
            ['name' => 'Jayapura', 'lat' => -2.5333, 'lng' => 140.7167],
        ],
        'Papua Barat' => [
            ['name' => 'Manokwari', 'lat' => -0.8615, 'lng' => 134.0620],
            ['name' => 'Sorong', 'lat' => -0.8762, 'lng' => 131.2870],
        ],
        'Papua Selatan' => [
            ['name' => 'Merauke', 'lat' => -8.4932, 'lng' => 140.4018],
        ],
        'Papua Tengah' => [
            ['name' => 'Nabire', 'lat' => -3.3666, 'lng' => 135.4833],
        ],
        'Papua Pegunungan' => [
            ['name' => 'Wamena', 'lat' => -4.1000, 'lng' => 138.9500],
        ],
        'Papua Barat Daya' => [
            ['name' => 'Kota Sorong', 'lat' => -0.8618, 'lng' => 131.2553],
        ],
    ];
}
