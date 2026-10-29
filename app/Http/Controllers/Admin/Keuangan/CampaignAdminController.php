<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\DonationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CampaignAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DonationCampaign::with('category')->withCount('donations');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $campaigns = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $categories = DonationCategory::orderBy('name')->get();

        $stats = [
            'total' => DonationCampaign::count(),
            'active' => DonationCampaign::where('status', 'active')->count(),
            'featured' => DonationCampaign::where('is_featured', true)->count(),
            'total_collected' => DonationCampaign::sum('collected_amount'),
        ];

        return view('admin.keuangan.campaigns.index', compact('campaigns', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = DonationCategory::orderBy('name')->get();
        $campaign = null;
        $verifiedSum = $verifiedCount = $pendingSum = $pendingCount = 0;
        $isDivergent = false;
        $recentDonations = collect();
        return view('admin.keuangan.campaigns.form', compact(
            'campaign', 'categories',
            'verifiedSum', 'verifiedCount', 'pendingSum', 'pendingCount',
            'isDivergent', 'recentDonations'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'target_amount' => ['required', 'integer', 'min:10000'],
            'category_id' => ['nullable', 'exists:donation_categories,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,active,selesai,arsip'],
            'is_featured' => ['boolean'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . time();
        $data['collected_amount'] = 0;
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('campaigns', 'public');
        }

        DonationCampaign::create($data);

        return redirect()->route('admin.keuangan.campaigns.index')
            ->with('success', 'Program donasi berhasil dibuat.');
    }

    public function show(DonationCampaign $campaign)
    {
        $campaign->load('category');
        $donations = $campaign->donations()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        $donationStats = [
            'total_count' => $campaign->donations()->count(),
            'verified_count' => $campaign->donations()->whereIn('status', ['verified', 'paid'])->count(),
            'pending_count' => $campaign->donations()->where('status', 'waiting_verification')->count(),
            'total_amount' => $campaign->donations()->whereIn('status', ['verified', 'paid'])->sum('amount'),
        ];

        return view('admin.keuangan.campaigns.show', compact('campaign', 'donations', 'donationStats'));
    }

    public function edit(DonationCampaign $campaign)
    {
        $categories = DonationCategory::orderBy('name')->get();

        $verifiedSum   = (int) $campaign->donations()->whereIn('status', ['verified', 'paid'])->sum('amount');
        $verifiedCount = $campaign->donations()->whereIn('status', ['verified', 'paid'])->count();
        $pendingSum    = (int) $campaign->donations()->where('status', 'waiting_verification')->sum('amount');
        $pendingCount  = $campaign->donations()->where('status', 'waiting_verification')->count();
        $isDivergent   = $campaign->collected_amount !== $verifiedSum;
        $recentDonations = $campaign->donations()
            ->whereIn('status', ['verified', 'paid'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get(['id', 'donor_name', 'amount', 'status', 'updated_at', 'is_anonymous']);

        return view('admin.keuangan.campaigns.form', compact(
            'campaign', 'categories',
            'verifiedSum', 'verifiedCount', 'pendingSum', 'pendingCount',
            'isDivergent', 'recentDonations'
        ));
    }

    public function update(Request $request, DonationCampaign $campaign)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'target_amount' => ['required', 'integer', 'min:10000'],
            'category_id' => ['nullable', 'exists:donation_categories,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,active,selesai,arsip'],
            'is_featured' => ['boolean'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('banner')) {
            if ($campaign->banner) {
                Storage::disk('public')->delete($campaign->banner);
            }
            $data['banner'] = $request->file('banner')->store('campaigns', 'public');
        }

        $campaign->update($data);

        return redirect()->route('admin.keuangan.campaigns.index')
            ->with('success', 'Program donasi berhasil diperbarui.');
    }

    public function destroy(DonationCampaign $campaign)
    {
        $donationCount = $campaign->donations()->count();
        if ($donationCount > 0) {
            return back()->with('error', "Program tidak bisa dihapus karena sudah memiliki {$donationCount} donasi masuk. Arsipkan saja.");
        }

        if ($campaign->banner) {
            Storage::disk('public')->delete($campaign->banner);
        }

        $title = $campaign->title;
        $campaign->delete();

        return back()->with('success', "Program \"{$title}\" berhasil dihapus.");
    }

    public function toggleFeatured(DonationCampaign $campaign)
    {
        $campaign->update(['is_featured' => !$campaign->is_featured]);
        $status = $campaign->is_featured ? 'ditampilkan di halaman utama' : 'disembunyikan dari halaman utama';
        return back()->with('success', "Program \"{$campaign->title}\" berhasil {$status}.");
    }

    public function adjustAmount(Request $request, DonationCampaign $campaign)
    {
        $data = $request->validate([
            'collected_amount' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $before = $campaign->collected_amount;
        $campaign->update(['collected_amount' => $data['collected_amount']]);

        AuditLog::record('campaign.amount_adjusted', $campaign, [
            'collected_amount' => $before,
        ], [
            'collected_amount' => (int) $data['collected_amount'],
            'reason' => $data['reason'],
        ]);

        return back()->with('success', 'Jumlah terkumpul berhasil disesuaikan.');
    }

    public function syncAmount(DonationCampaign $campaign)
    {
        $actualSum = $campaign->donations()
            ->whereIn('status', ['verified', 'paid'])
            ->sum('amount');

        $before = $campaign->collected_amount;
        $campaign->update(['collected_amount' => $actualSum]);

        AuditLog::record('campaign.amount_synced', $campaign, [
            'collected_amount' => $before,
        ], [
            'collected_amount' => (int) $actualSum,
            'reason' => 'Sinkron dari total donasi terverifikasi',
        ]);

        $diff = $actualSum - $before;
        $msg = $diff === 0
            ? 'Nilai sudah sinkron, tidak ada perubahan.'
            : 'Nilai disinkronkan: ' . ($diff > 0 ? '+' : '') . 'Rp ' . number_format($diff, 0, ',', '.') . ' (dari Rp ' . number_format($before, 0, ',', '.') . ' → Rp ' . number_format($actualSum, 0, ',', '.') . ')';

        return back()->with('success', $msg);
    }
}
