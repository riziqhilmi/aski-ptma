<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_publish_and_update_article(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Kegiatan ASKI PTMA',
                'slug' => '',
                'excerpt' => 'Ringkasan kegiatan ASKI PTMA.',
                'body' => 'Isi artikel kegiatan ASKI PTMA.',
                'status' => Article::STATUS_PUBLISHED,
                'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 800),
            ])
            ->assertRedirect(route('admin.articles.index'));

        $article = Article::firstOrFail();
        $this->assertSame('kegiatan-aski-ptma', $article->slug);
        $this->assertNotNull($article->published_at);
        Storage::disk('local')->assertExists($article->cover_image_path);

        $this->actingAs($admin)
            ->post(route('admin.articles.update', $article), [
                '_method' => 'put',
                'title' => 'Kegiatan ASKI PTMA Revisi',
                'slug' => $article->slug,
                'excerpt' => 'Ringkasan revisi.',
                'body' => 'Isi artikel revisi.',
                'status' => Article::STATUS_DRAFT,
            ])
            ->assertRedirect(route('admin.articles.show', $article));

        $this->assertSame(Article::STATUS_DRAFT, $article->fresh()->status);
        $this->assertNull($article->fresh()->published_at);
    }

    public function test_public_only_sees_published_articles_and_can_open_cover(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $published = $this->article($admin, Article::STATUS_PUBLISHED, 'berita-terbit');
        $draft = $this->article($admin, Article::STATUS_DRAFT, 'berita-draft');

        $this->get(route('public.articles.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Articles/Index')
                ->has('articles.data', 1)
                ->where('articles.data.0.slug', 'berita-terbit')
            );

        $this->get(route('public.articles.show', $published))->assertOk();
        $this->get(route('public.articles.show', $draft))->assertNotFound();
        $this->get(route('public.articles.cover', $published))->assertOk();
        $this->get(route('public.articles.cover', $draft))->assertNotFound();
    }

    public function test_member_cannot_access_admin_article_management(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get(route('admin.articles.index'))
            ->assertForbidden();
    }

    private function article(User $admin, string $status, string $slug): Article
    {
        $path = UploadedFile::fake()
            ->image($slug.'.jpg', 1200, 800)
            ->store('article-images', 'local');

        return Article::create([
            'author_id' => $admin->id,
            'title' => str_replace('-', ' ', $slug),
            'slug' => $slug,
            'cover_image_path' => $path,
            'cover_image_mime' => 'image/jpeg',
            'excerpt' => 'Ringkasan artikel.',
            'body' => 'Isi artikel lengkap.',
            'status' => $status,
            'published_at' => $status === Article::STATUS_PUBLISHED ? now() : null,
        ]);
    }
}
