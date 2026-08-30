<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::orderBy('sort_order')->paginate(20);
        return view('admin.keuangan.bank-accounts.index', compact('accounts'));
    }

    public function create()
    {
        $account = null;
        return view('admin.keuangan.bank-accounts.form', compact('account'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
            'bank_logo' => ['nullable', 'image', 'max:1024'],
        ]);

        if ($request->hasFile('bank_logo')) {
            $data['bank_logo'] = $request->file('bank_logo')->store('bank-logos', 'public');
        }

        BankAccount::create($data);
        return redirect()->route('admin.keuangan.bank-accounts.index')->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function edit(BankAccount $bankAccount)
    {
        return view('admin.keuangan.bank-accounts.form', ['account' => $bankAccount]);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string'],
            'account_name' => ['required', 'string'],
            'account_number' => ['required', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);
        $bankAccount->update($data);
        return redirect()->route('admin.keuangan.bank-accounts.index')->with('success', 'Rekening diperbarui.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();
        return back()->with('success', 'Rekening dihapus.');
    }
}
