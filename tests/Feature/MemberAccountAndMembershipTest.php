<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberAccountAndMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_view_membership_summary(): void
    {
        $member = $this->member();
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'submitted_at' => now()]);
        Membership::create(['user_id' => $member->id, 'membership_application_id' => $application->id, 'invoice_id' => $this->invoiceId($member, $application), 'starts_at' => today()->subDay(), 'ends_at' => today()->addYear(), 'status' => 'active']);

        $expiredApplication = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::COMPLETED, 'submitted_at' => now()->subYears(2)]);
        Membership::create(['user_id' => $member->id, 'membership_application_id' => $expiredApplication->id, 'invoice_id' => $this->invoiceId($member, $expiredApplication), 'starts_at' => today()->subYears(2), 'ends_at' => today()->subYear(), 'status' => 'active']);

        $this->actingAs($member)->get(route('member.membership.index'))->assertOk()->assertInertia(fn ($page) => $page->component('Member/Membership/Index')->where('activeMembership.status', 'active')->has('memberships.data', 2)->where('memberships.data.0.display_status', 'active')->where('memberships.data.0.invoice.number', 'INV-SUMMARY-'.$member->id.'-'.$application->id)->where('memberships.data.1.display_status', 'expired')->has('applications', 2));
    }

    public function test_member_can_update_notification_preferences(): void
    {
        $member = $this->member();

        $this->actingAs($member)->put(route('member.settings.notifications.update'), [
            'membership_updates' => false,
            'invoice_updates' => true,
            'payment_updates' => false,
            'document_updates' => true,
            'news_updates' => true,
        ])->assertRedirect(route('member.settings.edit'));

        $this->assertSame(false, $member->fresh()->notification_preferences['membership_updates']);
        $this->assertSame(true, $member->fresh()->notification_preferences['news_updates']);
    }

    public function test_membership_summary_flags_historical_paid_application_status_mismatch(): void
    {
        $member = $this->member();
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'submitted_at' => now()]);
        $invoice = Invoice::create(['membership_application_id' => $application->id, 'user_id' => $member->id, 'number' => 'INV-STATUS-REVIEW', 'amount' => 100000, 'duration_months' => 12, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => Invoice::PAID]);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/proof.pdf', 'status' => Payment::VERIFIED]);

        $this->actingAs($member)->get(route('member.membership.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('applications.0.needs_status_review', true)->where('applications.0.invoice.number', 'INV-STATUS-REVIEW'));
        $this->assertSame(MembershipApplication::APPROVED_WAITING_PAYMENT, $application->fresh()->status);
    }

    public function test_member_cannot_download_draft_document_and_published_file_stays_private(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $member = $this->member();
        Storage::disk('local')->put('documents/private.pdf', 'private-content');
        $draft = Document::create(['uploaded_by' => $admin->id, 'title' => 'Draft', 'category' => Document::CATEGORY_GUIDE, 'status' => Document::STATUS_DRAFT, 'file_path' => 'documents/private.pdf', 'original_filename' => 'private.pdf', 'mime_type' => 'application/pdf', 'file_size' => 15]);

        $this->actingAs($member)->get(route('member.documents.download', $draft))->assertForbidden();
        $this->assertFalse(file_exists(storage_path('app/documents/private.pdf')));
    }

    private function member(): User
    {
        $member = User::factory()->create(['role' => 'member']);
        MemberProfile::create(['user_id' => $member->id, 'account_type' => 'individual', 'phone' => '08123456789', 'address' => 'Yogyakarta']);
        return $member;
    }

    private function invoiceId(User $member, MembershipApplication $application): int
    {
        return $member->invoices()->create(['membership_application_id' => $application->id, 'number' => 'INV-SUMMARY-'.$member->id.'-'.$application->id, 'amount' => 100000, 'duration_months' => 12, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => 'paid'])->id;
    }
}
