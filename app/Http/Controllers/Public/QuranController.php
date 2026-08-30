<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\QuranBookmark;
use App\Models\QuranKhatamProgram;
use App\Models\QuranKhatamProgress;
use App\Models\Surah;
use App\Services\QuranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuranController extends Controller
{
    public function __construct(private readonly QuranService $quranService)
    {
    }

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'surah');
        $surahs = $this->quranService->getSurahList();
        $juzInfo = $this->quranService->getJuzInfo();

        $bookmarks = collect();
        $lastRead = null;
        $khatamPrograms = collect();

        if (Auth::check()) {
            $bookmarks = Auth::user()->quranBookmarks()
                ->with('surah')
                ->where('is_last_read', false)
                ->latest()
                ->get();

            $lastRead = Auth::user()->quranBookmarks()
                ->with('surah')
                ->whereHas('surah')
                ->where('is_last_read', true)
                ->first();

            $khatamPrograms = Auth::user()->khatamPrograms()
                ->withCount('progress')
                ->latest()
                ->get();
        }

        return view('public.quran.index', compact(
            'tab', 'surahs', 'juzInfo', 'bookmarks', 'lastRead', 'khatamPrograms'
        ) + [
            'quranReady' => $surahs->isNotEmpty(),
            'quranLocal' => $this->quranService->isLocalDataAvailable(),
        ]);
    }

    public function surah(int $number)
    {
        $surah = $this->quranService->getSurah($number);
        if (!$surah) {
            abort(404);
        }

        $verses = $this->quranService->getAllSurahVerses($surah->id);
        $surahs = $this->quranService->getSurahList();
        $pageGroups = $verses->groupBy('page_number')->sortKeys();

        $pageMetaMap = $pageGroups->map(fn ($pageVerses) => [
            'surah_number' => $surah->number,
            'verse_from'   => $pageVerses->first()->verse_number,
            'verse_to'     => $pageVerses->last()->verse_number,
            'juz_number'   => $pageVerses->first()->juz_number,
        ]);

        $prevSurah = $number > 1 ? $this->quranService->getSurah($number - 1) : null;
        $nextSurah = $number < 114 ? $this->quranService->getSurah($number + 1) : null;

        $bookmarkedVerses = [];
        $activeKhatam = null;
        if (Auth::check()) {
            $bookmarkedVerses = Auth::user()->quranBookmarks()
                ->where('surah_id', $surah->id)
                ->where('is_last_read', false)
                ->pluck('verse_number')
                ->toArray();

            $activeKhatam = Auth::user()->khatamPrograms()
                ->where('status', 'active')
                ->latest('id')
                ->first();
        }

        return view('public.quran.surah', compact(
            'surah', 'verses', 'surahs', 'prevSurah', 'nextSurah',
            'bookmarkedVerses', 'activeKhatam', 'pageMetaMap'
        ));
    }

    public function juz(int $number)
    {
        if ($number < 1 || $number > 30) {
            abort(404);
        }

        $verses = $this->quranService->getJuzVerses($number);
        $groupedVerses = $verses->groupBy('surah_id');
        $pageGroups = $verses->groupBy('page_number')->sortKeys();

        $pageMetaMap = $pageGroups->map(fn ($pageVerses) => [
            'surah_number' => $pageVerses->first()->surah->number,
            'verse_from'   => $pageVerses->first()->verse_number,
            'verse_to'     => $pageVerses->last()->verse_number,
            'juz_number'   => $pageVerses->first()->juz_number,
        ]);

        $bookmarkedVerses = [];
        $activeKhatam = null;
        if (Auth::check()) {
            $bookmarkedVerses = Auth::user()->quranBookmarks()
                ->where('is_last_read', false)
                ->get()
                ->map(fn ($b) => $b->surah_id . ':' . $b->verse_number)
                ->toArray();

            $activeKhatam = Auth::user()->khatamPrograms()
                ->where('status', 'active')
                ->latest('id')
                ->first();
        }

        return view('public.quran.juz', compact(
            'number', 'groupedVerses', 'bookmarkedVerses', 'activeKhatam', 'pageMetaMap'
        ));
    }

    public function verse(int $surahNumber, int $verseNumber)
    {
        $verse = $this->quranService->getVerse($surahNumber, $verseNumber);
        if (!$verse) {
            abort(404);
        }

        return response()->json([
            'verse' => $verse,
            'surah' => $verse->surah,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $lang = $request->get('lang', 'id');
        $results = collect();

        if (strlen($query) >= 3) {
            $results = $this->quranService->search($query, $lang);
        }

        if ($request->wantsJson()) {
            return response()->json(['results' => $results]);
        }

        $surahs = $this->quranService->getSurahList();

        return view('public.quran.index', [
            'tab' => 'search',
            'surahs' => $surahs,
            'juzInfo' => $this->quranService->getJuzInfo(),
            'bookmarks' => collect(),
            'lastRead' => null,
            'khatamPrograms' => collect(),
            'searchQuery' => $query,
            'searchResults' => $results,
        ]);
    }

    public function bookmark(Request $request)
    {
        $request->validate([
            'surah_id' => 'required|exists:surahs,id',
            'verse_number' => 'required|integer|min:1',
            'label' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $existing = $user->quranBookmarks()
            ->where('surah_id', $request->surah_id)
            ->where('verse_number', $request->verse_number)
            ->where('is_last_read', false)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['bookmarked' => false]);
        }

        QuranBookmark::create([
            'user_id' => $user->id,
            'surah_id' => $request->surah_id,
            'verse_number' => $request->verse_number,
            'label' => $request->label,
            'is_last_read' => false,
        ]);

        return response()->json(['bookmarked' => true]);
    }

    public function setLastRead(Request $request)
    {
        $request->validate([
            'surah_id' => 'required|exists:surahs,id',
            'verse_number' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        $user->quranBookmarks()->where('is_last_read', true)->delete();

        QuranBookmark::create([
            'user_id' => $user->id,
            'surah_id' => $request->surah_id,
            'verse_number' => $request->verse_number,
            'is_last_read' => true,
        ]);

        return response()->json(['success' => true]);
    }

    public function khatamIndex()
    {
        $programs = Auth::user()->khatamPrograms()
            ->with(['progress' => fn($q) => $q->orderByDesc('read_date')->limit(7)])
            ->latest()
            ->get();

        return view('public.quran.khatam', compact('programs'));
    }

    public function khatamStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'target_days' => 'required|integer|in:30,60,90,180,365',
        ]);

        $activeCount = Auth::user()->khatamPrograms()->active()->count();
        if ($activeCount >= 3) {
            return back()->with('error', 'Maksimal 3 program khatam aktif bersamaan.');
        }

        $targetDays = (int) $validated['target_days'];
        $startDate = now()->startOfDay();

        QuranKhatamProgram::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'target_days' => $targetDays,
            'start_date' => $startDate,
            'target_date' => $startDate->copy()->addDays($targetDays),
        ]);

        return back()->with('success', 'Program khatam berhasil dibuat!');
    }

    public function khatamShow(int $id)
    {
        $program = Auth::user()->khatamPrograms()
            ->with(['progress' => fn($q) => $q->orderByDesc('read_date')])
            ->findOrFail($id);

        $surahs = $this->quranService->getSurahList();

        return view('public.quran.khatam-detail', compact('program', 'surahs'));
    }

    public function khatamProgress(Request $request, int $id)
    {
        $program = Auth::user()->khatamPrograms()->findOrFail($id);

        if ($program->status !== 'active') {
            return response()->json(['error' => 'Program sudah tidak aktif.'], 422);
        }

        $request->validate([
            'surah_number' => 'required|integer|min:1|max:114',
            'verse_from'   => 'required|integer|min:1',
            'verse_to'     => 'required|integer|min:1|gte:verse_from',
            'juz_number'   => 'required|integer|min:1|max:30',
            'pages_read'   => 'required|integer|min:1|max:20',
        ]);

        $duplicate = QuranKhatamProgress::where('program_id', $program->id)
            ->where('read_date', now()->toDateString())
            ->where('surah_number', $request->surah_number)
            ->where('verse_from', $request->verse_from)
            ->where('verse_to', $request->verse_to)
            ->exists();

        if ($duplicate) {
            $program = $program->fresh();

            return response()->json([
                'success'          => true,
                'duplicate'        => true,
                'progress_percent' => $program->progress_percent,
                'total_pages_read' => $program->total_pages_read,
            ]);
        }

        QuranKhatamProgress::create([
            'program_id' => $program->id,
            'surah_number' => $request->surah_number,
            'verse_from' => $request->verse_from,
            'verse_to' => $request->verse_to,
            'juz_number' => $request->juz_number,
            'pages_read' => $request->pages_read,
            'read_date' => now()->toDateString(),
        ]);

        $program = $program->fresh();

        if ($program->total_pages_read >= 604) {
            $program->update(['status' => 'completed']);
            $program = $program->fresh();
        }

        return response()->json([
            'success' => true,
            'progress_percent' => $program->progress_percent,
            'total_pages_read' => $program->total_pages_read,
        ]);
    }
}
