<?php

namespace Tests\Feature;

use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_complete_an_individual_profile(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->put(route('member.profile.update'), [
            'name' => 'Siti Aminah',
            'email' => 'siti@example.test',
            'account_type' => 'individual',
            'phone' => '08123456789',
            'address' => 'Yogyakarta',
        ])->assertRedirect(route('member.profile.edit'));

        $this->assertDatabaseHas('member_profiles', [
            'user_id' => $member->id,
            'account_type' => 'individual',
            'phone' => '08123456789',
        ]);
        $this->assertSame('lengkap', $member->fresh()->memberProfile->completion_status);
    }

    public function test_institution_profile_requires_institution_and_responsible_person(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->put(route('member.profile.update'), [
            'name' => $member->name,
            'email' => $member->email,
            'account_type' => 'institution',
            'phone' => '08123456789',
            'address' => 'Yogyakarta',
        ])->assertSessionHasErrors(['institution_name', 'responsible_name']);
    }

    public function test_admin_can_filter_and_view_member_account_detail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member', 'name' => 'Member Institusi']);
        MemberProfile::create([
            'user_id' => $member->id,
            'account_type' => 'institution',
            'phone' => '08123456789',
            'address' => 'Bandung',
            'institution_name' => 'Universitas Contoh',
            'responsible_name' => 'Dr. Penanggung Jawab',
        ]);

        $this->actingAs($admin)->get(route('admin.accounts.index', ['type' => 'institution']))
            ->assertOk()
            ->assertSee('Member Institusi');

        $this->actingAs($admin)->get(route('admin.accounts.show', $member))->assertOk()->assertSee('Universitas Contoh');
    }

    public function test_member_cannot_access_account_management(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get(route('admin.accounts.index'))->assertForbidden();
    }

    public function test_admin_account_filters_include_account_type_completion_and_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $individual = User::factory()->create(['role' => 'member', 'name' => 'Akun Individu']);
        MemberProfile::create(['user_id' => $individual->id, 'account_type' => 'individual', 'phone' => '08123456789', 'address' => 'Bandung']);
        $institution = User::factory()->create(['role' => 'member', 'name' => 'Akun Institusi']);
        MemberProfile::create(['user_id' => $institution->id, 'account_type' => 'institution', 'phone' => '08123456780', 'address' => 'Solo', 'institution_name' => 'Kampus Contoh', 'responsible_name' => 'Penanggung Jawab']);

        $this->actingAs($admin)->get(route('admin.accounts.index', ['type' => 'institution', 'status' => 'lengkap']))
            ->assertOk()->assertSee('Akun Institusi')->assertDontSee('Akun Individu');
        $this->actingAs($admin)->get(route('admin.accounts.index', ['q' => 'Akun Institusi']))
            ->assertOk()->assertSee('Akun Institusi')->assertDontSee('Akun Individu');
        $this->actingAs($admin)->get(route('admin.accounts.index', ['status' => 'belum_lengkap']))
            ->assertOk()->assertSee('Member');
    }
}
