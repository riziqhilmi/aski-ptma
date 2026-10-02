<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipApplicationDecisionRequest;
use App\Models\MembershipApplication;
use App\Models\MembershipRate;
use App\Models\Invoice;
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
        return Inertia::render('Admin/MembershipApplications/Index', [
            'applications' => MembershipApplication::with('user.memberProfile')->when(array_key_exists($status, MembershipApplication::statusLabels()), fn ($query) => $query->where('status', $status))->latest()->paginate(10)->withQueryString(),
            'filters' => ['status' => $status],
            'statusLabels' => MembershipApplication::statusLabels(),
        ]);
    }

    public function show(MembershipApplication $application): Response
    {
        $this->authorize('view', $application);
        $application->load(['user.memberProfile', 'histories.changedBy']);
        return Inertia::render('Admin/MembershipApplications/Show', ['application' => $application, 'statusLabels' => MembershipApplication::statusLabels()]);
    }

    public function update(MembershipApplicationDecisionRequest $request, MembershipApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);
        abort_if($application->status !== MembershipApplication::SUBMITTED, 422, 'Hanya permohonan berstatus diajukan yang dapat diproses.');
        $data = $request->validated();

        DB::transaction(function () use ($application, $data, $request): void {
            $application = MembershipApplication::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $application->update(['status' => $data['status'], 'admin_note' => $data['admin_note'] ?? null]);
            $application->histories()->create(['status' => $data['status'], 'note' => $data['admin_note'] ?? null, 'changed_by' => $request->user()->id]);

            if ($data['status'] === MembershipApplication::APPROVED_WAITING_PAYMENT) {
                $accountType = $application->user()->with('memberProfile')->firstOrFail()->memberProfile?->account_type;
                $rate = MembershipRate::activeFor($accountType, today())->latest('active_from')->first();
                abort_unless($rate, 422, 'Tarif aktif untuk tipe akun ini belum tersedia.');
                Invoice::firstOrCreate(['membership_application_id' => $application->id], ['user_id' => $application->user_id, 'membership_rate_id' => $rate->id, 'number' => 'TAG-'.now()->format('Y').'-'.str_pad((string) $application->id, 6, '0', STR_PAD_LEFT), 'amount' => $rate->amount, 'duration_months' => $rate->duration_months, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => Invoice::UNPAID]);
            }
        });
        return to_route('admin.applications.show', $application)->with('success', 'Status permohonan berhasil diperbarui.');
    }
}
