<?php

namespace Tests\Feature;

use App\Models\IntellectualProperty;
use App\Models\Invoice;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_members_page_lists_only_active_members(): void
    {
        $active = $this->member('member-aktif@example.test', 'Universitas Aktif');
        $expired = $this->member('member-expired@example.test', 'Universitas Kadaluarsa');

        $this->membership($active, today()->subMonth(), today()->addMonth());
        $this->membership($expired, today()->subMonths(3), today()->subDay());

        $this->get(route('public.members.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Members/Index')
                ->has('members.data', 1)
                ->where('members.data.0.user.member_profile.institution_name', 'Universitas Aktif')
            );
    }

    public function test_public_hki_page_shows_records_and_summary(): void
    {
        $member = $this->member('hki@example.test', 'Universitas HKI');
        IntellectualProperty::create([
            'user_id' => $member->id,
            'application_number' => 'EC00202600001',
            'application_date' => '2026-01-01',
            'creator' => 'Tim Peneliti',
            'rights_holder' => 'Universitas HKI',
            'ip_type' => 'Hak Cipta',
            'recordation_number' => '0001',
            'title' => 'Aplikasi HKI',
            'first_published_at' => '2026-01-02',
            'protection_period' => 'Seumur hidup pencipta ditambah 70 tahun',
        ]);

        $this->get(route('public.intellectual-properties.index', ['type' => 'Hak Cipta']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/IntellectualProperties/Index')
                ->where('summary.total', 1)
                ->has('records.data', 1)
                ->where('records.data.0.title', 'Aplikasi HKI')
            );
    }

    private function member(string $email, string $institution): User
    {
        $user = User::factory()->create([
            'role' => 'member',
            'email' => $email,
        ]);

        MemberProfile::create([
            'user_id' => $user->id,
            'account_type' => 'institution',
            'phone' => '08123456789',
            'address' => 'Yogyakarta',
            'institution_name' => $institution,
            'responsible_name' => 'Penanggung Jawab',
            'responsible_position' => 'Ketua Sentra KI',
        ]);

        return $user;
    }

    private function membership(User $user, $startsAt, $endsAt): Membership
    {
        $application = MembershipApplication::create([
            'user_id' => $user->id,
            'status' => 'approved_waiting_payment',
            'submitted_at' => now(),
        ]);

        $invoice = Invoice::create([
            'membership_application_id' => $application->id,
            'user_id' => $user->id,
            'number' => 'INV-'.$user->id,
            'amount' => 100000,
            'duration_months' => 12,
            'issued_at' => today(),
            'due_at' => today()->addDays(14),
            'status' => 'paid',
        ]);

        return Membership::create([
            'user_id' => $user->id,
            'membership_application_id' => $application->id,
            'invoice_id' => $invoice->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'active',
        ]);
    }
}
