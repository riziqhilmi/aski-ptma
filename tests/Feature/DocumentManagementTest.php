<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_publish_and_download_a_document(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'title' => 'Pedoman Keanggotaan',
                'category' => Document::CATEGORY_GUIDE,
                'status' => Document::STATUS_PUBLISHED,
                'description' => 'Pedoman resmi member.',
                'file' => UploadedFile::fake()->create('pedoman.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.documents.index'));

        $document = Document::firstOrFail();

        $this->assertSame($admin->id, $document->uploaded_by);
        $this->assertNotNull($document->published_at);
        Storage::disk('local')->assertExists($document->file_path);

        $this->actingAs($admin)
            ->get(route('admin.documents.download', $document))
            ->assertOk();
    }

    public function test_member_only_sees_and_downloads_published_documents(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        $published = $this->document($admin, Document::STATUS_PUBLISHED, 'Pedoman Terbit');
        $draft = $this->document($admin, Document::STATUS_DRAFT, 'Pedoman Draft');

        $this->actingAs($member)
            ->get(route('member.documents.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Member/Documents/Index')
                ->has('documents.data', 1)
                ->where('documents.data.0.title', 'Pedoman Terbit')
            );

        $this->actingAs($member)
            ->get(route('member.documents.download', $published))
            ->assertOk();

        $this->actingAs($member)
            ->get(route('member.documents.download', $draft))
            ->assertForbidden();
    }

    public function test_document_upload_requires_pdf_and_member_cannot_access_admin_documents(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'title' => 'File Salah',
                'category' => Document::CATEGORY_GUIDE,
                'status' => Document::STATUS_DRAFT,
                'file' => UploadedFile::fake()->image('gambar.jpg'),
            ])
            ->assertSessionHasErrors('file');

        $this->actingAs($member)
            ->get(route('admin.documents.index'))
            ->assertForbidden();
    }

    private function document(User $admin, string $status, string $title): Document
    {
        $path = UploadedFile::fake()
            ->create($title.'.pdf', 80, 'application/pdf')
            ->store('documents', 'local');

        return Document::create([
            'uploaded_by' => $admin->id,
            'title' => $title,
            'category' => Document::CATEGORY_GUIDE,
            'status' => $status,
            'description' => 'Dokumen pengujian.',
            'file_path' => $path,
            'original_filename' => $title.'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 80000,
            'published_at' => $status === Document::STATUS_PUBLISHED ? now() : null,
        ]);
    }
}
