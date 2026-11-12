<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MembershipRenewalTest extends TestCase
{
    use RefreshDatabase;

    public function test_renewal_after_active_membership_starts_after_previous_end(): void
    {
        Storage::fake('local');
        [$admin, $member, $application, $invoice] = $this->renewalFixture(today()->addMonths(2));
        $payment = $this->payment($member, $invoice);

        $this->actingAs($admin)->put(route('admin.payments.update', $payment), ['status' => Payment::VERIFIED])->assertRedirect();

        $renewal = Membership::where('invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame(today()->addMonths(2)->addDay()->toDateString(), $renewal->starts_at->toDateString());
        $this->assertSame(MembershipApplication::COMPLETED, $application->fresh()->status);
        $this->assertSame(MembershipApplication::TYPE_RENEWAL, $application->application_type);
    }

    public function test_renewal_after_expiry_starts_on_verification_date(): void
    {
        Storage::fake('local');
        [$admin, $member, $application, $invoice] = $this->renewalFixture(today()->subDay());
        $payment = $this->payment($member, $invoice);

        $this->actingAs($admin)->put(route('admin.payments.update', $payment), ['status' => Payment::VERIFIED])->assertRedirect();

        $renewal = Membership::where('invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame(today()->toDateString(), $renewal->starts_at->toDateString());
    }

    public function test_member_cannot_create_duplicate_pending_renewal(): void
    {
        [$admin, $member, $application, $invoice] = $this->renewalFixture(today()->addMonth());
        $this->actingAs($member)->post(route('member.applications.store'))->assertStatus(422);
        $this->assertDatabaseCount('membership_applications', 2);
    }

    public function test_member_may_submit_renewal_from_30_days_before_expiry(): void
    {
        [$admin, $member] = $this->renewalFixtureWithoutPending(today()->addDays(30));

        $this->actingAs($member)->get(route('member.applications.index'))
            ->assertInertia(fn ($page) => $page->where('canSubmitApplication', true)->where('renewalEligible', true));
        $this->actingAs($member)->post(route('member.applications.store'))->assertRedirect();
        $this->assertDatabaseHas('membership_applications', ['user_id' => $member->id, 'application_type' => MembershipApplication::TYPE_RENEWAL, 'status' => MembershipApplication::SUBMITTED]);
    }

    public function test_member_cannot_submit_renewal_earlier_than_30_day_window(): void
    {
        [, $member] = $this->renewalFixtureWithoutPending(today()->addDays(31));

        $this->actingAs($member)->get(route('member.applications.index'))
            ->assertInertia(fn ($page) => $page->where('canSubmitApplication', false)->where('renewalEligible', false));
        $this->actingAs($member)->post(route('member.applications.store'))->assertStatus(422);
    }

    public function test_renewal_invoice_and_period_are_12_months(): void
    {
        Storage::fake('local');
        [$admin, $member, $application, $invoice] = $this->renewalFixture(today()->addMonths(2));
        $invoice->update(['duration_months' => 6]);
        $payment = $this->payment($member, $invoice);

        $this->actingAs($admin)->put(route('admin.payments.update', $payment), ['status' => Payment::VERIFIED])->assertRedirect();
        $renewal = Membership::where('invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame($renewal->starts_at->copy()->addMonthsNoOverflow(12)->subDay()->toDateString(), $renewal->ends_at->toDateString());
    }

    private function renewalFixture($previousEndsAt): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        MemberProfile::create(['user_id' => $member->id, 'account_type' => 'individual', 'phone' => '08123', 'address' => 'Solo']);
        $previousApplication = MembershipApplication::create(['user_id' => $member->id, 'application_type' => MembershipApplication::TYPE_NEW, 'status' => MembershipApplication::COMPLETED, 'submitted_at' => now()]);
        $previousInvoice = Invoice::create(['membership_application_id' => $previousApplication->id, 'user_id' => $member->id, 'number' => 'PREV-'.$member->id, 'amount' => 1, 'duration_months' => 12, 'issued_at' => today()->subYear(), 'due_at' => today()->subYear()->addDays(14), 'status' => Invoice::PAID]);
        Membership::create(['user_id' => $member->id, 'membership_application_id' => $previousApplication->id, 'invoice_id' => $previousInvoice->id, 'starts_at' => today()->subYear(), 'ends_at' => $previousEndsAt, 'status' => 'active']);
        $application = MembershipApplication::create(['user_id' => $member->id, 'application_type' => MembershipApplication::TYPE_RENEWAL, 'previous_membership_id' => Membership::latest('id')->value('id'), 'status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'submitted_at' => now()]);
        $invoice = Invoice::create(['membership_application_id' => $application->id, 'user_id' => $member->id, 'number' => 'RENEW-'.$member->id, 'amount' => 1, 'duration_months' => 6, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => Invoice::UNPAID]);
        return [$admin, $member, $application, $invoice];
    }

    private function renewalFixtureWithoutPending($previousEndsAt): array
    {
        [$admin, $member] = $this->renewalFixture($previousEndsAt);
        $member->membershipApplications()->where('application_type', MembershipApplication::TYPE_RENEWAL)->delete();
        return [$admin, $member];
    }

    private function payment(User $member, Invoice $invoice): Payment
    {
        return Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/proof.pdf', 'status' => Payment::SUBMITTED]);
    }
}
