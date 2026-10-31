<?php

namespace App\Http\Controllers\Admin\MediaIT;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MosqueDisplayItem;
use App\Models\PrayerLocation;
use App\Services\MosqueDisplayService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MosqueDisplayController extends Controller
{
    public function __construct(private MosqueDisplayService $display) {}

    public function index()
    {
        return response()->view('admin.media-it.mosque-display.index', ['profile' => $this->display->profile()->load('items'), 'locations' => PrayerLocation::orderBy('city_name')->get()])->header('Cache-Control', 'no-store, private');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            '_tab' => ['sometimes', 'required', Rule::in(['identity', 'layout', 'content', 'prayer'])],
            'title' => ['required', 'string', 'max:160'], 'theme' => ['required', Rule::in(array_keys(MosqueDisplayService::THEMES))],
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/D'], 'announcement' => ['nullable', 'string', 'max:2000'],
            'announcements' => ['nullable', 'array', 'max:'.MosqueDisplayService::ANNOUNCEMENT_LIMIT],
            'announcements.*' => ['nullable', 'string', 'max:'.MosqueDisplayService::ANNOUNCEMENT_LENGTH],
            'layout' => ['sometimes', 'required', 'in:focus,split,immersive'],
            'media_fit' => ['sometimes', 'required', 'in:contain,cover'],
            'prayer_location_id' => ['nullable', 'integer', 'exists:prayer_locations,id'], 'show_activities' => ['nullable', 'boolean'],
            'prayer_mode_enabled' => ['sometimes', 'required', 'boolean'],
            'adhan_minutes' => ['sometimes', 'required', 'integer', 'min:1', 'max:15'],
            'iqamah_minutes' => ['sometimes', 'required', 'integer', 'min:0', 'max:30'],
            'prayer_minutes' => ['sometimes', 'required', 'integer', 'min:1', 'max:60'],
            'announcement_seconds' => ['sometimes', 'required', 'integer', 'min:10', 'max:180'],
            'announcement_size' => ['sometimes', 'required', Rule::in(['normal', 'large', 'extra'])],
            'transition_ms' => ['sometimes', 'required', 'integer', 'min:200', 'max:2000'],
            'decoration' => ['sometimes', 'required', Rule::in(array_keys(MosqueDisplayService::DECORATIONS))],
        ]);
        $tab = $data['_tab'] ?? 'identity';
        unset($data['_tab']);
        if ($request->exists('announcements')) {
            $data['announcements'] = $this->display->announcementLines(['announcements' => $data['announcements'] ?? []]);
            $data['announcement'] = implode("\n", $data['announcements']);
        } else {
            $data['announcement'] = $data['announcement'] ?? '';
            unset($data['announcements']);
        }
        $data['prayer_location_id'] = $data['prayer_location_id'] ?? null;
        $data['show_activities'] = $request->boolean('show_activities');
        $this->display->locked(function ($profile) use ($data) {
            $profile->update(['draft' => array_replace(MosqueDisplayService::DEFAULTS, $profile->draft, $data)]);
            AuditLog::record('mosque_display.draft_updated', $profile);
        });

        return redirect()->to(route('admin.media-it.mosque-display.index').'#'.$tab)->with('success', 'Draft disimpan. TV belum berubah sampai diterbitkan.');
    }

    private function itemData(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'type' => [$creating ? 'required' : 'sometimes', 'in:photo,youtube'],
            'title' => ['required', 'string', 'max:160'], 'youtube_url' => ['nullable', 'string', 'max:300'],
            'photo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000'],
            'duration_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'], 'ends_at' => array_filter(['nullable', 'date_format:Y-m-d\TH:i', $request->filled('starts_at') ? 'after:starts_at' : null]),
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        unset($data['photo']);

        return $data;
    }

    public function storeItem(Request $request)
    {
        $data = $this->itemData($request, true);
        $photo = null;
        if ($data['type'] === 'photo') {
            if (! $request->hasFile('photo')) {
                throw ValidationException::withMessages(['photo' => 'Foto wajib diunggah.']);
            }
            $photo = $this->display->storePhoto($request->file('photo'));
        } else {
            $data['youtube_id'] = MosqueDisplayService::youtubeId($data['youtube_url'] ?? '');
            if (! $data['youtube_id']) {
                throw ValidationException::withMessages(['youtube_url' => 'Gunakan URL HTTPS YouTube dengan ID video sah.']);
            }
        }
        unset($data['youtube_url']);
        try {
            $this->display->locked(function ($profile) use ($data, $photo) {
                if ($profile->items()->count() >= 50) {
                    throw ValidationException::withMessages(['playlist' => 'Maksimal 50 item playlist.']);
                }
                $item = $profile->items()->create($this->dates($profile, $data) + ['photo' => $photo]);
                AuditLog::record('mosque_display.item_created', $item);
            });
        } catch (\Throwable $exception) {
            if ($photo) {
                Storage::disk('local')->delete('mosque-display/'.$photo);
            }
            throw $exception;
        }

        return redirect()->to(route('admin.media-it.mosque-display.index').'#media')->with('success', 'Item ditambahkan ke draft.');
    }

    private function dates($profile, array $data): array
    {
        $timezone = PrayerLocation::find($profile->draft['prayer_location_id'] ?? null)?->timezone() ?? config('app.timezone');
        foreach (['starts_at', 'ends_at'] as $field) {
            $data[$field] = empty($data[$field]) ? null : CarbonImmutable::createFromFormat('Y-m-d\TH:i', $data[$field], $timezone)->setTimezone(config('app.timezone'));
        }

        return $data;
    }

    public function updateItem(Request $request, MosqueDisplayItem $item)
    {
        $data = $this->itemData($request, false);
        unset($data['type'], $data['youtube_url']);
        $this->display->locked(function ($profile) use ($item, $data) {
            $item = $profile->items()->findOrFail($item->id);
            $item->update($this->dates($profile, $data));
            AuditLog::record('mosque_display.item_updated', $item);
        });

        return redirect()->to(route('admin.media-it.mosque-display.index').'#media')->with('success', 'Item draft diperbarui.');
    }

    public function destroyItem(MosqueDisplayItem $item)
    {
        $this->display->locked(function ($profile) use ($item) {
            $item = $profile->items()->findOrFail($item->id);
            $photo = $item->photo;
            AuditLog::record('mosque_display.item_deleted', $item);
            $item->delete();
            if ($photo) {
                $this->display->deleteUnusedPhoto($profile, $photo);
            }
        });

        return redirect()->to(route('admin.media-it.mosque-display.index').'#media')->with('success', 'Item dihapus dari draft. Foto terbit dipertahankan sampai publikasi berikutnya.');
    }

    public function publish()
    {
        $this->display->publish();

        return redirect()->to(route('admin.media-it.mosque-display.index').'#publish')->with('success', 'Snapshot TV diterbitkan.');
    }

    public function preview(Request $request)
    {
        $request->validate(['phase' => ['nullable', Rule::in(['adhan', 'iqamah', 'prayer'])]]);

        return response()->view('mosque-display.screen', ['preview' => true, 'previewPhase' => $request->input('phase', ''), 'feedUrl' => route('admin.media-it.mosque-display.preview-feed')])->header('Cache-Control', 'no-store, private');
    }

    public function previewFeed()
    {
        $profile = $this->display->profile();

        return response()->json($this->display->feed($this->display->snapshot($profile), $profile->version, null, true))->header('Cache-Control', 'no-store, private');
    }

    public function photo(string $photo)
    {
        abort_unless($this->display->profile()->items()->where('photo', $photo)->exists(), 404);
        abort_unless(Storage::disk('local')->exists('mosque-display/'.$photo), 404);

        return response()->file(Storage::disk('local')->path('mosque-display/'.$photo), ['Cache-Control' => 'no-store, private', 'Content-Type' => 'image/jpeg', 'X-Content-Type-Options' => 'nosniff']);
    }
}
