<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportTicketResponseRequest;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportTicketController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        return Inertia::render('Admin/SupportTickets/Index', [
            'filters' => ['status' => $status],
            'tickets' => SupportTicket::with('user')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function show(SupportTicket $supportTicket): Response
    {
        $this->authorize('view', $supportTicket);

        return Inertia::render('Admin/SupportTickets/Show', [
            'ticket' => $supportTicket->load(['user.memberProfile', 'responder']),
        ]);
    }

    public function update(SupportTicketResponseRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('update', $supportTicket);

        $data = $request->validated();
        $supportTicket->update([
            'status' => $data['status'],
            'admin_response' => $data['admin_response'] ?? $supportTicket->admin_response,
            'responded_by' => $data['status'] === SupportTicket::STATUS_ANSWERED ? $request->user()->id : $supportTicket->responded_by,
            'responded_at' => $data['status'] === SupportTicket::STATUS_ANSWERED ? now() : $supportTicket->responded_at,
        ]);

        return redirect()->route('admin.support-tickets.show', $supportTicket)->with('success', 'Tiket bantuan berhasil diperbarui.');
    }
}
