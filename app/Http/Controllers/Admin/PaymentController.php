<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentDecisionRequest;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        return Inertia::render('Admin/Payments/Index', ['payments' => Payment::with(['user', 'invoice'])->when(in_array($status, [Payment::SUBMITTED, Payment::VERIFIED, Payment::REJECTED], true), fn ($query) => $query->where('status', $status))->latest()->paginate(10)->withQueryString(), 'filters' => ['status' => $status]]);
    }
    public function show(Payment $payment): Response { $payment->load(['user', 'invoice.membership']); return Inertia::render('Admin/Payments/Show', ['payment' => $payment]); }
    public function update(PaymentDecisionRequest $request, Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($request, $payment): void {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            abort_unless($payment->status === Payment::SUBMITTED, 422, 'Pembayaran ini sudah diproses.');
            $data = $request->validated();
            $payment->update(['status' => $data['status'], 'admin_note' => $data['admin_note'] ?? null, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
            if ($data['status'] === Payment::VERIFIED) {
                $invoice = Invoice::whereKey($payment->invoice_id)->lockForUpdate()->firstOrFail();
                abort_unless($invoice->status === Invoice::UNPAID, 422, 'Tagihan sudah dibayar atau tidak dapat diproses.');
                $invoice->update(['status' => 'paid']);
                Membership::firstOrCreate(['invoice_id' => $invoice->id], ['user_id' => $invoice->user_id, 'membership_application_id' => $invoice->membership_application_id, 'starts_at' => today(), 'ends_at' => today()->addMonthsNoOverflow($invoice->duration_months)->subDay(), 'status' => 'active']);
            }
        });
        return to_route('admin.payments.show', $payment)->with('success', 'Status pembayaran berhasil diperbarui.');
    }
    public function proof(Payment $payment) { abort_unless(Storage::disk('local')->exists($payment->proof_path), 404); return Storage::disk('local')->response($payment->proof_path); }
}
