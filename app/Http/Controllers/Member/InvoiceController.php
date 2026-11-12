<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PaymentSetting;

class InvoiceController extends Controller
{
    public function index(): Response { return Inertia::render('Member/Invoices/Index', ['invoices' => request()->user()->invoices()->latest()->paginate(10), 'statusLabels' => Invoice::statusLabels()]); }
    public function show(Invoice $invoice): Response { $this->authorize('view', $invoice); $invoice->load(['user', 'payments', 'application']); return Inertia::render('Member/Invoices/Show', ['invoice' => $invoice, 'statusLabels' => Invoice::statusLabels(), 'paymentStatusLabels' => \App\Models\Payment::statusLabels()]); }
    public function pdf(Invoice $invoice) { $this->authorize('view', $invoice); $invoice->load(['user', 'payments']); return Pdf::loadView('pdf.invoice', ['invoice' => $invoice, 'setting' => PaymentSetting::current(), 'title' => 'INVOICE TAGIHAN'])->download($invoice->number.'.pdf'); }
}
