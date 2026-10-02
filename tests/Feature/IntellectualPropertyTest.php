<?php

namespace Tests\Feature;

use App\Models\IntellectualProperty;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntellectualPropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_manage_own_intellectual_property_records(): void
    {
        $member = $this->memberWithProfile();

        $payload = $this->payload(['title' => 'Aplikasi Edukasi ASKI']);

        $this->actingAs($member)
            ->post(route('member.intellectual-properties.store'), $payload)
            ->assertRedirect(route('member.intellectual-properties.index'));

        $record = IntellectualProperty::firstOrFail();

        $this->assertSame($member->id, $record->user_id);
        $this->assertDatabaseHas('intellectual_properties', ['title' => 'Aplikasi Edukasi ASKI']);

        $this->actingAs($member)
            ->put(route('member.intellectual-properties.update', $record), $this->payload(['title' => 'Aplikasi Edukasi ASKI Revisi']))
            ->assertRedirect(route('member.intellectual-properties.show', $record));

        $this->assertDatabaseHas('intellectual_properties', ['title' => 'Aplikasi Edukasi ASKI Revisi']);

        $this->actingAs($member)
            ->delete(route('member.intellectual-properties.destroy', $record))
            ->assertRedirect(route('member.intellectual-properties.index'));

        $this->assertSoftDeleted('intellectual_properties', ['id' => $record->id]);
    }

    public function test_member_cannot_access_another_members_record(): void
    {
        $owner = $this->memberWithProfile();
        $otherMember = $this->memberWithProfile();
        $record = IntellectualProperty::create($this->recordPayload($owner));

        $this->actingAs($otherMember)
            ->get(route('member.intellectual-properties.show', $record))
            ->assertForbidden();

        $this->actingAs($otherMember)
            ->put(route('member.intellectual-properties.update', $record), $this->payload(['title' => 'Tidak Boleh']))
            ->assertForbidden();
    }

    public function test_admin_can_filter_and_view_intellectual_property_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $institutionMember = $this->memberWithProfile('institution', 'Universitas A');
        $individualMember = $this->memberWithProfile();

        IntellectualProperty::create($this->recordPayload($institutionMember, [
            'ip_type' => 'Hak Cipta',
            'title' => 'Karya Institusi',
        ]));
        IntellectualProperty::create($this->recordPayload($individualMember, [
            'ip_type' => 'Paten',
            'title' => 'Karya Individu',
        ]));

        $this->actingAs($admin)
            ->get(route('admin.intellectual-properties.index', ['type' => 'Hak Cipta', 'institution' => 'Universitas A']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/IntellectualProperties/Index')
                ->where('summary.total', 1)
                ->has('records.data', 1)
                ->where('records.data.0.title', 'Karya Institusi')
            );
    }

    public function test_admin_can_export_filtered_records_to_excel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = $this->memberWithProfile('institution', 'Universitas A');
        IntellectualProperty::create($this->recordPayload($member, ['ip_type' => 'Hak Cipta']));

        $response = $this->actingAs($admin)
            ->get(route('admin.intellectual-properties.export', ['type' => 'Hak Cipta']));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    private function memberWithProfile(string $accountType = 'individual', ?string $institutionName = null): User
    {
        $member = User::factory()->create(['role' => 'member']);
        MemberProfile::create([
            'user_id' => $member->id,
            'account_type' => $accountType,
            'phone' => '08123456789',
            'address' => 'Yogyakarta',
            'institution_name' => $institutionName,
            'responsible_person' => $accountType === 'institution' ? 'Ketua LPPM' : null,
        ]);

        return $member;
    }

    private function recordPayload(User $user, array $overrides = []): array
    {
        return array_merge($this->payload(), ['user_id' => $user->id], $overrides);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'application_number' => 'EC00202600001',
            'application_date' => '2026-01-10',
            'creator' => 'Tim ASKI',
            'rights_holder' => 'ASKI-PTMA',
            'ip_type' => 'Hak Cipta',
            'recordation_number' => '000123456',
            'title' => 'Aplikasi Edukasi',
            'first_published_at' => '2026-01-15',
            'protection_period' => 'Seumur hidup pencipta ditambah 70 tahun',
        ], $overrides);
    }
}
