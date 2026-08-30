<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ManagementHistory;
use App\Models\MosqueProfile;

class MosqueInfoController extends Controller
{
    public function index()
    {
        $mosque = MosqueProfile::with('histories')->first();
        return view('public.info-masjid.index', compact('mosque'));
    }

    public function history()
    {
        $mosque = MosqueProfile::with(['histories' => fn($q) => $q->orderBy('event_date')])->first();
        return view('public.info-masjid.sejarah', compact('mosque'));
    }

    public function management()
    {
        $managements = ManagementHistory::orderByDesc('start_year')->get();
        return view('public.info-masjid.kepengurusan', compact('managements'));
    }

    public function organization()
    {
        $current = ManagementHistory::where('is_current', true)->with('organizationStructures.children')->first();
        $past = ManagementHistory::where('is_current', false)->orderByDesc('start_year')->with('organizationStructures')->get();
        return view('public.info-masjid.organisasi', compact('current', 'past'));
    }
}
