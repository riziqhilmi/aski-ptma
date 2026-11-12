<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MembershipApplication;
use App\Models\Membership;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Http\Request;
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
        $applications = $user->membershipApplications()->with(['histories.changedBy'])->latest()->paginate(10)->withQueryString();
        $revisionApplication = $user->membershipApplications()->where('status', MembershipApplication::REVISION)->latest()->first();
        $hasPendingApplication = $user->membershipApplications()->whereIn('status', [MembershipApplication::SUBMITTED, MembershipApplication::APPROVED_WAITING_PAYMENT])->exists();
        $hasRevision = $user->membershipApplications()->where('status', MembershipApplication::REVISION)->exists();
        $latestMembership = $user->memberships()->latest('ends_at')->first();
        $renewalOpensAt = $latestMembership?->ends_at?->copy()->subDays(30);
        $renewalEligible = $latestMembership && $latestMembership->ends_at->gte(today())
            && today()->gte($renewalOpensAt);

        return Inertia::render('Member/MembershipApplications/Index', [
            'profileComplete' => $profileComplete,
            'canSubmitApplication' => $profileComplete && ! $hasPendingApplication && ! $hasRevision && (! $latestMembership || $renewalEligible),
            'renewalEligible' => (bool) $renewalEligible,
            'renewalOpensAt' => $renewalOpensAt?->toDateString(),
            'membershipEndsAt' => $latestMembership?->ends_at?->toDateString(),
            'hasPendingApplication' => $hasPendingApplication,
            'resubmittingRevision' => $hasRevision && ! $hasPendingApplication,
            'revisionApplication' => $revisionApplication ? $revisionApplication->load('histories.changedBy') : null,
            'revisionProfile' => $revisionApplication ? array_merge(
                $user->only('name', 'email'),
                $user->memberProfile?->only('account_type', 'phone', 'address', 'institution_name', 'responsible_name', 'responsible_position') ?? []
            ) : null,
            'applications' => $applications,
            'statusLabels' => MembershipApplication::statusLabels(),
        ]);
    }

    public function updateRevision(Request $request, MembershipApplication $application): RedirectResponse
    {
        abort_unless((int) $application->user_id === (int) $request->user()->id, 404);
        DB::transaction(function () use ($request, $application): void {
            $locked = $request->user()->membershipApplications()->whereKey($application->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === MembershipApplication::REVISION, 422, 'Permohonan ini tidak sedang menunggu revisi.');
            $revisionRequest = $locked->histories()->where('status', MembershipApplication::REVISION)->orderByDesc('id')->first();
            $allowedFields = ['name', 'email', 'account_type', 'phone', 'address', 'institution_name', 'responsible_name', 'responsible_position'];
            $revisionData = $revisionRequest?->revision_data ?? [];
            $requestedFields = array_values(array_intersect($revisionData['requested_fields'] ?? [], $allowedFields));
            // Older revision records predate the requested-fields snapshot.
            // Keep those requests editable using the supported profile fields.
            if (! array_key_exists('requested_fields', $revisionData)) {
                $requestedFields = $allowedFields;
            }
            abort_if(empty($requestedFields), 422, 'Permintaan revisi tidak memiliki field yang harus diperbaiki.');

            $user = $locked->user()->with('memberProfile')->firstOrFail();
            $profile = $user->memberProfile ?? new MemberProfile(['user_id' => $user->id]);
            $input = $request->only($requestedFields);
            $accountType = $input['account_type'] ?? $profile->account_type;
            $fieldsToValidate = $requestedFields;
            if ($accountType === 'institution' && $profile->account_type !== 'institution') {
                $fieldsToValidate = array_values(array_unique(array_merge($fieldsToValidate, ['institution_name', 'responsible_name'])));
            }
            $input = $request->only($fieldsToValidate);
            $validated = validator($input, [
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => ['sometimes', 'required', 'email', 'max:255', \Illuminate\Validation\Rule::unique(User::class)->ignore($user->id)],
                'account_type' => ['sometimes', 'required', \Illuminate\Validation\Rule::in(['individual', 'institution'])],
                'phone' => ['sometimes', 'required', 'string', 'max:25'],
                'address' => ['sometimes', 'required', 'string', 'max:1000'],
                'institution_name' => [$accountType === 'institution' && in_array('institution_name', $fieldsToValidate, true) ? 'required' : 'sometimes', 'nullable', 'string', 'max:255'],
                'responsible_name' => [$accountType === 'institution' && in_array('responsible_name', $fieldsToValidate, true) ? 'required' : 'sometimes', 'nullable', 'string', 'max:255'],
                'responsible_position' => ['sometimes', 'nullable', 'string', 'max:255'],
            ], [], [
                'name' => 'nama', 'email' => 'email', 'account_type' => 'jenis akun', 'phone' => 'nomor telepon',
                'address' => 'alamat', 'institution_name' => 'nama institusi', 'responsible_name' => 'nama penanggung jawab',
                'responsible_position' => 'jabatan penanggung jawab',
            ])->validate();
            $fieldsToSave = array_intersect_key($validated, array_flip($fieldsToValidate));

            $user->fill(array_intersect_key($fieldsToSave, array_flip(['name', 'email'])))->save();
            $profile->fill(array_intersect_key($fieldsToSave, array_flip(['account_type', 'phone', 'address', 'institution_name', 'responsible_name', 'responsible_position'])));
            $profile->save();

            $requestedProfile = $fieldsToSave;
            $snapshot = array_merge($requestedProfile, [
                'submitted_profile' => array_merge($user->only('name', 'email'), $profile->only('account_type', 'phone', 'address', 'institution_name', 'responsible_name', 'responsible_position')),
                'requested_fields' => $requestedFields,
            ]);
            $locked->update(['status' => MembershipApplication::SUBMITTED, 'admin_note' => null, 'submitted_at' => now()]);
            $locked->histories()->create([
                'status' => MembershipApplication::SUBMITTED,
                'note' => 'Member memperbarui data yang diminta dan mengajukan ulang permohonan.',
                'changed_by' => $request->user()->id,
                'revision_data' => $snapshot,
            ]);
        });

        return to_route('member.applications.index')->with('success', 'Perubahan tersimpan dan permohonan berhasil diajukan ulang.');
    }

    public function store(): RedirectResponse
    {
        $user = request()->user();
        abort_unless($user->memberProfile?->isComplete(), 422, 'Lengkapi profil Anda sebelum mengajukan keanggotaan.');

        DB::transaction(function () use ($user): void {
            $revisionApplication = $user->membershipApplications()->where('status', MembershipApplication::REVISION)->latest()->lockForUpdate()->first();
            abort_if($revisionApplication, 422, 'Perbaiki data yang diminta sebelum mengajukan ulang permohonan.');
            abort_if($user->membershipApplications()->whereIn('status', [MembershipApplication::SUBMITTED, MembershipApplication::APPROVED_WAITING_PAYMENT])->exists(), 422, 'Masih ada permohonan yang sedang diproses.');
            $previousMembership = $user->memberships()->latest('ends_at')->lockForUpdate()->first();
            abort_if($previousMembership && ($previousMembership->ends_at->lt(today()) || today()->lt($previousMembership->ends_at->copy()->subDays(30))), 422, 'Perpanjangan dapat diajukan mulai 30 hari kalender sebelum masa keanggotaan berakhir.');
            $application = $user->membershipApplications()->create([
                'application_type' => $previousMembership ? MembershipApplication::TYPE_RENEWAL : MembershipApplication::TYPE_NEW,
                'previous_membership_id' => $previousMembership?->id,
                'status' => MembershipApplication::SUBMITTED,
                'submitted_at' => now(),
            ]);
            $this->recordHistory($application, MembershipApplication::SUBMITTED, 'Permohonan diajukan oleh Member.', $user->id);
        });

        return to_route('member.applications.index')->with('success', 'Permohonan keanggotaan berhasil diajukan.');
    }

    private function recordHistory(MembershipApplication $application, string $status, ?string $note, ?int $changedBy): void
    {
        $application->histories()->create(['status' => $status, 'note' => $note, 'changed_by' => $changedBy]);
    }
}
