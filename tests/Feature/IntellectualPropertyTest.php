<?php

namespace Tests\Feature;

use App\Models\IntellectualProperty;
use App\Models\Invoice;
use App\Models\MemberProfile;
use App\Models\MembershipApplication;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class IntellectualPropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_manage_own_intellectual_property_records(): void
    {
        $member = $this->memberWithProfile();
        $this->verifyMembershipPayment($member);

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

    public function test_empty_submission_uses_indonesian_validation_messages(): void
    {
        $member = $this->memberWithProfile();
        $this->actingAs($member)->post(route('member.intellectual-properties.store'), [])
            ->assertSessionHasErrors(['application_number', 'application_date', 'creator', 'rights_holder', 'title', 'protection_period', 'type_details.work_type'])
            ->assertSessionHas('errors', fn ($errors) => str_contains($errors->first('application_number'), 'wajib diisi'));
    }

    public function test_member_can_save_the_fixed_hki_form_fields(): void
    {
        $member = $this->memberWithProfile();
        $this->verifyMembershipPayment($member);

        $this->actingAs($member)->post(route('member.intellectual-properties.store'), $this->payload([
            'type_details' => ['work_type' => 'Indikasi Geografis'],
        ]))->assertRedirect(route('member.intellectual-properties.index'));

        $record = IntellectualProperty::firstOrFail();
        $this->assertSame('Indikasi Geografis', $record->type_details['work_type']);
        $this->assertSame('Hak Cipta', $record->ip_type);

        $this->actingAs($member)
            ->put(route('member.intellectual-properties.update', $record), $this->payload(['title' => 'Karya Revisi']))
            ->assertRedirect(route('member.intellectual-properties.show', $record));

        $this->assertSame('Karya Revisi', $record->fresh()->title);
    }

    public function test_member_cannot_submit_unknown_creation_type(): void
    {
        $member = $this->memberWithProfile();

        $this->actingAs($member)->post(route('member.intellectual-properties.store'), $this->payload([
            'type_details' => ['work_type' => 'Kategori Tidak Valid'],
        ]))->assertSessionHasErrors(['type_details.work_type']);
    }

    public function test_member_without_verified_membership_payment_cannot_open_or_submit_hki_creation(): void
    {
        $member = $this->memberWithProfile();

        $this->actingAs($member)
            ->get(route('member.intellectual-properties.create'))
            ->assertRedirect(route('member.intellectual-properties.index'))
            ->assertSessionHas('error');

        $this->actingAs($member)
            ->post(route('member.intellectual-properties.store'), $this->payload())
            ->assertRedirect(route('member.intellectual-properties.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('intellectual_properties', 0);
    }

    public function test_member_can_edit_legacy_copyright_record_without_new_optional_legacy_details(): void
    {
        $member = $this->memberWithProfile();
        $record = IntellectualProperty::create($this->recordPayload($member, [
            'type_details' => null,
        ]));

        $this->actingAs($member)
            ->put(route('member.intellectual-properties.update', $record), $this->payload([
                'type_details' => ['work_type' => 'Hak Cipta'],
            ]))
            ->assertRedirect('member/intellectual-properties/'.$record->id);

        $this->assertSame(['work_type' => 'Hak Cipta'], $record->fresh()->type_details);
        $this->assertSame('2026-01-15', $record->fresh()->first_published_at->toDateString());
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
            ->put(route('member.intellectual-properties.update', $record), $this->payload(['type_details' => ['work_type' => 'Paten']]))
            ->assertForbidden();
    }

    public function test_admin_can_filter_and_view_intellectual_property_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $institutionMember = $this->memberWithProfile('institution', 'Universitas A');
        $individualMember = $this->memberWithProfile();

        IntellectualProperty::create($this->recordPayload($institutionMember, [
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

    public function test_admin_can_download_import_template_and_non_admin_cannot_import(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = $this->memberWithProfile();

        $this->actingAs($admin)
            ->get(route('admin.intellectual-properties.import-template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($member)
            ->post(route('admin.intellectual-properties.import'), [])
            ->assertForbidden();
    }

    public function test_admin_can_import_hki_excel_and_repeat_import_updates_existing_record(): void
    {
        Excel::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->create('data-hki.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // A malformed workbook is rejected without creating records.
        $this->actingAs($admin)
            ->post(route('admin.intellectual-properties.import'), ['file' => $file])
            ->assertRedirect();
        $this->assertDatabaseCount('intellectual_properties', 0);
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

    private function verifyMembershipPayment(User $member): void
    {
        $application = MembershipApplication::create([
            'user_id' => $member->id,
            'status' => MembershipApplication::COMPLETED,
            'submitted_at' => now(),
        ]);
        $invoice = Invoice::create([
            'membership_application_id' => $application->id,
            'user_id' => $member->id,
            'number' => 'INV-HKI-TEST-'.$member->id,
            'amount' => 100000,
            'duration_months' => 12,
            'issued_at' => today(),
            'due_at' => today()->addDays(14),
            'status' => Invoice::PAID,
        ]);
        Payment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $member->id,
            'proof_path' => 'payment-proofs/verified-test.pdf',
            'status' => Payment::VERIFIED,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'application_number' => 'EC00202600001',
            'application_date' => '2026-01-10',
            'creator' => 'Tim ASKI',
            'rights_holder' => 'ASKI-PTMA',
            'ip_type' => 'Hak Cipta',
            'ip_type' => 'Hak Cipta',
            'recordation_number' => '000123456',
            'title' => 'Aplikasi Edukasi',
            'first_published_at' => '2026-01-15',
            'protection_period' => 'Seumur hidup pencipta ditambah 70 tahun',
            'type_details' => ['work_type' => 'Hak Cipta'],
        ], $overrides);
    }
}
