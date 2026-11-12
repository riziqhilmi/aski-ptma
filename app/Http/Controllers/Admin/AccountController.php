<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $search = trim((string) $request->query('q', ''));

        $accounts = User::query()
            ->where('role', 'member')
            ->with('memberProfile')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($accounts) use ($search): void {
                    $accounts->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('memberships', fn ($memberships) => $memberships->where('member_number', 'like', '%'.$search.'%'));
                });
            })
            ->when(in_array($type, ['individual', 'institution'], true), fn ($query) => $query->whereHas('memberProfile', fn ($profile) => $profile->where('account_type', $type)))
            ->when($type === 'unselected', fn ($query) => $query->whereDoesntHave('memberProfile'))
            ->when($status === 'belum_lengkap', function ($query): void {
                $query->where(function ($accounts): void {
                    $accounts->whereDoesntHave('memberProfile')
                        ->orWhereHas('memberProfile', function ($profile): void {
                            $profile->whereNull('account_type')
                                ->orWhereNull('phone')
                                ->orWhereNull('address')
                                ->orWhere(function ($institution): void {
                                    $institution->where('account_type', 'institution')
                                        ->where(function ($missing): void {
                                            $missing->whereNull('institution_name')->orWhereNull('responsible_name');
                                        });
                                });
                        });
                });
            })
            ->when($status === 'lengkap', function ($query): void {
                $query->whereHas('memberProfile', function ($profile): void {
                    $profile->whereNotNull('account_type')->whereNotNull('phone')->whereNotNull('address')
                        ->where(function ($types): void {
                            $types->where('account_type', 'individual')
                                ->orWhere(function ($institution): void {
                                    $institution->where('account_type', 'institution')
                                        ->whereNotNull('institution_name')->whereNotNull('responsible_name');
                                });
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'account_type' => $user->memberProfile?->account_type,
                'completion_status' => $user->memberProfile?->completion_status ?? 'belum lengkap',
            ]);

        return Inertia::render('Admin/Accounts/Index', [
            'accounts' => $accounts,
            'filters' => ['type' => $type, 'status' => $status, 'q' => $search],
        ]);
    }

    public function show(User $user): Response
    {
        abort_unless($user->role === 'member', 404);
        $user->load(['memberProfile', 'memberships.invoice', 'membershipApplications.invoice.payments', 'intellectualProperties', 'issuedMembershipDocuments.membership']);

        $memberships = $user->memberships->sortByDesc('starts_at')->values()->map(function ($membership): array {
            $displayStatus = $membership->status !== 'active'
                ? $membership->status
                : ($membership->starts_at->gt(today()) ? 'upcoming' : ($membership->ends_at->lt(today()) ? 'expired' : 'active'));

            return array_merge($membership->toArray(), ['display_status' => $displayStatus]);
        });
        $applications = $user->membershipApplications->sortByDesc('submitted_at')->values();
        $applications->each(function ($application): void {
            $application->needs_status_review = $application->invoice?->status === 'paid'
                && $application->invoice->payments->contains('status', 'verified')
                && $application->status !== 'completed';
        });

        return Inertia::render('Admin/Accounts/Show', [
            'account' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->memberProfile,
                'completion_status' => $user->memberProfile?->completion_status ?? 'belum lengkap',
                'memberships' => $memberships,
                'applications' => $applications,
                'intellectual_properties' => $user->intellectualProperties->sortByDesc('application_date')->values(),
                'membership_documents' => $user->issuedMembershipDocuments->sortByDesc('issued_at')->values(),
            ],
        ]);
    }
}
