<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_faqs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.faqs.store'), [
                'question' => 'Bagaimana menghubungi Admin?',
                'answer' => 'Gunakan menu Bantuan.',
                'is_published' => true,
                'sort_order' => 1,
            ])
            ->assertRedirect(route('admin.faqs.index'));

        $faq = Faq::firstOrFail();
        $this->assertTrue($faq->is_published);

        $this->actingAs($admin)
            ->put(route('admin.faqs.update', $faq), [
                'question' => 'Bagaimana membuat tiket?',
                'answer' => 'Isi formulir Hubungi Admin.',
                'is_published' => false,
                'sort_order' => 2,
            ])
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'question' => 'Bagaimana membuat tiket?',
            'is_published' => false,
        ]);
    }

    public function test_member_can_view_published_faqs_and_create_ticket(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        Faq::create(['question' => 'FAQ Terbit', 'answer' => 'Jawaban', 'is_published' => true, 'sort_order' => 1]);
        Faq::create(['question' => 'FAQ Draft', 'answer' => 'Jawaban', 'is_published' => false, 'sort_order' => 2]);

        $this->actingAs($member)
            ->get(route('member.support.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Member/Support/Index')
                ->has('faqs', 1)
                ->where('faqs.0.question', 'FAQ Terbit')
            );

        $this->actingAs($member)
            ->post(route('member.support.store'), [
                'subject' => 'Butuh bantuan',
                'message' => 'Mohon informasi keanggotaan.',
            ])
            ->assertRedirect(route('member.support.index'));

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $member->id,
            'subject' => 'Butuh bantuan',
            'status' => SupportTicket::STATUS_OPEN,
        ]);
    }

    public function test_empty_support_ticket_uses_indonesian_validation_messages(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $this->actingAs($member)->post(route('member.support.store'), [])
            ->assertSessionHasErrors(['subject', 'message'])
            ->assertSessionHas('errors', fn ($errors) => str_contains($errors->first('subject'), 'wajib diisi'));
    }

    public function test_admin_can_answer_ticket_and_member_can_read_own_ticket_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        $otherMember = User::factory()->create(['role' => 'member']);
        $ticket = SupportTicket::create([
            'user_id' => $member->id,
            'subject' => 'Status dokumen',
            'message' => 'Kapan dokumen tersedia?',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.support-tickets.update', $ticket), [
                'status' => SupportTicket::STATUS_ANSWERED,
                'admin_response' => 'Dokumen sedang diproses.',
            ])
            ->assertRedirect(route('admin.support-tickets.show', $ticket));

        $ticket->refresh();
        $this->assertSame(SupportTicket::STATUS_ANSWERED, $ticket->status);
        $this->assertSame($admin->id, $ticket->responded_by);
        $this->assertNotNull($ticket->responded_at);

        $this->actingAs($member)
            ->get(route('member.support.show', $ticket))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Member/Support/Show')
                ->where('ticket.admin_response', 'Dokumen sedang diproses.')
            );

        $this->actingAs($otherMember)
            ->get(route('member.support.show', $ticket))
            ->assertForbidden();
    }

    public function test_member_cannot_access_admin_support_pages(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get(route('admin.support-tickets.index'))
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('admin.faqs.store'), [
                'question' => 'Tidak boleh',
                'answer' => 'Tidak boleh',
                'is_published' => true,
                'sort_order' => 1,
            ])
            ->assertForbidden();
    }

    public function test_closed_ticket_must_be_reopened_before_admin_can_answer_again(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        $ticket = SupportTicket::create([
            'user_id' => $member->id,
            'subject' => 'Pertanyaan lanjutan',
            'message' => 'Mohon bantuan.',
            'status' => SupportTicket::STATUS_CLOSED,
            'admin_response' => 'Sudah ditindaklanjuti.',
            'responded_by' => $admin->id,
            'responded_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.support-tickets.update', $ticket), [
                'status' => SupportTicket::STATUS_ANSWERED,
                'admin_response' => 'Jawaban baru.',
            ])
            ->assertSessionHasErrors('status');
        $this->assertSame(SupportTicket::STATUS_CLOSED, $ticket->fresh()->status);
        $this->assertSame('Sudah ditindaklanjuti.', $ticket->fresh()->admin_response);

        $this->actingAs($admin)
            ->put(route('admin.support-tickets.update', $ticket), ['status' => SupportTicket::STATUS_OPEN])
            ->assertRedirect(route('admin.support-tickets.show', $ticket));
        $this->assertSame(SupportTicket::STATUS_OPEN, $ticket->fresh()->status);

        $this->actingAs($admin)
            ->put(route('admin.support-tickets.update', $ticket), [
                'status' => SupportTicket::STATUS_ANSWERED,
                'admin_response' => 'Jawaban baru.',
            ])
            ->assertRedirect(route('admin.support-tickets.show', $ticket));
        $this->assertSame('Jawaban baru.', $ticket->fresh()->admin_response);
    }
}
