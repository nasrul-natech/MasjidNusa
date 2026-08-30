<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentConfiguration;
use Illuminate\Http\Request;

class PaymentRulesController extends Controller
{
    public function index()
    {
        $configs = PaymentConfiguration::all()->pluck('value', 'key')->toArray();
        return view('super-admin.payment-rules.index', compact('configs'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);
        foreach ($data as $key => $value) {
            PaymentConfiguration::setValue($key, $value);
        }
        return back()->with('success', 'Aturan pembayaran diperbarui.');
    }
}
