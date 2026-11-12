<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentDecisionRequest;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\MembershipApplication;
use App\Services\MembershipDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PaymentSetting;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('Admin/Payments/Index', [
            'payments' => Payment::with(['user', 'invoice'])
                ->when(in_array($status, [Payment::SUBMITTED, Payment::VERIFIED, Payment::REJECTED], true), fn ($query) => $query->where('status', $status))
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                            ->orWhereHas('invoice', fn ($invoice) => $invoice->where('number', 'like', "%{$search}%"));
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => ['status' => $status, 'q' => $search],
            'statusLabels' => Payment::statusLabels(),
        ]);
    }
    public function show(Payment $payment): Response { $payment->load(['user', 'invoice.membership']); return Inertia::render('Admin/Payments/Show', ['payment' => $payment, 'statusLabels' => Payment::statusLabels()]); }
    public function update(PaymentDecisionRequest $request, Payment $payment, MembershipDocumentService $documents): RedirectResponse
    {
        DB::transaction(function () use ($request, $payment, $documents): void {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            abort_unless($payment->status === Payment::SUBMITTED, 422, 'Pembayaran ini sudah diproses.');
            $data = $request->validated();
            $invoice = null;
            if ($data['status'] === Payment::VERIFIED) {
                $invoice = Invoice::whereKey($payment->invoice_id)->lockForUpdate()->firstOrFail();
                abort_unless($invoice->status === Invoice::UNPAID, 422, 'Tagihan sudah dibayar atau tidak dapat diproses.');
            }
            $payment->update(['status' => $data['status'], 'admin_note' => $data['admin_note'] ?? null, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
            if ($data['status'] === Payment::VERIFIED) {
                $invoice->update(['status' => Invoice::PAID]);
                $memberships = Membership::where('user_id', $invoice->user_id)->lockForUpdate()->orderByDesc('ends_at')->get();
                $latestMembership = $memberships->first();
                $application = MembershipApplication::find($invoice->membership_application_id);
                $isRenewal = $application?->application_type === MembershipApplication::TYPE_RENEWAL;
                $startsAt = $isRenewal && $latestMembership && $latestMembership->ends_at->gte(today())
                    ? $latestMembership->ends_at->copy()->addDay()
                    : today();
                $durationMonths = $isRenewal ? 12 : $invoice->duration_months;
                $endsAt = $startsAt->copy()->addMonthsNoOverflow($durationMonths)->subDay();
                $membership = Membership::firstOrCreate(['invoice_id' => $invoice->id], ['user_id' => $invoice->user_id, 'membership_application_id' => $invoice->membership_application_id, 'starts_at' => $startsAt, 'ends_at' => $endsAt, 'status' => 'active']);
                MembershipApplication::whereKey($invoice->membership_application_id)->update(['status' => MembershipApplication::COMPLETED]);
                $documents->issueAll($membership);
            }
        });
        return to_route('admin.payments.show', $payment)->with('success', 'Status pembayaran berhasil diperbarui.');
    }
    public function proof(Payment $payment) { abort_unless($this->isPaymentProofPath($payment->proof_path), 404); abort_unless(Storage::disk('local')->exists($payment->proof_path), 404); return Storage::disk('local')->response($payment->proof_path); }
    public function receipt(Payment $payment) { abort_unless($payment->status === Payment::VERIFIED, 404); $payment->load(['invoice.user']); return Pdf::loadView('pdf.receipt', ['payment' => $payment, 'setting' => PaymentSetting::current()])->download('kwitansi-'.$payment->invoice->number.'.pdf'); }

    private function isPaymentProofPath(string $path): bool
    {
        return str_starts_with($path, 'payment-proofs/')
            && ! str_contains($path, '..')
            && ! str_contains($path, "\0");
    }
}
