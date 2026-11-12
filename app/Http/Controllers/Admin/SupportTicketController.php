<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportTicketResponseRequest;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SupportTicketController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        return Inertia::render('Admin/SupportTickets/Index', [
            'filters' => ['status' => $status],
            'statusLabels' => SupportTicket::statusLabels(),
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
            'statusLabels' => SupportTicket::statusLabels(),
        ]);
    }

    public function update(SupportTicketResponseRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('update', $supportTicket);

        $data = $request->validated();
        DB::transaction(function () use ($supportTicket, $data, $request): void {
            $ticket = SupportTicket::whereKey($supportTicket->id)->lockForUpdate()->firstOrFail();
            if ($ticket->status === SupportTicket::STATUS_CLOSED) {
                abort_unless($data['status'] === SupportTicket::STATUS_OPEN, 422, 'Tiket ditutup; buka kembali sebelum mengirim jawaban.');
                $ticket->update(['status' => SupportTicket::STATUS_OPEN]);
                return;
            }
            $ticket->update([
                'status' => $data['status'],
                'admin_response' => $data['admin_response'] ?? $ticket->admin_response,
                'responded_by' => $data['status'] === SupportTicket::STATUS_ANSWERED ? $request->user()->id : $ticket->responded_by,
                'responded_at' => $data['status'] === SupportTicket::STATUS_ANSWERED ? now() : $ticket->responded_at,
            ]);
        });

        return redirect()->route('admin.support-tickets.show', $supportTicket)->with('success', 'Tiket bantuan berhasil diperbarui.');
    }
}
