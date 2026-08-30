<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'upcoming');
        $category = $request->get('category');
        $month = $request->get('month', now()->format('Y-m'));

        $query = Activity::with('category')->published();

        if ($filter === 'past') {
            $query->where('start_date', '<', now())->orderByDesc('start_date');
        } else {
            $query->where('start_date', '>=', now()->startOfDay())->orderBy('start_date');
        }

        if ($category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $category));
        }

        $activities = $query->paginate(9);
        $categories = ActivityCategory::all();

        // Calendar data for current month (grouped by date for multi-event days)
        [$year, $monthNum] = explode('-', $month);
        $year = (int) $year;
        $monthNum = (int) $monthNum;
        $calendarActivities = Activity::published()
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $monthNum)
            ->with('category')
            ->orderBy('start_date')
            ->get()
            ->groupBy(fn ($a) => $a->start_date->format('Y-m-d'));

        $calendarData = $calendarActivities->map(fn ($items) => $items->map(fn ($a) => [
            'title' => $a->title,
            'slug' => $a->slug,
            'time' => $a->start_date->format('H:i'),
            'date_label' => $a->start_date->locale('id')->isoFormat('dddd, D MMMM Y'),
            'location' => $a->location,
            'category' => $a->category?->name,
            'banner' => $a->banner_url,
            'url' => route('kegiatan.show', $a->slug),
        ])->values())->toArray();

        return view('public.kegiatan.index', compact(
            'activities', 'categories', 'filter', 'calendarActivities', 'calendarData', 'month', 'year', 'monthNum'
        ));
    }

    public function show(string $slug)
    {
        $activity = Activity::where('slug', $slug)->published()->with(['category', 'galleries', 'stories'])->firstOrFail();

        $related = Activity::published()
            ->where('id', '!=', $activity->id)
            ->where('category_id', $activity->category_id)
            ->limit(3)
            ->get();

        return view('public.kegiatan.show', compact('activity', 'related'));
    }
}
