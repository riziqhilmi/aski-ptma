<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportTicketRequest;
use App\Models\Faq;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Member/Support/Index', [
            'faqs' => Faq::where('is_published', true)->orderBy('sort_order')->get(),
            'tickets' => SupportTicket::where('user_id', auth()->id())->latest()->paginate(8)->withQueryString(),
        ]);
    }

    public function store(SupportTicketRequest $request): RedirectResponse
    {
        $request->user()->supportTickets()->create($request->validated());

        return redirect()->route('member.support.index')->with('success', 'Tiket bantuan berhasil dikirim.');
    }

    public function show(SupportTicket $supportTicket): Response
    {
        $this->authorize('view', $supportTicket);

        return Inertia::render('Member/Support/Show', [
            'ticket' => $supportTicket,
        ]);
    }
}
