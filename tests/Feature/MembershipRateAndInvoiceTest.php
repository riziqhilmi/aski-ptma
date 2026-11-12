<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\MemberProfile;
use App\Models\MembershipApplication;
use App\Models\MembershipRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipRateAndInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_membership_rate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.rates.store'), ['account_type' => 'individual', 'name' => 'Tarif Individu 2026', 'amount' => 500000, 'duration_months' => 12, 'active_from' => today()->toDateString()])->assertRedirect(route('admin.rates.index'));
        $this->assertDatabaseHas('membership_rates', ['name' => 'Tarif Individu 2026', 'amount' => 500000]);
    }

    public function test_approval_creates_one_unpaid_invoice_using_active_rate(): void
    {
        [$admin, $member, $application] = $this->applicationWithActiveRate();
        $this->actingAs($admin)->put(route('admin.applications.update', $application), ['status' => MembershipApplication::APPROVED_WAITING_PAYMENT])->assertRedirect();
        $invoice = Invoice::firstOrFail();
        $this->assertSame(Invoice::UNPAID, $invoice->status);
        $this->assertSame('150000.00', $invoice->amount);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertSame(MembershipApplication::APPROVED_WAITING_PAYMENT, $application->fresh()->status);

        $this->actingAs($admin)->put(route('admin.applications.update', $application), ['status' => MembershipApplication::APPROVED_WAITING_PAYMENT])->assertStatus(422);
        $this->assertDatabaseCount('invoices', 1);
        $this->actingAs($member)->get(route('member.invoices.show', $invoice))->assertOk();
    }

    public function test_approval_requires_an_active_rate_and_member_cannot_view_other_invoice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        MemberProfile::create(['user_id' => $member->id, 'account_type' => 'individual', 'phone' => '08123', 'address' => 'Solo']);
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::SUBMITTED, 'submitted_at' => now()]);
        $this->actingAs($admin)->put(route('admin.applications.update', $application), ['status' => MembershipApplication::APPROVED_WAITING_PAYMENT])->assertStatus(422);
        $this->assertSame(MembershipApplication::SUBMITTED, $application->fresh()->status);

        $other = User::factory()->create(['role' => 'member']);
        $invoice = Invoice::create(['membership_application_id' => $application->id, 'user_id' => $member->id, 'number' => 'TAG-TEST-1', 'amount' => 1, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => 'unpaid']);
        $this->actingAs($other)->get(route('member.invoices.show', $invoice))->assertForbidden();
    }

    public function test_approval_uses_supported_account_type_from_submitted_revision_snapshot_when_profile_type_is_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        MemberProfile::create(['user_id' => $member->id, 'account_type' => null, 'phone' => '08123', 'address' => 'Solo']);
        $rate = MembershipRate::create(['account_type' => 'institution', 'name' => 'Tarif Institusi', 'amount' => 250000, 'active_from' => today()->subDay()]);
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::SUBMITTED, 'submitted_at' => now()]);
        $application->histories()->create([
            'status' => MembershipApplication::SUBMITTED,
            'revision_data' => ['submitted_profile' => ['account_type' => 'institution']],
        ]);

        $this->actingAs($admin)->put(route('admin.applications.update', $application), ['status' => MembershipApplication::APPROVED_WAITING_PAYMENT])->assertRedirect();

        $this->assertDatabaseHas('invoices', ['membership_application_id' => $application->id, 'membership_rate_id' => $rate->id, 'amount' => 250000]);
        $this->assertSame('institution', $member->fresh()->memberProfile->account_type);
    }

    private function applicationWithActiveRate(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        MemberProfile::create(['user_id' => $member->id, 'account_type' => 'individual', 'phone' => '08123', 'address' => 'Solo']);
        MembershipRate::create(['account_type' => 'individual', 'name' => 'Tarif Individu', 'amount' => 150000, 'active_from' => today()->subDay()]);
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::SUBMITTED, 'submitted_at' => now()]);
        return [$admin, $member, $application];
    }
}
