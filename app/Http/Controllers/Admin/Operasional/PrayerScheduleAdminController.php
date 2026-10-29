<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\PrayerLocation;
use App\Models\PrayerSchedule;
use App\Models\PrayerTime;
use App\Services\PrayerScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrayerScheduleAdminController extends Controller
{
    public function __construct(private readonly PrayerScheduleService $prayerService)
    {
    }

    public function index(Request $request)
    {
        $locations = PrayerLocation::orderBy('province_name')->orderBy('city_name')->get();
        $selectedLocationId = $request->get('location_id', PrayerLocation::getDefault()?->id);
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $prayerTimes = collect();
        $selectedLocation = null;

        if ($selectedLocationId) {
            $selectedLocation = PrayerLocation::find($selectedLocationId);
            $prayerTimes = PrayerTime::where('location_id', $selectedLocationId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date')
                ->get();
        }

        $manualSchedules = PrayerSchedule::orderByRaw("FIELD(type, 'daily', 'jumat', 'special')")
            ->orderBy('time')
            ->get();

        return view('admin.operasional.prayer-schedule.index', compact(
            'locations', 'selectedLocation', 'selectedLocationId',
            'prayerTimes', 'month', 'year', 'manualSchedules'
        ));
    }

    public function create()
    {
        $schedule = null;
        return view('admin.operasional.prayer-schedule.form', compact('schedule'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prayer_name' => ['required', 'string', 'max:50'],
            'time'        => ['required', 'date_format:H:i'],
            'type'        => ['required', 'in:daily,jumat,special'],
            'notes'       => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        PrayerSchedule::create($data);
        return redirect()->route('admin.operasional.prayer-schedule.index')
            ->with('success', 'Jadwal sholat berhasil ditambahkan.');
    }

    public function edit(PrayerSchedule $prayerSchedule)
    {
        return view('admin.operasional.prayer-schedule.form', ['schedule' => $prayerSchedule]);
    }

    public function update(Request $request, PrayerSchedule $prayerSchedule)
    {
        $data = $request->validate([
            'prayer_name' => ['required', 'string', 'max:50'],
            'time'        => ['required', 'date_format:H:i'],
            'type'        => ['required', 'in:daily,jumat,special'],
            'notes'       => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $prayerSchedule->update($data);
        return redirect()->route('admin.operasional.prayer-schedule.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(PrayerSchedule $prayerSchedule)
    {
        $prayerSchedule->delete();
        return back()->with('success', 'Jadwal dihapus.');
    }

    // ===== LOCATION MANAGEMENT =====

    public function locations()
    {
        $locations = PrayerLocation::withCount('prayerTimes')
            ->orderBy('province_name')
            ->orderBy('city_name')
            ->get();
        return view('admin.operasional.prayer-schedule.locations', compact('locations'));
    }

    public function storeLocation(Request $request)
    {
        $data = $request->validate([
            'province_name' => ['required', 'string', 'max:100'],
            'city_name'     => ['required', 'string', 'max:100'],
            'latitude'      => ['required', 'numeric', 'between:-90,90'],
            'longitude'     => ['required', 'numeric', 'between:-180,180'],
            'is_default'    => ['boolean'],
        ]);

        $data['is_default']    = $request->boolean('is_default');
        $data['equran_province'] = $data['province_name'];
        $data['equran_city']     = $data['city_name'];
        $data['province_id']   = '0';
        $data['city_id']       = '0';

        $location = PrayerLocation::updateOrCreate(
            ['equran_province' => $data['province_name'], 'equran_city' => $data['city_name']],
            $data
        );

        if ($data['is_default']) {
            $location->setAsDefault();
        }

        return back()->with('success', "Lokasi {$location->full_name} berhasil ditambahkan.");
    }

    public function destroyLocation(PrayerLocation $location)
    {
        $name = $location->full_name;
        $location->delete();
        return back()->with('success', "Lokasi {$name} berhasil dihapus.");
    }

    public function setDefaultLocation(PrayerLocation $location)
    {
        $location->setAsDefault();
        return back()->with('success', "Lokasi {$location->full_name} dijadikan default.");
    }

    // ===== API PROXY (AJAX) =====

    public function apiProvinces()
    {
        try {
            $provinces = $this->prayerService->getProvinces();
            return response()->json(['data' => $provinces]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function apiCities(Request $request)
    {
        $request->validate(['province' => ['required', 'string']]);

        try {
            $cities = $this->prayerService->getCities($request->province);
            return response()->json(['data' => $cities]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    // ===== IMPORT =====

    public function importForm()
    {
        $locations = PrayerLocation::orderBy('province_name')->orderBy('city_name')->get();
        return view('admin.operasional.prayer-schedule.import', compact('locations'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'location_id' => ['required', 'exists:prayer_locations,id'],
            'month'       => ['required', 'integer', 'between:1,12'],
            'year'        => ['required', 'integer', 'between:2020,2080'],
            'csv_file'    => ['required', 'file', 'mimes:csv,txt', 'max:1024'],
        ]);

        $location = PrayerLocation::findOrFail($request->location_id);
        $month    = (int) $request->month;
        $year     = (int) $request->year;

        try {
            $file    = $request->file('csv_file');
            $content = file_get_contents($file->getPathname());
            $lines   = array_filter(explode("\n", $content), fn($l) => trim($l) !== '');

            if (count($lines) < 2) {
                return back()->with('error', 'File CSV tidak valid atau kosong.');
            }

            $header = str_getcsv(array_shift($lines), ';');
            $header = array_map(fn($h) => strtolower(trim($h)), $header);

            $columnMap = $this->detectColumns($header);

            if (!$columnMap) {
                return back()->with('error', 'Format kolom CSV tidak dikenali. Pastikan menggunakan format Kemenag (Tanggal;Imsak;Subuh;Terbit;Dhuha;Dzuhur;Ashar;Maghrib;Isya).');
            }

            $imported = 0;
            DB::beginTransaction();

            foreach ($lines as $line) {
                $row = str_getcsv($line, ';');
                if (count($row) < count($columnMap)) continue;

                $day = (int) trim($row[$columnMap['tanggal']] ?? '');
                if ($day < 1 || $day > 31) continue;

                try {
                    $date = Carbon::createFromDate($year, $month, $day)->toDateString();
                } catch (\Exception) {
                    continue;
                }

                $timeData = ['location_id' => $location->id, 'date' => $date];

                foreach (['imsak', 'subuh', 'terbit', 'dhuha', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $prayer) {
                    if (isset($columnMap[$prayer], $row[$columnMap[$prayer]])) {
                        $timeVal = trim($row[$columnMap[$prayer]]);
                        if (preg_match('/^\d{2}:\d{2}$/', $timeVal)) {
                            $timeData[$prayer] = $timeVal;
                        }
                    }
                }

                if (empty($timeData['subuh']) || empty($timeData['dzuhur'])) continue;

                PrayerTime::updateOrCreate(
                    ['location_id' => $location->id, 'date' => $date],
                    $timeData
                );
                $imported++;
            }

            DB::commit();

            return redirect()->route('admin.operasional.prayer-schedule.index', [
                'location_id' => $location->id,
                'month'       => $month,
                'year'        => $year,
            ])->with('success', "Berhasil mengimpor {$imported} jadwal sholat untuk {$location->full_name} ({$this->getMonthName($month)} {$year}).");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengimpor: ' . $e->getMessage());
        }
    }

    public function fetchFromApi(Request $request)
    {
        $request->validate([
            'location_id' => ['required', 'exists:prayer_locations,id'],
            'month'       => ['required', 'integer', 'between:1,12'],
            'year'        => ['required', 'integer', 'between:2020,2080'],
        ]);

        $location = PrayerLocation::findOrFail($request->location_id);
        $month    = (int) $request->month;
        $year     = (int) $request->year;

        try {
            $count = $this->prayerService->fetchAndSave($location, $month, $year);

            return redirect()->route('admin.operasional.prayer-schedule.index', [
                'location_id' => $location->id,
                'month'       => $month,
                'year'        => $year,
            ])->with('success', "Berhasil mengambil {$count} jadwal dari Aladhan API untuk {$location->full_name} ({$this->getMonthName($month)} {$year}).");

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengambil data dari API: ' . $e->getMessage());
        }
    }

    public function destroyMonth(Request $request)
    {
        $request->validate([
            'location_id' => ['required', 'exists:prayer_locations,id'],
            'month'       => ['required', 'integer', 'between:1,12'],
            'year'        => ['required', 'integer', 'between:2020,2080'],
        ]);

        $deleted = PrayerTime::where('location_id', $request->location_id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->delete();

        return back()->with('success', "Berhasil menghapus {$deleted} jadwal sholat.");
    }

    // ===== HELPERS =====

    private function detectColumns(array $header): ?array
    {
        $map = [];
        $aliases = [
            'tanggal' => ['tanggal', 'tgl', 'date', 'day', 'hari'],
            'imsak'   => ['imsak', 'imsyak'],
            'subuh'   => ['subuh', 'shubuh', 'fajr'],
            'terbit'  => ['terbit', 'syuruq', 'sunrise'],
            'dhuha'   => ['dhuha', 'duha'],
            'dzuhur'  => ['dzuhur', 'dhuhur', 'zuhur', 'duhur', 'zuhr'],
            'ashar'   => ['ashar', 'asar', 'asr'],
            'maghrib' => ['maghrib', 'magrib', 'magreb'],
            'isya'    => ['isya', "isya'", 'isha'],
        ];

        foreach ($aliases as $key => $names) {
            foreach ($header as $idx => $col) {
                $col = preg_replace('/[^a-z]/', '', $col);
                foreach ($names as $name) {
                    if (str_contains($col, $name)) {
                        $map[$key] = $idx;
                        break 2;
                    }
                }
            }
        }

        if (!isset($map['tanggal'])) {
            $map['tanggal'] = 0;
        }

        return (isset($map['subuh']) && isset($map['dzuhur'])) ? $map : null;
    }

    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $months[$month] ?? '';
    }
}
