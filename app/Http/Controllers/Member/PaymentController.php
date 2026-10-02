<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentUploadRequest;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(PaymentUploadRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('view', $invoice);
        DB::transaction(function () use ($request, $invoice): void {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            abort_unless($invoice->status === Invoice::UNPAID, 422, 'Tagihan ini tidak dapat menerima bukti pembayaran.');
            abort_if($invoice->payments()->whereIn('status', [Payment::SUBMITTED, Payment::VERIFIED])->exists(), 422, 'Bukti pembayaran sedang diproses atau telah diverifikasi.');
            $path = $request->file('proof')->store('payment-proofs', 'local');
            $invoice->payments()->create(['user_id' => $request->user()->id, 'proof_path' => $path, 'status' => Payment::SUBMITTED]);
        });
        return to_route('member.invoices.show', $invoice)->with('success', 'Bukti pembayaran berhasil dikirim.');
    }
}
