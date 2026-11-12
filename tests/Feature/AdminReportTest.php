<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\IntellectualProperty;
use App\Models\Invoice;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_report_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        MemberProfile::create(['user_id' => $member->id, 'account_type' => 'individual', 'phone' => '08123456789', 'address' => 'Yogyakarta']);
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'submitted_at' => now()]);
        $invoice = Invoice::create(['membership_application_id' => $application->id, 'user_id' => $member->id, 'number' => 'INV-REPORT-1', 'amount' => 125000, 'duration_months' => 12, 'issued_at' => today(), 'due_at' => today()->addDays(14), 'status' => 'paid']);
        Membership::create(['user_id' => $member->id, 'membership_application_id' => $application->id, 'invoice_id' => $invoice->id, 'starts_at' => today(), 'ends_at' => today()->addYear(), 'status' => 'active']);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $member->id, 'proof_path' => 'payment-proofs/proof.pdf', 'status' => Payment::VERIFIED]);
        IntellectualProperty::create(['user_id' => $member->id, 'application_number' => 'EC00202600001', 'application_date' => today(), 'creator' => 'Tim', 'rights_holder' => $member->name, 'ip_type' => 'Hak Cipta', 'recordation_number' => '0001', 'title' => 'Karya Laporan', 'first_published_at' => today(), 'protection_period' => 'Seumur hidup pencipta ditambah 70 tahun']);
        Article::create(['author_id' => $admin->id, 'title' => 'Berita Laporan', 'slug' => 'berita-laporan', 'excerpt' => 'Ringkasan', 'body' => 'Isi', 'status' => Article::STATUS_PUBLISHED, 'published_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Reports/Index')
                ->where('summary.members', 1)
                ->where('summary.payments', 1)
                ->where('summary.hki', 1)
                ->where('summary.published_articles', 1)
            );
    }

    public function test_member_cannot_access_admin_reports(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_verified_invoice_amount_is_counted_once_even_with_multiple_verified_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        $application = MembershipApplication::create([
            'user_id' => $member->id,
            'status' => MembershipApplication::COMPLETED,
            'submitted_at' => now(),
        ]);
        $invoice = Invoice::create([
            'membership_application_id' => $application->id,
            'user_id' => $member->id,
            'number' => 'INV-REPORT-DUPLICATE',
            'amount' => 125000,
            'duration_months' => 12,
            'issued_at' => today(),
            'due_at' => today()->addDays(14),
            'status' => Invoice::PAID,
        ]);

        foreach (['proof-one.pdf', 'proof-two.pdf'] as $proof) {
            Payment::create([
                'invoice_id' => $invoice->id,
                'user_id' => $member->id,
                'proof_path' => 'payment-proofs/'.$proof,
                'status' => Payment::VERIFIED,
            ]);
        }

        $olderApplication = MembershipApplication::create([
            'user_id' => $member->id,
            'status' => MembershipApplication::COMPLETED,
            'submitted_at' => now()->subDay(),
        ]);
        $olderInvoice = Invoice::create([
            'membership_application_id' => $olderApplication->id,
            'user_id' => $member->id,
            'number' => 'INV-REPORT-OLDER',
            'amount' => 99000,
            'duration_months' => 12,
            'issued_at' => today()->subDay(),
            'due_at' => today()->addDays(13),
            'status' => Invoice::PAID,
        ]);
        $olderPayment = Payment::create([
            'invoice_id' => $olderInvoice->id,
            'user_id' => $member->id,
            'proof_path' => 'payment-proofs/proof-older.pdf',
            'status' => Payment::VERIFIED,
        ]);
        $olderPayment->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->saveQuietly();
        $this->assertNotSame(today()->toDateString(), $olderPayment->fresh()->created_at->toDateString());

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['period_start' => today()->toDateString(), 'period_end' => today()->toDateString()]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.period_start', today()->toDateString())
                ->where('summary.payments', 2)
                ->where('summary.verified_payment_amount', 125000)
            );
    }
}
