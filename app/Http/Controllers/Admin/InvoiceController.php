<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PaymentSetting;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => Invoice::with('user')
                ->when(array_key_exists($status, Invoice::statusLabels()), fn ($query) => $query->where('status', $status))
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('number', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%"));
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => ['status' => $status, 'q' => $search],
            'statusLabels' => Invoice::statusLabels(),
        ]);
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['user', 'payments', 'application']);
        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice, 'setting' => PaymentSetting::current(), 'title' => 'INVOICE TAGIHAN'])->download($invoice->number.'.pdf');
    }

    public function view(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);
        $invoice->load(['user', 'payments', 'application']);
        return Inertia::render('Admin/Invoices/Show', ['invoice' => $invoice, 'paymentSetting' => PaymentSetting::current(), 'statusLabels' => Invoice::statusLabels(), 'paymentStatusLabels' => \App\Models\Payment::statusLabels()]);
    }
}
