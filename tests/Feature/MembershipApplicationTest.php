<?php

namespace Tests\Feature;

use App\Models\MemberProfile;
use App\Models\MembershipApplication;
use App\Models\MembershipApplicationHistory;
use App\Models\MembershipRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_with_incomplete_profile_cannot_submit_an_application(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $this->actingAs($member)->post(route('member.applications.store'))->assertStatus(422);
        $this->assertDatabaseCount('membership_applications', 0);
    }

    public function test_member_can_update_requested_profile_fields_and_resubmit_with_auditable_snapshot(): void
    {
        $member = $this->completeMember();
        $this->actingAs($member)->post(route('member.applications.store'))->assertRedirect(route('member.applications.index'))->assertSessionHas('success', 'Permohonan keanggotaan berhasil diajukan.');
        $this->actingAs($member)->get(route('member.applications.index'))
            ->assertInertia(fn ($page) => $page->where('flash.success', 'Permohonan keanggotaan berhasil diajukan.'));
        $application = MembershipApplication::firstOrFail();
        $this->assertSame(MembershipApplication::SUBMITTED, $application->status);
        $this->assertDatabaseHas('membership_application_histories', ['membership_application_id' => $application->id, 'status' => MembershipApplication::SUBMITTED]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.applications.update', $application), [
            'status' => MembershipApplication::REVISION,
            'admin_note' => 'Perbaiki alamat dan nomor telepon.',
            'requested_fields' => ['address', 'phone'],
        ])->assertRedirect();
        $this->actingAs($member)->get(route('member.applications.index'))
            ->assertInertia(fn ($page) => $page->where('revisionApplication.id', $application->id)->where('revisionProfile.address', 'Yogyakarta'));
        $this->actingAs($member)->put(route('member.applications.revision.update', $application), [
            'name' => $member->name,
            'email' => $member->email,
            'account_type' => 'individual',
            'phone' => '08111111111',
            'address' => 'Sleman, Yogyakarta',
        ])->assertRedirect(route('member.applications.index'));
        $this->assertSame(MembershipApplication::SUBMITTED, $application->fresh()->status);
        $this->assertSame('Sleman, Yogyakarta', $member->fresh()->memberProfile->address);
        $this->assertDatabaseHas('membership_application_histories', [
            'membership_application_id' => $application->id,
            'status' => MembershipApplication::SUBMITTED,
            'changed_by' => $member->id,
        ]);
        $resubmission = MembershipApplicationHistory::where('membership_application_id', $application->id)->where('status', MembershipApplication::SUBMITTED)->orderByDesc('id')->firstOrFail();
        $this->assertSame('Sleman, Yogyakarta', $resubmission->revision_data['submitted_profile']['address']);
        $this->assertNull($application->fresh()->admin_note);
        $this->actingAs($admin)->get(route('admin.applications.show', $application))
            ->assertInertia(fn ($page) => $page->where('application.latest_revision.revision_data.submitted_profile.address', 'Sleman, Yogyakarta'));
    }

    public function test_application_page_hides_submit_action_while_request_is_pending(): void
    {
        $member = $this->completeMember();
        $this->actingAs($member)->get(route('member.applications.index'))
            ->assertInertia(fn ($page) => $page->where('canSubmitApplication', true)->where('resubmittingRevision', false));

        MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'submitted_at' => now()]);
        $this->actingAs($member)->get(route('member.applications.index'))
            ->assertInertia(fn ($page) => $page->where('canSubmitApplication', false)->where('hasPendingApplication', true));
    }

    public function test_admin_can_request_revision_and_member_cannot_access_admin_application_pages(): void
    {
        $member = $this->completeMember();
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::SUBMITTED, 'submitted_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.applications.update', $application), ['status' => MembershipApplication::REVISION, 'admin_note' => 'Mohon lengkapi alamat.', 'requested_fields' => ['address']])->assertRedirect();
        $this->assertSame(MembershipApplication::REVISION, $application->fresh()->status);
        $this->assertDatabaseHas('membership_application_histories', ['membership_application_id' => $application->id, 'status' => MembershipApplication::REVISION, 'changed_by' => $admin->id]);
        $this->actingAs($member)->get(route('admin.applications.index'))->assertForbidden();
    }

    public function test_admin_approval_creates_invoice_and_rejection_is_recorded(): void
    {
        $member = $this->completeMember();
        $rate = MembershipRate::create(['account_type' => 'individual', 'name' => 'Tarif Individu', 'amount' => 120000, 'duration_months' => 12, 'active_from' => today()->subMonth(), 'active_until' => null]);
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::SUBMITTED, 'submitted_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.applications.update', $application), ['status' => MembershipApplication::APPROVED_WAITING_PAYMENT, 'requested_fields' => []])->assertRedirect();
        $this->assertSame(MembershipApplication::APPROVED_WAITING_PAYMENT, $application->fresh()->status);
        $this->assertDatabaseHas('invoices', ['membership_application_id' => $application->id, 'membership_rate_id' => $rate->id, 'status' => 'unpaid']);

        $rejected = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::SUBMITTED, 'submitted_at' => now()]);
        $this->actingAs($admin)->put(route('admin.applications.update', $rejected), ['status' => MembershipApplication::REJECTED, 'admin_note' => 'Dokumen belum dapat diverifikasi.'])->assertRedirect();
        $this->assertSame(MembershipApplication::REJECTED, $rejected->fresh()->status);
        $this->assertDatabaseMissing('invoices', ['membership_application_id' => $rejected->id]);
        $this->assertDatabaseHas('membership_application_histories', ['membership_application_id' => $rejected->id, 'status' => MembershipApplication::REJECTED, 'changed_by' => $admin->id]);
    }

    public function test_member_cannot_submit_revision_for_another_members_application_or_invalid_profile(): void
    {
        $owner = $this->completeMember();
        $other = $this->completeMember();
        $application = MembershipApplication::create(['user_id' => $owner->id, 'status' => MembershipApplication::REVISION, 'admin_note' => 'Perbaiki alamat', 'submitted_at' => now()]);
        $application->histories()->create(['status' => MembershipApplication::REVISION, 'note' => 'Perbaiki alamat', 'revision_data' => ['requested_fields' => ['address']]]);

        $this->actingAs($other)->put(route('member.applications.revision.update', $application), [])->assertNotFound();
        $this->actingAs($owner)->put(route('member.applications.revision.update', $application), [
            'name' => $owner->name, 'email' => $owner->email, 'account_type' => 'individual', 'phone' => '', 'address' => '',
        ])->assertSessionHasErrors(['phone', 'address']);
        $this->assertSame(MembershipApplication::REVISION, $application->fresh()->status);
    }

    public function test_revision_requires_at_least_one_requested_field_and_institution_data_is_validated(): void
    {
        $member = $this->completeMember();
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::SUBMITTED, 'submitted_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.applications.update', $application), [
            'status' => MembershipApplication::REVISION,
            'admin_note' => 'Perbaiki data institusi.',
            'requested_fields' => [],
        ])->assertSessionHasErrors('requested_fields');
        $this->assertSame(MembershipApplication::SUBMITTED, $application->fresh()->status);

        $this->actingAs($admin)->put(route('admin.applications.update', $application), [
            'status' => MembershipApplication::REVISION,
            'admin_note' => 'Perbaiki data institusi.',
            'requested_fields' => ['institution_name', 'responsible_name'],
        ])->assertRedirect();
        $this->actingAs($member)->put(route('member.applications.revision.update', $application), [
            'name' => $member->name, 'email' => $member->email, 'account_type' => 'institution',
            'phone' => '08123456789', 'address' => 'Yogyakarta', 'institution_name' => '', 'responsible_name' => '',
        ])->assertSessionHasErrors(['institution_name', 'responsible_name']);
        $this->assertSame(MembershipApplication::REVISION, $application->fresh()->status);
    }

    private function completeMember(): User
    {
        $member = User::factory()->create(['role' => 'member']);
        MemberProfile::create(['user_id' => $member->id, 'account_type' => 'individual', 'phone' => '08123456789', 'address' => 'Yogyakarta']);
        return $member;
    }
}
