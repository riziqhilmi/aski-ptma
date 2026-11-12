<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentUploadRequest;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PaymentSetting;

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

    public function proof(Request $request, Payment $payment): Response
    {
        abort_unless($payment->user_id === $request->user()->id, 403);
        abort_unless($this->isPaymentProofPath($payment->proof_path), 404);
        abort_unless(Storage::disk('local')->exists($payment->proof_path), 404);

        return Storage::disk('local')->response($payment->proof_path);
    }

    public function receipt(Request $request, Payment $payment)
    {
        abort_unless($payment->user_id === $request->user()->id && $payment->status === Payment::VERIFIED, 404);
        $payment->load(['invoice.user']);
        return Pdf::loadView('pdf.receipt', ['payment' => $payment, 'setting' => PaymentSetting::current()])->download('kwitansi-'.$payment->invoice->number.'.pdf');
    }

    private function isPaymentProofPath(string $path): bool
    {
        return str_starts_with($path, 'payment-proofs/')
            && ! str_contains($path, '..')
            && ! str_contains($path, "\0");
    }
}
