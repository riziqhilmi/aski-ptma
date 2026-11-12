<?php

namespace Tests\Feature;

use App\Models\IntellectualProperty;
use App\Models\Invoice;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_operational_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = $this->member();
        $application = MembershipApplication::create([
            'user_id' => $member->id,
            'status' => MembershipApplication::SUBMITTED,
            'submitted_at' => now(),
        ]);
        $invoice = Invoice::create([
            'membership_application_id' => $application->id,
            'user_id' => $member->id,
            'number' => 'INV-DASH-1',
            'amount' => 150000,
            'duration_months' => 12,
            'issued_at' => today(),
            'due_at' => today()->addDays(14),
            'status' => 'paid',
        ]);
        Membership::create([
            'user_id' => $member->id,
            'membership_application_id' => $application->id,
            'invoice_id' => $invoice->id,
            'starts_at' => today()->subDay(),
            'ends_at' => today()->addYear(),
            'status' => 'active',
        ]);
        Payment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $member->id,
            'proof_path' => 'payment-proofs/proof.pdf',
            'status' => Payment::SUBMITTED,
        ]);
        $this->hki($member);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->where('summary.active_members', 1)
                ->where('summary.new_applications', 1)
                ->where('summary.total_hki', 1)
                ->where('summary.pending_payments', 1)
            );
    }

    public function test_member_dashboard_shows_membership_invoices_and_hki_summary(): void
    {
        $member = $this->member();
        $application = MembershipApplication::create([
            'user_id' => $member->id,
            'status' => MembershipApplication::APPROVED_WAITING_PAYMENT,
            'submitted_at' => now(),
        ]);
        $invoice = Invoice::create([
            'membership_application_id' => $application->id,
            'user_id' => $member->id,
            'number' => 'INV-DASH-2',
            'amount' => 200000,
            'duration_months' => 12,
            'issued_at' => today(),
            'due_at' => today()->addDays(14),
            'status' => Invoice::UNPAID,
        ]);
        Membership::create([
            'user_id' => $member->id,
            'membership_application_id' => $application->id,
            'invoice_id' => $invoice->id,
            'starts_at' => today()->subDay(),
            'ends_at' => today()->addYear(),
            'status' => 'active',
        ]);
        $this->hki($member);

        $this->actingAs($member)
            ->get(route('member.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Member/Dashboard')
                ->where('summary.membership_status', 'Aktif')
                ->where('summary.hki_count', 1)
                ->where('summary.unpaid_invoices', 1)
                ->where('nextAction.route', 'member.invoices.show')
                ->where('nextAction.id', $invoice->id)
                ->has('latestInvoices', 1)
                ->has('latestHki', 1)
            );
    }

    private function member(): User
    {
        $user = User::factory()->create(['role' => 'member']);
        MemberProfile::create([
            'user_id' => $user->id,
            'account_type' => 'individual',
            'phone' => '08123456789',
            'address' => 'Yogyakarta',
        ]);

        return $user;
    }

    private function hki(User $user): IntellectualProperty
    {
        return IntellectualProperty::create([
            'user_id' => $user->id,
            'application_number' => 'EC00202600001',
            'application_date' => '2026-01-01',
            'creator' => 'Tim Peneliti',
            'rights_holder' => $user->name,
            'ip_type' => 'Hak Cipta',
            'recordation_number' => '0001',
            'title' => 'Aplikasi Dashboard',
            'first_published_at' => '2026-01-02',
            'protection_period' => 'Seumur hidup pencipta ditambah 70 tahun',
        ]);
    }
}
