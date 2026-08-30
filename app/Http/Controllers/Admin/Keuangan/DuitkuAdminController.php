<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\DuitkuConfig;
use Illuminate\Http\Request;

class DuitkuAdminController extends Controller
{
    public function index()
    {
        $config = DuitkuConfig::first();
        return view('admin.keuangan.duitku.index', compact('config'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'merchant_code' => ['required', 'string'],
            'api_key' => ['required', 'string'],
            'expiry_period' => ['required', 'integer', 'min:5', 'max:1440'],
            'environment' => ['required', 'in:sandbox,production'],
            'is_active' => ['boolean'],
        ]);

        $data['callback_url'] = config('app.url') . '/webhook/duitku/callback';
        $data['return_url'] = config('app.url') . '/webhook/duitku/return';

        DuitkuConfig::updateOrCreate(['id' => 1], $data);

        return back()->with('success', 'Konfigurasi Duitku berhasil diperbarui.');
    }
}
