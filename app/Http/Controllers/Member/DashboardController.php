<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\MembershipApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $activeMembership = Membership::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->latest('ends_at')
            ->first();
        $latestApplication = MembershipApplication::where('user_id', $user->id)
            ->latest('submitted_at')
            ->first();

        return Inertia::render('Member/Dashboard', [
            'summary' => [
                'membership_status' => $activeMembership ? 'Aktif' : ($latestApplication ? MembershipApplication::statusLabels()[$latestApplication->status] : 'Belum mengajukan'),
                'active_period' => $activeMembership ? $activeMembership->starts_at->format('Y-m-d').' s.d. '.$activeMembership->ends_at->format('Y-m-d') : '-',
                'hki_count' => $user->intellectualProperties()->count(),
                'unpaid_invoices' => $user->invoices()->where('status', Invoice::UNPAID)->count(),
                'unpaid_amount' => (float) $user->invoices()->where('status', Invoice::UNPAID)->sum('amount'),
            ],
            'latestApplication' => $latestApplication,
            'latestInvoices' => $user->invoices()->latest()->limit(5)->get(),
            'latestHki' => $user->intellectualProperties()->latest('application_date')->limit(5)->get(),
        ]);
    }
}
