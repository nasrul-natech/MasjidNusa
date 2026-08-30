<?php

namespace App\Services;

use App\Models\PrayerLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PrayerLocationResolver
{
    /**
     * Resolve prayer location from GPS: prioritize administrative area (kota/kabupaten)
     * over pure distance — important for border areas (e.g. Bekasi vs Jakarta Timur).
     */
    public function resolve(float $lat, float $lon): ?PrayerLocation
    {
        $locations = PrayerLocation::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($locations->isEmpty()) {
            return null;
        }

        $geocoded = $this->reverseGeocode($lat, $lon);
        $candidates = $this->scoreByAdministrativeArea($locations, $geocoded, $lat, $lon);

        $bestMatches = $candidates->filter(fn ($item) => $item['score'] >= 60);

        if ($bestMatches->isNotEmpty()) {
            return $bestMatches->sortBy('distance_km')->first()['location'];
        }

        $weakMatches = $candidates->filter(fn ($item) => $item['score'] >= 30);
        if ($weakMatches->isNotEmpty()) {
            return $weakMatches
                ->sort(function (array $a, array $b) {
                    if ($a['score'] !== $b['score']) {
                        return $b['score'] <=> $a['score'];
                    }

                    return $a['distance_km'] <=> $b['distance_km'];
                })
                ->first()['location'];
        }

        return $locations
            ->map(fn (PrayerLocation $loc) => [
                'location'    => $loc,
                'distance_km' => $this->haversineKm($lat, $lon, (float) $loc->latitude, (float) $loc->longitude),
            ])
            ->sortBy('distance_km')
            ->first()['location'] ?? null;
    }

    /**
     * @return array{city: string[], province: string[], raw: array<string, mixed>|null}
     */
    private function reverseGeocode(float $lat, float $lon): array
    {
        $cacheKey = 'prayer_geocode_' . round($lat, 4) . '_' . round($lon, 4);

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lon) {
            try {
                $response = Http::timeout(8)
                    ->withHeaders(['User-Agent' => 'MasjidNusa-App/1.0'])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat'             => $lat,
                        'lon'             => $lon,
                        'format'          => 'json',
                        'accept-language' => 'id',
                        'addressdetails'  => 1,
                        'zoom'            => 10,
                    ]);

                if (!$response->successful()) {
                    return ['city' => [], 'province' => [], 'raw' => null];
                }

                $data = $response->json();
                $addr = $data['address'] ?? [];

                $cityNames = collect([
                    $addr['city'] ?? null,
                    $addr['town'] ?? null,
                    $addr['municipality'] ?? null,
                    $addr['county'] ?? null,
                    $addr['city_district'] ?? null,
                    $addr['suburb'] ?? null,
                ])
                    ->filter()
                    ->flatMap(fn ($name) => $this->expandPlaceName((string) $name))
                    ->unique()
                    ->values()
                    ->all();

                $provinceNames = collect([
                    $addr['state'] ?? null,
                    $addr['region'] ?? null,
                ])
                    ->filter()
                    ->flatMap(fn ($name) => $this->expandPlaceName((string) $name))
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'city'     => $cityNames,
                    'province' => $provinceNames,
                    'raw'      => $addr,
                ];
            } catch (\Throwable) {
                return ['city' => [], 'province' => [], 'raw' => null];
            }
        });
    }

    /**
     * @return string[]
     */
    private function expandPlaceName(string $name): array
    {
        $variants = [$name];
        $normalized = $this->normalizePlaceName($name);

        if ($normalized !== $name) {
            $variants[] = $normalized;
        }

        foreach (['Kota ', 'Kabupaten ', 'Kab. ', 'Kec. ', 'Kecamatan '] as $prefix) {
            if (Str::startsWith($name, $prefix)) {
                $variants[] = trim(Str::after($name, $prefix));
            }
        }

        return array_values(array_unique(array_filter($variants)));
    }

    private function normalizePlaceName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/^(Kota|Kabupaten|Kab\.|Kec\.|Kecamatan)\s+/iu', '', $name) ?? $name;

        $aliases = [
            'dki jakarta'                        => 'jakarta',
            'daerah khusus ibukota jakarta'      => 'jakarta',
            'jakarta timur'                      => 'jakarta timur',
            'jakarta barat'                      => 'jakarta barat',
            'jakarta selatan'                    => 'jakarta selatan',
            'jakarta utara'                      => 'jakarta utara',
            'jakarta pusat'                      => 'jakarta pusat',
            'kota administrasi jakarta timur'    => 'jakarta timur',
            'kota administrasi jakarta barat'    => 'jakarta barat',
            'kota administrasi jakarta selatan'  => 'jakarta selatan',
            'kota administrasi jakarta utara'    => 'jakarta utara',
            'kota administrasi jakarta pusat'    => 'jakarta pusat',
            'kota bekasi'                        => 'bekasi',
            'kabupaten bekasi'                   => 'bekasi',
            'jawa barat'                         => 'jawa barat',
            'banten'                             => 'banten',
        ];

        $lower = Str::lower($name);

        return $aliases[$lower] ?? $lower;
    }

    /**
     * @param  Collection<int, PrayerLocation>  $locations
     * @param  array{city: string[], province: string[], raw: array<string, mixed>|null}  $geocoded
     * @return Collection<int, array{location: PrayerLocation, score: int, distance_km: float}>
     */
    private function scoreByAdministrativeArea(Collection $locations, array $geocoded, float $lat, float $lon): Collection
    {
        return $locations->map(function (PrayerLocation $loc) use ($geocoded, $lat, $lon) {
            $cityNorm = $this->normalizePlaceName($loc->city_name);
            $provNorm = $this->normalizePlaceName($loc->province_name);
            $score = 0;

            foreach ($geocoded['city'] as $geoCity) {
                $geoNorm = $this->normalizePlaceName($geoCity);

                if ($geoNorm === $cityNorm) {
                    $score = max($score, 100);
                } elseif (Str::contains($geoNorm, $cityNorm) || Str::contains($cityNorm, $geoNorm)) {
                    $score = max($score, 85);
                } else {
                    $geoWords = preg_split('/\s+/', $geoNorm) ?: [];
                    $cityWords = preg_split('/\s+/', $cityNorm) ?: [];
                    $matchedWords = collect($geoWords)->filter(
                        fn ($w) => strlen($w) > 3 && in_array($w, $cityWords, true)
                    )->count();
                    if ($matchedWords > 0) {
                        $score = max($score, 40 + ($matchedWords * 10));
                    }
                }
            }

            foreach ($geocoded['province'] as $geoProv) {
                $geoNorm = $this->normalizePlaceName($geoProv);
                if ($geoNorm === $provNorm || Str::contains($geoNorm, $provNorm) || Str::contains($provNorm, $geoNorm)) {
                    $score += 15;
                }
            }

            return [
                'location'    => $loc,
                'score'       => $score,
                'distance_km' => $this->haversineKm(
                    $lat,
                    $lon,
                    (float) $loc->latitude,
                    (float) $loc->longitude
                ),
            ];
        });
    }

    public function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
