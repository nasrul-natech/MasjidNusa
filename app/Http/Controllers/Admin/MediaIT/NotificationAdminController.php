<?php

namespace App\Http\Controllers\Admin\MediaIT;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationAdminController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index()
    {
        return view('admin.media-it.notifications.index');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:500'],
            'category' => ['required', 'in:activity,general,system'],
            'link' => ['nullable', 'string'],
        ]);

        $this->notificationService->notifyAllUsers(
            $data['title'],
            $data['body'],
            $data['category'],
            $data['link'] ?? null
        );

        return back()->with('success', 'Notifikasi berhasil dikirim ke semua pengguna.');
    }
}
