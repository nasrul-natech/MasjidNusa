<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Support\TableSort;
use Illuminate\Http\Request;

class DonationAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Donation::with(['user', 'campaign', 'latestTransaction']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('donation_code', 'like', "%{$request->search}%")
                  ->orWhere('donor_name', 'like', "%{$request->search}%")
                  ->orWhere('donor_email', 'like', "%{$request->search}%");
            });
        }

        TableSort::apply($query, ['donation_code', 'donor_name', 'amount', 'status', 'created_at'], 'created_at', 'desc');

        $donations = $query->paginate(20)->withQueryString();

        return view('admin.keuangan.donations.index', compact('donations'));
    }

    public function show(Donation $donation)
    {
        $donation->load(['user', 'campaign', 'transactions.proof', 'transactions.paymentMethod']);
        return view('admin.keuangan.donations.show', compact('donation'));
    }
}
