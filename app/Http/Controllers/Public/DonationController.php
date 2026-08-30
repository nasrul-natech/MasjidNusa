<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\DonationCategory;

class DonationController extends Controller
{
    public function index()
    {
        $categories = DonationCategory::all();
        $campaigns = DonationCampaign::active()->with('category')->get();
        $bankAccounts = BankAccount::active()->get();

        $recentDonations = Donation::whereIn('status', ['verified', 'paid'])
            ->with('campaign')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('public.donasi.index', compact('categories', 'campaigns', 'bankAccounts', 'recentDonations'));
    }

    public function show(string $slug)
    {
        $campaign = DonationCampaign::where('slug', $slug)->active()->with(['category'])->firstOrFail();

        $donors = $campaign->donations()
            ->whereIn('status', ['verified', 'paid'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $bankAccounts = BankAccount::active()->get();

        return view('public.donasi.show', compact('campaign', 'donors', 'bankAccounts'));
    }
}
