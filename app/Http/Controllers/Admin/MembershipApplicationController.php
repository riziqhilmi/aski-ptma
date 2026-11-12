<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipApplicationDecisionRequest;
use App\Models\MembershipApplication;
use App\Models\MembershipRate;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MembershipApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('Admin/MembershipApplications/Index', [
            'applications' => MembershipApplication::query()
                ->with('user.memberProfile')
                ->when(array_key_exists($status, MembershipApplication::statusLabels()), fn ($query) => $query->where('status', $status))
                ->when(in_array($type, ['individual', 'institution'], true), fn ($query) => $query->whereHas('user.memberProfile', fn ($profile) => $profile->where('account_type', $type)))
                ->when($type === 'unselected', fn ($query) => $query->whereHas('user', fn ($user) => $user->whereDoesntHave('memberProfile')))
                ->when($search !== '', function ($query) use ($search): void {
                    $query->whereHas('user', function ($user) use ($search): void {
                        $user->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhereHas('memberships', fn ($memberships) => $memberships->where('member_number', 'like', '%'.$search.'%'));
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => ['status' => $status, 'type' => $type, 'q' => $search],
            'statusLabels' => MembershipApplication::statusLabels(),
        ]);
    }

    public function show(MembershipApplication $application): Response
    {
        $this->authorize('view', $application);
        $application->load(['user.memberProfile', 'histories.changedBy', 'invoice.payments', 'invoice.membership']);
        $application->latest_revision = $application->histories->first(fn ($history) => $history->status === MembershipApplication::SUBMITTED && $history->revision_data !== null);
        $application->needs_status_review = $application->invoice?->status === Invoice::PAID
            && $application->invoice->payments->contains('status', \App\Models\Payment::VERIFIED)
            && $application->status !== MembershipApplication::COMPLETED;
        $application->can_reconcile_paid = $application->status === MembershipApplication::APPROVED_WAITING_PAYMENT
            && $application->invoice?->status === Invoice::PAID
            && $application->invoice->payments->contains('status', \App\Models\Payment::VERIFIED)
            && $application->invoice->membership !== null;
        return Inertia::render('Admin/MembershipApplications/Show', ['application' => $application, 'statusLabels' => MembershipApplication::statusLabels()]);
    }

    public function update(MembershipApplicationDecisionRequest $request, MembershipApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);
        abort_if($application->status !== MembershipApplication::SUBMITTED, 422, 'Hanya permohonan berstatus diajukan yang dapat diproses.');
        $data = $request->validated();

        $member = $application->user()->with('memberProfile')->firstOrFail();
        $accountType = $member->memberProfile?->account_type;
        if (! in_array($accountType, ['individual', 'institution'], true)) {
            // Old applications may have been submitted before account type was
            // reliably saved to the profile. Use the submitted snapshot when
            // available, while still requiring an explicit supported type.
            $latestSubmission = $application->histories()
                ->where('status', MembershipApplication::SUBMITTED)
                ->whereNotNull('revision_data')
                ->latest('id')
                ->first();
            $accountType = $latestSubmission?->revision_data['submitted_profile']['account_type'] ?? null;
        }

        $rate = null;
        if ($data['status'] === MembershipApplication::APPROVED_WAITING_PAYMENT) {
            $rate = MembershipRate::activeFor($accountType, today())->latest('active_from')->first();
            if (! $rate) {
                return to_route('admin.applications.show', $application)
                    ->with('error', 'Tarif aktif untuk tipe akun ini belum tersedia. Tambahkan tarif aktif terlebih dahulu, lalu coba setujui permohonan kembali.');
            }
        }

        DB::transaction(function () use ($application, $data, $request, $member, $accountType, $rate): void {
            $application = MembershipApplication::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $application->update(['status' => $data['status'], 'admin_note' => $data['admin_note'] ?? null]);
            $application->histories()->create([
                'status' => $data['status'],
                'note' => $data['admin_note'] ?? null,
                'changed_by' => $request->user()->id,
                'revision_data' => $data['status'] === MembershipApplication::REVISION ? ['requested_fields' => $data['requested_fields']] : null,
            ]);

            if ($data['status'] === MembershipApplication::APPROVED_WAITING_PAYMENT) {
                if (in_array($accountType, ['individual', 'institution'], true) && $member->memberProfile?->account_type !== $accountType) {
                    $member->memberProfile()->updateOrCreate([], ['account_type' => $accountType]);
                }
                $durationMonths = $application->application_type === MembershipApplication::TYPE_RENEWAL ? 12 : $rate->duration_months;
                Invoice::firstOrCreate(['membership_application_id' => $application->id], ['user_id' => $application->user_id, 'membership_rate_id' => $rate->id, 'number' => 'TAG-'.now()->format('Y').'-'.str_pad((string) $application->id, 6, '0', STR_PAD_LEFT), 'amount' => $rate->amount, 'duration_months' => $durationMonths, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => Invoice::UNPAID]);
            }
        });
        return to_route('admin.applications.show', $application)->with('success', 'Status permohonan berhasil diperbarui.');
    }

    public function reconcilePaid(Request $request, MembershipApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        DB::transaction(function () use ($request, $application): void {
            $lockedApplication = MembershipApplication::whereKey($application->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedApplication->status === MembershipApplication::APPROVED_WAITING_PAYMENT, 422, 'Permohonan ini tidak lagi menunggu pembayaran.');

            $invoice = Invoice::where('membership_application_id', $lockedApplication->id)->lockForUpdate()->firstOrFail();
            abort_unless($invoice->status === Invoice::PAID, 422, 'Tagihan belum berstatus dibayar.');
            abort_unless(Payment::where('invoice_id', $invoice->id)->where('status', Payment::VERIFIED)->exists(), 422, 'Belum ada bukti pembayaran terverifikasi.');
            abort_unless(Membership::where('invoice_id', $invoice->id)->where('user_id', $invoice->user_id)->exists(), 422, 'Masa keanggotaan untuk tagihan ini belum tercatat.');

            $lockedApplication->update(['status' => MembershipApplication::COMPLETED]);
            $lockedApplication->histories()->create([
                'status' => MembershipApplication::COMPLETED,
                'note' => 'Rekonsiliasi Admin: tagihan berstatus dibayar, bukti pembayaran terverifikasi, dan masa keanggotaan tercatat.',
                'changed_by' => $request->user()->id,
            ]);
        });

        return to_route('admin.applications.show', $application)->with('success', 'Status permohonan berhasil direkonsiliasi berdasarkan bukti pembayaran dan masa keanggotaan.');
    }
}
