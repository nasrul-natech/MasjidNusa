<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\PaymentConfiguration;
use Illuminate\Http\Request;

class PaymentConfigController extends Controller
{
    public function index()
    {
        $activeMethod = PaymentConfiguration::getValue('active_payment_method', 'both');
        return view('admin.keuangan.payment-config.index', compact('activeMethod'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'active_payment_method' => ['required', 'in:manual,duitku,both'],
        ]);

        PaymentConfiguration::setValue('active_payment_method', $data['active_payment_method']);

        return back()->with('success', 'Konfigurasi pembayaran berhasil diperbarui.');
    }
}
