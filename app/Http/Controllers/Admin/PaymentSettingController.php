<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentSettingController extends Controller
{
    public function edit(): Response { return Inertia::render('Admin/PaymentSettings/Edit', ['setting' => PaymentSetting::current()]); }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:150'],
        ]);
        PaymentSetting::current()->update($data);
        return to_route('admin.payment-settings.edit')->with('success', 'Rekening pembayaran berhasil diperbarui.');
    }
}
