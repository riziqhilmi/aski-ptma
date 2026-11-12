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
        $months = collect(range(0, 5))->map(function (int $offset) {
            return now(config('app.timezone'))->startOfMonth()->subMonths(5 - $offset);
        });
        $periodStart = $months->first()->toDateString();
        $periodEnd = $months->last()->copy()->endOfMonth()->toDateString();

        $invoiceCounts = $user->invoices()
            ->whereBetween('issued_at', [$periodStart, $periodEnd])
            ->get(['issued_at'])
            ->countBy(fn (Invoice $invoice) => $invoice->issued_at->format('Y-m'));

        $hkiCounts = $user->intellectualProperties()
            ->whereBetween('application_date', [$periodStart, $periodEnd])
            ->get(['application_date'])
            ->countBy(fn ($record) => $record->application_date->format('Y-m'));

        $monthlyTrends = $months->map(fn ($month) => [
            'key' => $month->format('Y-m'),
            'label' => $month->locale('id')->translatedFormat('M'),
            'invoices' => $invoiceCounts->get($month->format('Y-m'), 0),
            'hki' => $hkiCounts->get($month->format('Y-m'), 0),
        ])->values();

        $activeMembership = Membership::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->latest('ends_at')
            ->first();
        $latestApplication = MembershipApplication::where('user_id', $user->id)
            ->latest('submitted_at')
            ->first();
        $unpaidInvoice = $user->invoices()->where('status', Invoice::UNPAID)->latest()->first();
        $nextAction = match (true) {
            $unpaidInvoice !== null => ['label' => 'Lihat tagihan belum dibayar', 'route' => 'member.invoices.show', 'id' => $unpaidInvoice->id],
            $latestApplication?->status === MembershipApplication::SUBMITTED => ['label' => 'Lihat permohonan yang sedang diproses', 'route' => 'member.applications.index'],
            $latestApplication?->status === MembershipApplication::APPROVED_WAITING_PAYMENT => ['label' => 'Lihat status pembayaran', 'route' => 'member.applications.index'],
            $latestApplication?->status === MembershipApplication::REVISION => ['label' => 'Ajukan ulang permohonan', 'route' => 'member.applications.index'],
            $activeMembership !== null => ['label' => 'Lihat masa keanggotaan', 'route' => 'member.membership.index'],
            default => ['label' => 'Ajukan keanggotaan atau perpanjangan', 'route' => 'member.applications.index'],
        };

        return Inertia::render('Member/Dashboard', [
            'summary' => [
                'membership_status' => $activeMembership ? 'Aktif' : ($latestApplication ? MembershipApplication::statusLabels()[$latestApplication->status] : 'Belum mengajukan'),
                'active_period' => $activeMembership ? $activeMembership->starts_at->format('Y-m-d').' s.d. '.$activeMembership->ends_at->format('Y-m-d') : '-',
                'hki_count' => $user->intellectualProperties()->count(),
                'unpaid_invoices' => $user->invoices()->where('status', Invoice::UNPAID)->count(),
                'unpaid_amount' => (float) $user->invoices()->where('status', Invoice::UNPAID)->sum('amount'),
            ],
            'statusLabels' => [
                'applications' => MembershipApplication::statusLabels(),
                'invoices' => Invoice::statusLabels(),
            ],
            'nextAction' => $nextAction,
            'monthlyTrends' => $monthlyTrends,
            'latestApplication' => $latestApplication,
            'latestInvoices' => $user->invoices()->latest()->limit(5)->get(),
            'latestHki' => $user->intellectualProperties()->latest('application_date')->limit(5)->get(),
        ]);
    }
}
