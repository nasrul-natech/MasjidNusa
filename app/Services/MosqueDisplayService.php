<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\MosqueDisplay;
use App\Models\PrayerLocation;
use App\Models\PrayerSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MosqueDisplayService
{
    public const THEMES = ['emerald' => 'Zamrud', 'daylight' => 'Cerah (putih)', 'midnight' => 'Malam', 'sand' => 'Pasir', 'royal' => 'Safir', 'ocean' => 'Samudra', 'maroon' => 'Marun', 'olive' => 'Zaitun', 'slate' => 'Batu'];

    /**
     * Kartu media di TV 1920×1080 tidak sama di tiap tata letak.
     * Foto 16:9 yang dipaksakan ke kartu lebih lebar atau lebih sempit akan terpotong saat mode "Penuhi frame".
     */
    public const MEDIA_FRAMES = [
        'focus' => [
            'label' => 'Fokus media',
            'width' => 1920,
            'height' => 880,
            'note' => 'Kartu selebar layar, di antara header dan jadwal. Lebih lebar dari 16:9.',
            'youtube' => 'Video YouTube tetap 16:9 dan diletakkan utuh di tengah. Ada ruang kosong kiri-kanan agar bagian atas dan bawah tidak terpotong.',
        ],
        'split' => [
            'label' => 'Media dan kegiatan',
            'width' => 1420,
            'height' => 860,
            'note' => 'Kartu kiri saat panel kegiatan tampil. Lebih sempit dari Fokus media.',
            'youtube' => 'Video 16:9 hampir pas. Sedikit ruang atas-bawah jika dipilih utuh. Tanpa kegiatan, kartu sama dengan Fokus media.',
        ],
        'immersive' => [
            'label' => 'Layar penuh',
            'width' => 1920,
            'height' => 1080,
            'note' => 'Seluruh layar 16:9. Header dan jadwal menimpa tepi atas dan bawah.',
            'youtube' => 'Video 16:9 memenuhi layar. Jangan letakkan teks penting di tepi atas atau bawah.',
        ],
    ];

    public const PHOTO_STORED_MAX = 1920;

    public const PHOTO_UPLOAD_MAX_SIDE = 4096;

    public const ANNOUNCEMENT_LIMIT = 8;

    public const ANNOUNCEMENT_LENGTH = 280;

    public const DECORATIONS = ['none' => 'Tanpa ornamen', 'mosque' => 'Siluet Masjid', 'clouds' => 'Awan', 'ramadan' => 'Ramadan - Bulan & Lentera', 'qurban' => 'Idul Adha - Kubah & Geometri', 'desert' => 'Padang Pasir', 'stars' => 'Langit Berbintang'];

    public const DEFAULTS = [
        'title' => 'Display Masjid', 'theme' => 'emerald', 'accent' => '#c8a75b', 'layout' => 'focus',
        'announcement' => '', 'announcements' => [], 'prayer_location_id' => null, 'show_activities' => true, 'media_fit' => 'contain',
        'prayer_mode_enabled' => false, 'adhan_minutes' => 3, 'iqamah_minutes' => 10, 'prayer_minutes' => 15,
        'announcement_seconds' => 40, 'announcement_size' => 'large', 'transition_ms' => 800, 'decoration' => 'mosque',
    ];

    public function logoUrl(?string $url = null): string
    {
        if ($url === null) {
            $path = Setting::where('group', 'branding')->where('key', 'logo_path')->value('value');
            $url = is_string($path) ? '/storage/'.$path : '';
        }
        // Only existing public branding rasters; never fetch URLs or expose private files.
        if (preg_match('~^/storage/(branding/[A-Za-z0-9_-]+\.(?:png|jpe?g|webp|gif))$~D', $url, $match)
            && Storage::disk('public')->exists($match[1])) {
            return $url;
        }

        return '/images/logo-masjidnusa.png';
    }

    public function profile(): MosqueDisplay
    {
        // Fixed primary key makes concurrent first visits safe without multiple profiles.
        DB::table('mosque_displays')->insertOrIgnore([
            'id' => 1, 'draft' => json_encode(self::DEFAULTS), 'version' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $profile = MosqueDisplay::findOrFail(1);
        $profile->draft = array_replace(self::DEFAULTS, $profile->draft);

        return $profile;
    }

    public function locked(callable $callback): mixed
    {
        $this->profile();

        return DB::transaction(fn () => $callback(MosqueDisplay::whereKey(1)->lockForUpdate()->firstOrFail()));
    }

    public static function youtubeId(string $url): ?string
    {
        $parts = parse_url($url);
        if (! $parts || ($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $id = null;
        if ($host === 'youtu.be' && preg_match('~^/([A-Za-z0-9_-]{11})/?$~D', $path, $match)) {
            $id = $match[1];
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if ($path === '/watch') {
                parse_str($parts['query'] ?? '', $query);
                $id = $query['v'] ?? null;
            } elseif (preg_match('~^/(?:embed|shorts)/([A-Za-z0-9_-]{11})/?$~D', $path, $match)) {
                $id = $match[1];
            }
        }

        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/D', $id) ? $id : null;
    }

    public function storePhoto(UploadedFile $file): string
    {
        $size = @getimagesize($file->getPathname());
        if (! $size || $size[0] > 4096 || $size[1] > 4096 || $size[0] * $size[1] > 12000000 || ! in_array($size['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw ValidationException::withMessages(['photo' => 'Foto harus JPEG, PNG, atau WebP; maksimal 4096 piksel dan 12 megapiksel.']);
        }
        try {
            $image = (new ImageManager(new Driver))->read($file->getPathname());
            $image->scaleDown(width: 1920, height: 1920);
            $encoded = $image->toJpeg(quality: 85, strip: true);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages(['photo' => 'Foto tidak dapat diproses.']);
        }
        $name = Str::uuid().'.jpg';
        if (! Storage::disk('local')->put('mosque-display/'.$name, (string) $encoded)) {
            throw ValidationException::withMessages(['photo' => 'Foto gagal disimpan.']);
        }

        return $name;
    }

    public function snapshot(MosqueDisplay $profile): array
    {
        $settings = array_replace(self::DEFAULTS, array_intersect_key($profile->draft, self::DEFAULTS));
        $settings['logo_url'] = $this->logoUrl();
        $location = PrayerLocation::find($settings['prayer_location_id']);
        $settings['timezone'] = $location?->timezone() ?? config('app.timezone', 'Asia/Jakarta');
        $settings['prayer_location_name'] = $location?->city_name;
        $settings['items'] = $profile->items()->where('is_active', true)->get()->map(fn ($item) => [
            'id' => $item->id, 'type' => $item->type, 'title' => $item->title,
            'photo' => $item->photo, 'youtube_id' => $item->youtube_id,
            'duration_seconds' => $item->duration_seconds,
            'starts_at' => $item->starts_at?->toIso8601String(), 'ends_at' => $item->ends_at?->toIso8601String(),
        ])->all();
        $settings['activities'] = $settings['show_activities'] ? Activity::published()
            ->whereNotNull('start_date')->where(function ($query) {
                $query->where('end_date', '>=', now())->orWhere('start_date', '>=', now());
            })->orderBy('start_date')->limit(10)->get(['title', 'start_date', 'end_date'])
            ->map(fn ($activity) => ['title' => $activity->title, 'starts_at' => $activity->start_date->toIso8601String(), 'ends_at' => $activity->end_date?->toIso8601String()])->all() : [];

        return $settings;
    }

    public function publish(): void
    {
        $this->locked(function ($profile) {
            $snapshot = $this->snapshot($profile);
            foreach ($snapshot['items'] as $item) {
                if ($item['type'] === 'photo' && ! Storage::disk('local')->exists('mosque-display/'.$item['photo'])) {
                    throw ValidationException::withMessages(['playlist' => 'Foto hilang. Hapus item tersebut sebelum menerbitkan.']);
                }
            }
            $oldPhotos = array_column($profile->published['items'] ?? [], 'photo');
            $profile->update(['published' => $snapshot, 'version' => $profile->version + 1, 'published_at' => now()]);
            AuditLog::record('mosque_display.published', $profile, [], ['version' => $profile->version]);
            foreach (array_filter($oldPhotos) as $photo) {
                $this->deleteUnusedPhoto($profile, $photo);
            }
        });
    }

    public function deleteUnusedPhoto(MosqueDisplay $profile, string $photo): void
    {
        if (! $profile->items()->where('photo', $photo)->exists() && ! in_array($photo, array_column($profile->published['items'] ?? [], 'photo'), true)) {
            DB::afterCommit(fn () => $this->locked(function ($current) use ($photo) {
                if (! $current->items()->where('photo', $photo)->exists() && ! in_array($photo, array_column($current->published['items'] ?? [], 'photo'), true)) {
                    Storage::disk('local')->delete('mosque-display/'.$photo);
                }
            }));
        }
    }

    public function feed(array $snapshot, int $version, ?string $publishedAt, bool $preview = false): array
    {
        $snapshot = array_replace(self::DEFAULTS, $snapshot);
        $now = CarbonImmutable::now($snapshot['timezone']);
        $date = $now->toDateString();
        $prayers = $this->prayersForDate($snapshot, $now);
        $tomorrow = $this->prayersForDate($snapshot, $now->addDay());
        $yesterday = $this->prayersForDate($snapshot, $now->subDay());
        $timeline = collect(array_merge($yesterday, $prayers, $tomorrow))
            ->filter(fn ($prayer) => in_array($prayer['key'], ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'], true))
            ->sortBy('at')->values();
        $nextPrayer = $timeline->first(fn ($prayer) => CarbonImmutable::parse($prayer['at'])->gt($now));
        $previousPrayer = $timeline->filter(fn ($prayer) => CarbonImmutable::parse($prayer['at'])->lte($now))->last();
        $items = [];
        foreach ($snapshot['items'] as $item) {
            if (($item['starts_at'] && CarbonImmutable::parse($item['starts_at'])->gt($now)) || ($item['ends_at'] && CarbonImmutable::parse($item['ends_at'])->lte($now))) {
                continue;
            }
            if ($item['type'] === 'photo' && ! Storage::disk('local')->exists('mosque-display/'.$item['photo'])) {
                continue;
            }
            $items[] = [
                'id' => $item['id'], 'type' => $item['type'], 'title' => $item['title'], 'duration_seconds' => $item['duration_seconds'],
                'photo_url' => $item['type'] === 'photo' ? route($preview ? 'admin.media-it.mosque-display.photo' : 'mosque-display.photo', ['photo' => $item['photo']]) : null,
                'youtube_id' => $item['youtube_id'],
                'scheduled' => (bool) ($item['starts_at'] || $item['ends_at']),
            ];
        }

        return [
            'version' => $version, 'published_at' => $publishedAt, 'server_now' => $now->toIso8601String(),
            'title' => $snapshot['title'], 'theme' => $snapshot['theme'], 'accent' => $snapshot['accent'],
            'layout' => in_array($snapshot['layout'] ?? '', ['focus', 'split', 'immersive'], true) ? $snapshot['layout'] : 'focus',
            'media_fit' => in_array($snapshot['media_fit'], ['contain', 'cover'], true) ? $snapshot['media_fit'] : 'contain',
            'logo_url' => $this->logoUrl($snapshot['logo_url'] ?? ''),
            'announcements' => $announcements = $this->announcementLines($snapshot),
            'announcement' => implode('   ', $announcements),
            'timezone' => $snapshot['timezone'],
            'prayer_mode_enabled' => (bool) $snapshot['prayer_mode_enabled'],
            'adhan_minutes' => (int) $snapshot['adhan_minutes'], 'iqamah_minutes' => (int) $snapshot['iqamah_minutes'], 'prayer_minutes' => (int) $snapshot['prayer_minutes'],
            'announcement_seconds' => (int) $snapshot['announcement_seconds'], 'announcement_size' => $snapshot['announcement_size'],
            'transition_ms' => (int) $snapshot['transition_ms'], 'decoration' => $snapshot['decoration'],
            'prayer_date' => $date, 'prayer_location' => $snapshot['prayer_location_name'],
            'prayers' => $prayers, 'next_prayer' => $nextPrayer, 'previous_prayer' => $previousPrayer,
            'items' => $items, 'activities' => array_values(array_filter($snapshot['activities'], fn ($activity) => CarbonImmutable::parse($activity['ends_at'] ?? $activity['starts_at'])->gte($now))),
        ];
    }

    public function announcementLines(array $snapshot): array
    {
        $lines = [];
        foreach (is_array($snapshot['announcements'] ?? null) ? $snapshot['announcements'] : [] as $line) {
            if (! is_string($line)) {
                continue;
            }
            $line = trim(preg_replace('/\s+/u', ' ', $line) ?? '');
            if ($line === '') {
                continue;
            }
            $lines[] = mb_substr($line, 0, self::ANNOUNCEMENT_LENGTH);
            if (count($lines) >= self::ANNOUNCEMENT_LIMIT) {
                return $lines;
            }
        }
        if ($lines === [] && is_string($snapshot['announcement'] ?? null)) {
            $legacy = trim(preg_replace('/\s+/u', ' ', $snapshot['announcement']) ?? '');
            if ($legacy !== '') {
                $lines[] = mb_substr($legacy, 0, 2000);
            }
        }

        return $lines;
    }

    private function prayersForDate(array $settings, CarbonImmutable $day): array
    {
        if ($settings['prayer_location_id']) {
            $rows = PrayerTime::where('location_id', $settings['prayer_location_id'])->whereDate('date', $day->toDateString())->first()?->toScheduleArray() ?? [];
        } else {
            // Dated overrides replace recurring manual values for the same prayer.
            $rows = PrayerSchedule::where('type', 'daily')->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('date')->orWhereDate('date', $day->toDateString()))
                ->orderBy('id')->get(['prayer_name', 'time', 'date'])
                ->sortBy(fn ($row) => $row->date === null ? 0 : 1)
                ->map(fn ($row) => ['name' => $row->prayer_name, 'time' => $row->time])->all();
        }
        $names = [
            'imsak' => 'Imsak', 'subuh' => 'Subuh', 'terbit' => 'Terbit',
            'dzuhur' => 'Dzuhur', 'ashar' => 'Ashar', 'maghrib' => 'Maghrib', 'isya' => 'Isya',
        ];
        $aliases = [
            'fajr' => 'subuh', 'dhuhr' => 'dzuhur', 'zuhur' => 'dzuhur', 'dhuhur' => 'dzuhur',
            'asr' => 'ashar', 'asar' => 'ashar', 'isha' => 'isya', "isya'" => 'isya',
            'sunrise' => 'terbit', 'syuruk' => 'terbit',
        ];
        $result = [];
        foreach ($rows as $row) {
            $key = strtolower(trim($row['name']));
            $key = $aliases[$key] ?? $key;
            if (! isset($names[$key]) || ! preg_match('/^(\d{2}):(\d{2})(?::\d{2})?$/D', (string) $row['time'], $match) || (int) $match[1] > 23 || (int) $match[2] > 59) {
                continue;
            }
            $time = $match[1].':'.$match[2];
            $result[$key] = ['key' => $key, 'name' => $names[$key], 'time' => $time, 'at' => $day->setTime((int) $match[1], (int) $match[2])->startOfMinute()->toIso8601String()];
        }

        return array_values(array_filter(array_replace(array_fill_keys(array_keys($names), null), $result)));
    }
}
