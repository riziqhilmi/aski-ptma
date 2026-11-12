<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntellectualProperty;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $applicationCounts = MembershipApplication::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $paymentCounts = Payment::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return Inertia::render('Admin/Dashboard', [
            'summary' => [
                'active_members' => Membership::where('status', 'active')
                    ->whereDate('starts_at', '<=', today())
                    ->whereDate('ends_at', '>=', today())
                    ->distinct('user_id')
                    ->count('user_id'),
                'new_applications' => MembershipApplication::where('status', MembershipApplication::SUBMITTED)->count(),
                'total_hki' => IntellectualProperty::count(),
                'unpaid_invoices' => Invoice::where('status', Invoice::UNPAID)->count(),
                'pending_payments' => Payment::where('status', Payment::SUBMITTED)->count(),
                'paid_amount' => (float) Invoice::where('status', 'paid')->sum('amount'),
            ],
            'latestApplications' => MembershipApplication::with('user')
                ->latest('submitted_at')
                ->limit(5)
                ->get(),
            'latestPayments' => Payment::with(['user', 'invoice'])
                ->latest()
                ->limit(5)
                ->get(),
            'statusBreakdown' => [
                'applications' => $this->statusBreakdown(MembershipApplication::statusLabels(), $applicationCounts),
                'payments' => $this->statusBreakdown(Payment::statusLabels(), $paymentCounts),
            ],
            'statusLabels' => [
                'applications' => MembershipApplication::statusLabels(),
                'payments' => Payment::statusLabels(),
            ],
        ]);
    }

    private function statusBreakdown(array $labels, Collection $counts): array
    {
        return collect($labels)->map(fn (string $label, string $status): array => [
            'status' => $status,
            'label' => $label,
            'count' => (int) ($counts[$status] ?? 0),
        ])->values()->all();
    }
}
