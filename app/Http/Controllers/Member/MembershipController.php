<?php
namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;
use App\Models\MembershipApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class MembershipController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        return Inertia::render('Member/Membership/Index', [
            'activeMembership' => $user->memberships()->where('status', 'active')->whereDate('starts_at', '<=', today())->whereDate('ends_at', '>=', today())->latest('ends_at')->first(),
            'memberships' => $user->memberships()->latest('starts_at')->paginate(10)->withQueryString(),
            'applications' => $user->membershipApplications()->with('histories.changedBy')->latest('submitted_at')->limit(10)->get(),
            'statusLabels' => MembershipApplication::statusLabels(),
        ]);
    }
}
