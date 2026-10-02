<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_site_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = SiteContent::create([
            'key' => SiteContent::KEY_PROFILE,
            'title' => 'Profil Lama',
            'body' => 'Konten lama.',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.site-contents.update', $content), [
                'title' => 'Profil Baru',
                'body' => 'Konten profil baru.',
                'metadata' => [],
            ])
            ->assertRedirect(route('admin.site-contents.index'));

        $this->assertDatabaseHas('site_contents', [
            'id' => $content->id,
            'title' => 'Profil Baru',
            'updated_by' => $admin->id,
        ]);
    }

    public function test_public_profile_and_contact_pages_render_content(): void
    {
        SiteContent::create(['key' => SiteContent::KEY_PROFILE, 'title' => 'Profil ASKI', 'body' => 'Isi profil.']);
        SiteContent::create(['key' => SiteContent::KEY_VISION_MISSION, 'title' => 'Visi Misi', 'body' => 'Isi visi misi.']);
        SiteContent::create([
            'key' => SiteContent::KEY_CONTACT,
            'title' => 'Kontak Kami',
            'body' => 'Hubungi kami.',
            'metadata' => ['email' => 'info@example.test'],
        ]);

        $this->get(route('public.profile'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Profile')
                ->where('profile.title', 'Profil ASKI')
                ->where('visionMission.title', 'Visi Misi')
            );

        $this->get(route('public.contact'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Contact')
                ->where('contact.title', 'Kontak Kami')
                ->where('contact.metadata.email', 'info@example.test')
            );
    }

    public function test_member_cannot_access_site_content_management(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get(route('admin.site-contents.index'))
            ->assertForbidden();
    }
}
