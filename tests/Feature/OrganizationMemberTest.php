<?php

namespace Tests\Feature;

use App\Models\OrganizationMember;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizationMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_organization_members(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.organization-members.store'), [
                'name' => 'Dr. Aisyah',
                'position' => 'Ketua',
                'description' => 'Koordinator organisasi.',
                'sort_order' => 1,
                'is_active' => true,
                'photo' => UploadedFile::fake()->image('aisyah.jpg', 800, 800),
            ])
            ->assertRedirect(route('admin.organization-members.index'));

        $member = OrganizationMember::firstOrFail();
        Storage::disk('local')->assertExists($member->photo_path);

        $this->actingAs($admin)
            ->post(route('admin.organization-members.update', $member), [
                '_method' => 'put',
                'name' => 'Dr. Aisyah Revisi',
                'position' => 'Ketua Umum',
                'description' => 'Koordinator organisasi.',
                'sort_order' => 2,
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.organization-members.index'));

        $this->assertDatabaseHas('organization_members', [
            'id' => $member->id,
            'name' => 'Dr. Aisyah Revisi',
            'is_active' => false,
        ]);
    }

    public function test_public_profile_shows_only_active_organization_members(): void
    {
        SiteContent::create(['key' => SiteContent::KEY_PROFILE, 'title' => 'Profil', 'body' => 'Profil.']);
        SiteContent::create(['key' => SiteContent::KEY_VISION_MISSION, 'title' => 'Visi Misi', 'body' => 'Visi.']);
        OrganizationMember::create(['name' => 'Aktif', 'position' => 'Ketua', 'sort_order' => 1, 'is_active' => true]);
        OrganizationMember::create(['name' => 'Nonaktif', 'position' => 'Wakil', 'sort_order' => 2, 'is_active' => false]);

        $this->get(route('public.profile'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Profile')
                ->has('organizationMembers', 1)
                ->where('organizationMembers.0.name', 'Aktif')
            );
    }

    public function test_member_cannot_access_organization_management(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get(route('admin.organization-members.index'))
            ->assertForbidden();
    }
}
