<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MembershipApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MembershipApplicationController extends Controller
{
    public function index(): Response
    {
        $user = request()->user();
        $profileComplete = $user->memberProfile?->isComplete() ?? false;
        $applications = $user->membershipApplications()->with(['histories.changedBy'])->latest()->paginate(10);

        return Inertia::render('Member/MembershipApplications/Index', [
            'profileComplete' => $profileComplete,
            'applications' => $applications,
            'statusLabels' => MembershipApplication::statusLabels(),
        ]);
    }

    public function store(): RedirectResponse
    {
        $user = request()->user();
        abort_unless($user->memberProfile?->isComplete(), 422, 'Lengkapi profil Anda sebelum mengajukan keanggotaan.');

        DB::transaction(function () use ($user): void {
            $application = $user->membershipApplications()->where('status', MembershipApplication::REVISION)->latest()->lockForUpdate()->first();
            if ($application) {
                $application->update(['status' => MembershipApplication::SUBMITTED, 'admin_note' => null, 'submitted_at' => now()]);
                $this->recordHistory($application, MembershipApplication::SUBMITTED, 'Permohonan diajukan kembali oleh Member.', $user->id);
                return;
            }

            abort_if($user->membershipApplications()->whereIn('status', [MembershipApplication::SUBMITTED, MembershipApplication::APPROVED_WAITING_PAYMENT])->exists(), 422, 'Masih ada permohonan yang sedang diproses.');
            $application = $user->membershipApplications()->create(['status' => MembershipApplication::SUBMITTED, 'submitted_at' => now()]);
            $this->recordHistory($application, MembershipApplication::SUBMITTED, 'Permohonan diajukan oleh Member.', $user->id);
        });

        return to_route('member.applications.index')->with('success', 'Permohonan keanggotaan berhasil diajukan.');
    }

    private function recordHistory(MembershipApplication $application, string $status, ?string $note, ?int $changedBy): void
    {
        $application->histories()->create(['status' => $status, 'note' => $note, 'changed_by' => $changedBy]);
    }
}
