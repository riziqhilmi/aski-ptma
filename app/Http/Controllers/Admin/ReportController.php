<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\IntellectualProperty;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $periodStart = $request->date('period_start');
        $periodEnd = $request->date('period_end');

        $memberships = Membership::with('user.memberProfile')
            ->where('status', 'active')
            ->when($periodStart, fn (Builder $query) => $query->whereDate('starts_at', '>=', $periodStart))
            ->when($periodEnd, fn (Builder $query) => $query->whereDate('starts_at', '<=', $periodEnd));

        $payments = Payment::with(['user', 'invoice'])
            ->when($periodStart, fn (Builder $query) => $query->whereDate('created_at', '>=', $periodStart))
            ->when($periodEnd, fn (Builder $query) => $query->whereDate('created_at', '<=', $periodEnd));

        $hki = IntellectualProperty::with('user.memberProfile')
            ->when($periodStart, fn (Builder $query) => $query->whereDate('application_date', '>=', $periodStart))
            ->when($periodEnd, fn (Builder $query) => $query->whereDate('application_date', '<=', $periodEnd));

        $articles = Article::with('author')
            ->where('status', Article::STATUS_PUBLISHED)
            ->when($periodStart, fn (Builder $query) => $query->whereDate('published_at', '>=', $periodStart))
            ->when($periodEnd, fn (Builder $query) => $query->whereDate('published_at', '<=', $periodEnd));

        return Inertia::render('Admin/Reports/Index', [
            'filters' => [
                'period_start' => $request->input('period_start', ''),
                'period_end' => $request->input('period_end', ''),
            ],
            'paymentStatusLabels' => Payment::statusLabels(),
            'summary' => [
                'members' => (clone $memberships)->distinct('user_id')->count('user_id'),
                'payments' => (clone $payments)->count(),
                'verified_payment_amount' => (float) Invoice::whereIn(
                    'id',
                    (clone $payments)->where('payments.status', Payment::VERIFIED)->select('payments.invoice_id')->distinct(),
                )->sum('amount'),
                'hki' => (clone $hki)->count(),
                'published_articles' => (clone $articles)->count(),
            ],
            'memberships' => (clone $memberships)->latest('starts_at')->limit(8)->get(),
            'payments' => (clone $payments)->latest()->limit(8)->get(),
            'hkiRecords' => (clone $hki)->latest('application_date')->limit(8)->get(),
            'articles' => (clone $articles)->latest('published_at')->limit(8)->get(),
        ]);
    }
}
