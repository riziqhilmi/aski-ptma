<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\IssuedMembershipDocument;
use App\Models\MembershipDocumentSetting;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentAndMembershipActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_upload_a_private_payment_proof_for_unpaid_invoice(): void
    {
        Storage::fake('local');
        [$member, $invoice] = $this->invoiceForMember();
        $this->actingAs($member)->post(route('member.invoices.payments.store', $invoice), ['proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf')])->assertRedirect(route('member.invoices.show', $invoice));
        $payment = Payment::firstOrFail();
        $this->assertSame(Payment::SUBMITTED, $payment->status);
        Storage::disk('local')->assertExists($payment->proof_path);
        $this->actingAs($member)->post(route('member.invoices.payments.store', $invoice), ['proof' => UploadedFile::fake()->create('ulang.pdf', 100, 'application/pdf')])->assertStatus(422);
    }

    public function test_admin_verification_marks_invoice_paid_and_activates_membership_once(): void
    {
        Storage::fake('local');
        [$member, $invoice] = $this->invoiceForMember();
        $payment = Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/bukti.pdf', 'status' => Payment::SUBMITTED]);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.payments.update', $payment), ['status' => Payment::VERIFIED])->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status);
        $membership = Membership::firstOrFail();
        $this->assertSame('active', $membership->status);
        $this->assertSame(MembershipApplication::COMPLETED, $invoice->application->fresh()->status);
        $this->assertSame(today()->addMonthsNoOverflow(6)->subDay()->toDateString(), $membership->ends_at->toDateString());
        $this->assertMatchesRegularExpression('/^ASKI\\/'.now()->year.'\\/\\d{4}$/', $membership->member_number);
        $this->assertDatabaseCount('issued_membership_documents', 2);
        $this->assertCount(2, IssuedMembershipDocument::all());
        foreach (IssuedMembershipDocument::all() as $document) { Storage::disk('local')->assertExists($document->file_path); $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($document->file_path)); }
        $this->actingAs($admin)->put(route('admin.payments.update', $payment), ['status' => Payment::VERIFIED])->assertStatus(422);
        $this->assertDatabaseCount('memberships', 1);
    }

    public function test_invoice_status_is_consistent_in_admin_and_member_lists_and_detail(): void
    {
        [$member, $unpaidInvoice] = $this->invoiceForMember();
        $paidApplication = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'submitted_at' => now()]);
        $paidInvoice = Invoice::create(['membership_application_id' => $paidApplication->id, 'user_id' => $member->id, 'number' => 'TAG-2026-000013', 'amount' => 250000, 'duration_months' => 6, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => Invoice::PAID]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.invoices.index', ['status' => Invoice::PAID]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('statusLabels.paid', 'Dibayar')->has('invoices.data', 1)->where('invoices.data.0.id', $paidInvoice->id));
        $this->actingAs($member)->get(route('member.invoices.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('statusLabels.paid', 'Dibayar')->where('statusLabels.unpaid', 'Belum dibayar')->has('invoices.data', 2));
        $this->actingAs($member)->get(route('member.invoices.show', $paidInvoice))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('statusLabels.paid', 'Dibayar')->where('invoice.status', Invoice::PAID));
        $this->assertSame(Invoice::UNPAID, $unpaidInvoice->fresh()->status);
    }

    public function test_admin_account_and_application_details_flag_historical_paid_status_mismatch_without_changing_it(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'submitted_at' => now()]);
        $invoice = Invoice::create(['membership_application_id' => $application->id, 'user_id' => $member->id, 'number' => 'TAG-TEST-MISMATCH', 'amount' => 250000, 'duration_months' => 6, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => Invoice::PAID]);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/bukti.pdf', 'status' => Payment::VERIFIED]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.accounts.show', $member))
            ->assertOk()->assertInertia(fn ($page) => $page->where('account.applications.0.needs_status_review', true));
        $this->actingAs($admin)->get(route('admin.applications.show', $application))
            ->assertOk()->assertInertia(fn ($page) => $page->where('application.needs_status_review', true));
        $this->assertSame(MembershipApplication::APPROVED_WAITING_PAYMENT, $application->fresh()->status);
    }

    public function test_admin_can_reconcile_historical_paid_application_with_membership_and_audit_history(): void
    {
        [$member, $invoice] = $this->invoiceForMember();
        $application = $invoice->application;
        $invoice->update(['status' => Invoice::PAID]);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/verified.pdf', 'status' => Payment::VERIFIED]);
        Membership::create(['user_id' => $member->id, 'membership_application_id' => $application->id, 'invoice_id' => $invoice->id, 'starts_at' => today()->subDays(10), 'ends_at' => today()->addMonths(6), 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.applications.reconcile-paid', $application))->assertRedirect(route('admin.applications.show', $application));

        $this->assertSame(MembershipApplication::COMPLETED, $application->fresh()->status);
        $this->assertDatabaseHas('membership_application_histories', [
            'membership_application_id' => $application->id,
            'status' => MembershipApplication::COMPLETED,
            'changed_by' => $admin->id,
        ]);
        $this->assertDatabaseCount('memberships', 1);
    }

    public function test_reconciliation_refuses_paid_verified_application_without_membership(): void
    {
        [$member, $invoice] = $this->invoiceForMember();
        $application = $invoice->application;
        $invoice->update(['status' => Invoice::PAID]);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/verified.pdf', 'status' => Payment::VERIFIED]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.applications.reconcile-paid', $application))->assertStatus(422);

        $this->assertSame(MembershipApplication::APPROVED_WAITING_PAYMENT, $application->fresh()->status);
        $this->assertDatabaseCount('membership_application_histories', 0);
    }

    public function test_member_invoice_does_not_receive_empty_payment_instructions(): void
    {
        [$member, $invoice] = $this->invoiceForMember();
        $this->actingAs($member)->get(route('member.invoices.show', $invoice))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('paymentSetting.is_configured', false));
    }

    public function test_verification_cannot_mark_payment_verified_if_invoice_is_already_paid(): void
    {
        [$member, $invoice] = $this->invoiceForMember();
        $payment = Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/bukti.pdf', 'status' => Payment::SUBMITTED]);
        $invoice->update(['status' => Invoice::PAID]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.payments.update', $payment), ['status' => Payment::VERIFIED])->assertStatus(422);
        $this->assertSame(Payment::SUBMITTED, $payment->fresh()->status);
        $this->assertDatabaseCount('memberships', 0);
    }

    public function test_upload_rejects_invalid_file_and_member_cannot_access_admin_payment_pages(): void
    {
        [$member, $invoice] = $this->invoiceForMember();
        $this->actingAs($member)->post(route('member.invoices.payments.store', $invoice), ['proof' => UploadedFile::fake()->create('bukti.txt', 10, 'text/plain')])->assertSessionHasErrors('proof');
        $this->actingAs($member)->get(route('admin.payments.index'))->assertForbidden();
    }

    public function test_member_can_preview_only_own_payment_proof(): void
    {
        Storage::fake('local');
        [$member, $invoice] = $this->invoiceForMember();
        $other = User::factory()->create(['role' => 'member']);
        Storage::disk('local')->put('payment-proofs/bukti.pdf', '%PDF-own');
        $payment = Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/bukti.pdf', 'status' => Payment::SUBMITTED]);

        $this->actingAs($member)->get(route('member.payments.proof', $payment))->assertOk();
        $this->actingAs($other)->get(route('member.payments.proof', $payment))->assertForbidden();
    }

    public function test_admin_can_preview_issued_document_and_settings_are_applied(): void
    {
        Storage::fake('local');
        [$member, $invoice] = $this->invoiceForMember();
        MembershipDocumentSetting::create(['type' => IssuedMembershipDocument::CERTIFICATE, 'title' => 'SERTIFIKAT KHUSUS', 'number_prefix' => 'KHUSUS', 'background_color' => '#123456', 'accent_color' => '#abcdef']);
        $payment = Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/bukti.pdf', 'status' => Payment::SUBMITTED]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.payments.update', $payment), ['status' => Payment::VERIFIED])->assertRedirect();

        $certificate = IssuedMembershipDocument::where('type', IssuedMembershipDocument::CERTIFICATE)->firstOrFail();
        $this->assertSame('SERTIFIKAT KHUSUS', $certificate->snapshot['title']);
        $this->assertSame('#123456', $certificate->snapshot['background_color']);
        $this->actingAs($admin)->get(route('admin.membership-documents.preview', $certificate))->assertOk();
        $this->actingAs($member)->get(route('admin.membership-documents.preview', $certificate))->assertForbidden();
    }

    public function test_payment_proof_rejects_path_traversal(): void
    {
        Storage::fake('local');
        [$member, $invoice] = $this->invoiceForMember();
        $payment = Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => '../membership-documents/secret.pdf', 'status' => Payment::SUBMITTED]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.payments.proof', $payment))->assertNotFound();
        $this->actingAs($member)->get(route('member.payments.proof', $payment))->assertNotFound();
    }

    private function invoiceForMember(): array
    {
        $member = User::factory()->create(['role' => 'member']);
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'submitted_at' => now()]);
        $invoice = Invoice::create(['membership_application_id' => $application->id, 'user_id' => $member->id, 'number' => 'TAG-TEST-'.$member->id, 'amount' => 500000, 'duration_months' => 6, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => Invoice::UNPAID]);
        return [$member, $invoice];
    }
}
