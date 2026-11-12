<?php
namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;
use App\Models\MembershipApplication;
use App\Models\Membership;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class MembershipController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $applications = $user->membershipApplications()->with(['histories.changedBy', 'invoice.payments'])->latest('submitted_at')->limit(10)->get();
        $applications->each(function (MembershipApplication $application): void {
            $application->needs_status_review = $application->invoice?->status === Invoice::PAID
                && $application->invoice->payments->contains('status', Payment::VERIFIED)
                && $application->status !== MembershipApplication::COMPLETED;
        });

        return Inertia::render('Member/Membership/Index', [
            'activeMembership' => $user->memberships()->where('status', 'active')->whereDate('starts_at', '<=', today())->whereDate('ends_at', '>=', today())->latest('ends_at')->first(),
            'memberships' => $user->memberships()->with('invoice')->latest('starts_at')->paginate(10)->withQueryString()->through(function (Membership $membership): array {
                $displayStatus = $membership->status !== 'active'
                    ? $membership->status
                    : ($membership->starts_at->gt(today()) ? 'upcoming' : ($membership->ends_at->lt(today()) ? 'expired' : 'active'));

                return array_merge($membership->toArray(), ['display_status' => $displayStatus]);
            }),
            'applications' => $applications,
            'statusLabels' => MembershipApplication::statusLabels(),
        ]);
    }
}
