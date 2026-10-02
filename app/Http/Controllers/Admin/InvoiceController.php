<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        return Inertia::render('Admin/Invoices/Index', ['invoices' => Invoice::with('user')->when($status === Invoice::UNPAID, fn ($query) => $query->where('status', $status))->latest()->paginate(10)->withQueryString(), 'filters' => ['status' => $status]]);
    }
}
