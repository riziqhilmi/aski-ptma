<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(): Response { return Inertia::render('Member/Invoices/Index', ['invoices' => request()->user()->invoices()->latest()->paginate(10)]); }
    public function show(Invoice $invoice): Response { $this->authorize('view', $invoice); $invoice->load('payments'); return Inertia::render('Member/Invoices/Show', ['invoice' => $invoice]); }
}
