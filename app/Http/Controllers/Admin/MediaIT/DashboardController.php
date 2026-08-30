<?php

namespace App\Http\Controllers\Admin\MediaIT;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Article;
use App\Models\Banner;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'upcoming_activities' => Activity::published()->upcoming()->count(),
            'total_articles' => Article::published()->count(),
            'active_banners' => Banner::active()->count(),
            'draft_content' => Activity::where('status', 'draft')->count() + Article::where('status', 'draft')->count(),
        ];

        $recentActivities = Activity::with('category')->orderByDesc('created_at')->limit(5)->get();
        $recentArticles = Article::with('author')->orderByDesc('created_at')->limit(5)->get();

        return view('admin.media-it.dashboard', compact('stats', 'recentActivities', 'recentArticles'));
    }
}
