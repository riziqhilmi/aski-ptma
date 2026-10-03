<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\IssuedMembershipDocument;
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
        $this->assertSame(today()->addMonthsNoOverflow(6)->subDay()->toDateString(), $membership->ends_at->toDateString());
        $this->assertMatchesRegularExpression('/^ASKI\\/'.now()->year.'\\/\\d{4}$/', $membership->member_number);
        $this->assertDatabaseCount('issued_membership_documents', 2);
        $this->assertCount(2, IssuedMembershipDocument::all());
        foreach (IssuedMembershipDocument::all() as $document) { Storage::disk('local')->assertExists($document->file_path); $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($document->file_path)); }
        $this->actingAs($admin)->put(route('admin.payments.update', $payment), ['status' => Payment::VERIFIED])->assertStatus(422);
        $this->assertDatabaseCount('memberships', 1);
    }

    public function test_upload_rejects_invalid_file_and_member_cannot_access_admin_payment_pages(): void
    {
        [$member, $invoice] = $this->invoiceForMember();
        $this->actingAs($member)->post(route('member.invoices.payments.store', $invoice), ['proof' => UploadedFile::fake()->create('bukti.txt', 10, 'text/plain')])->assertSessionHasErrors('proof');
        $this->actingAs($member)->get(route('admin.payments.index'))->assertForbidden();
    }

    private function invoiceForMember(): array
    {
        $member = User::factory()->create(['role' => 'member']);
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'submitted_at' => now()]);
        $invoice = Invoice::create(['membership_application_id' => $application->id, 'user_id' => $member->id, 'number' => 'TAG-TEST-'.$member->id, 'amount' => 500000, 'duration_months' => 6, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => Invoice::UNPAID]);
        return [$member, $invoice];
    }
}
