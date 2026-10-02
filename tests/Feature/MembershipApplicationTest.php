<?php

namespace Tests\Feature;

use App\Models\MemberProfile;
use App\Models\MembershipApplication;
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

    public function test_member_with_complete_profile_can_submit_and_revision_can_be_resubmitted(): void
    {
        $member = $this->completeMember();
        $this->actingAs($member)->post(route('member.applications.store'))->assertRedirect(route('member.applications.index'));
        $application = MembershipApplication::firstOrFail();
        $this->assertSame(MembershipApplication::SUBMITTED, $application->status);
        $this->assertDatabaseHas('membership_application_histories', ['membership_application_id' => $application->id, 'status' => MembershipApplication::SUBMITTED]);

        $application->update(['status' => MembershipApplication::REVISION, 'admin_note' => 'Perbarui data']);
        $this->actingAs($member)->post(route('member.applications.store'))->assertRedirect();
        $this->assertSame(MembershipApplication::SUBMITTED, $application->fresh()->status);
        $this->assertDatabaseCount('membership_application_histories', 2);
    }

    public function test_admin_can_request_revision_and_member_cannot_access_admin_application_pages(): void
    {
        $member = $this->completeMember();
        $application = MembershipApplication::create(['user_id' => $member->id, 'status' => MembershipApplication::SUBMITTED, 'submitted_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.applications.update', $application), ['status' => MembershipApplication::REVISION, 'admin_note' => 'Mohon lengkapi alamat.'])->assertRedirect();
        $this->assertSame(MembershipApplication::REVISION, $application->fresh()->status);
        $this->assertDatabaseHas('membership_application_histories', ['membership_application_id' => $application->id, 'status' => MembershipApplication::REVISION, 'changed_by' => $admin->id]);
        $this->actingAs($member)->get(route('admin.applications.index'))->assertForbidden();
    }

    private function completeMember(): User
    {
        $member = User::factory()->create(['role' => 'member']);
        MemberProfile::create(['user_id' => $member->id, 'account_type' => 'individual', 'phone' => '08123456789', 'address' => 'Yogyakarta']);
        return $member;
    }
}
