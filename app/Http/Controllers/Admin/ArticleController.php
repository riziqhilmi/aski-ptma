<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Article::class);

        $status = $request->string('status')->toString();

        return Inertia::render('Admin/Articles/Index', [
            'articles' => Article::with('author')
                ->when(in_array($status, Article::STATUSES, true), fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => ['status' => $status],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Article::class);

        return Inertia::render('Admin/Articles/Form', [
            'article' => null,
        ]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $this->authorize('create', Article::class);
        $data = $request->validated();
        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('article-images', 'local')
            : null;

        Article::create([
            'author_id' => $request->user()->id,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['title']),
            'excerpt' => $data['excerpt'],
            'body' => $data['body'],
            'status' => $data['status'],
            'published_at' => $data['status'] === Article::STATUS_PUBLISHED ? now() : null,
            'cover_image_path' => $coverPath,
            'cover_image_mime' => $request->file('cover_image')?->getMimeType(),
        ]);

        return to_route('admin.articles.index')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function show(Article $article): Response
    {
        $this->authorize('view', $article);

        return Inertia::render('Admin/Articles/Show', [
            'article' => $article->load('author'),
        ]);
    }

    public function edit(Article $article): Response
    {
        $this->authorize('update', $article);

        return Inertia::render('Admin/Articles/Form', [
            'article' => $article,
        ]);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);
        $data = $request->validated();
        $payload = [
            'title' => $data['title'],
            'slug' => $data['slug'] ?: $article->slug,
            'excerpt' => $data['excerpt'],
            'body' => $data['body'],
            'status' => $data['status'],
            'published_at' => $data['status'] === Article::STATUS_PUBLISHED
                ? ($article->published_at ?? now())
                : null,
        ];

        if ($request->hasFile('cover_image')) {
            Storage::disk('local')->delete($article->cover_image_path);
            $payload['cover_image_path'] = $request->file('cover_image')->store('article-images', 'local');
            $payload['cover_image_mime'] = $request->file('cover_image')->getMimeType();
        }

        $article->update($payload);

        return to_route('admin.articles.show', $article)->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->authorize('delete', $article);

        $article->delete();

        return to_route('admin.articles.index')->with('success', 'Artikel berhasil dihapus.');
    }

    private function uniqueSlug(string $value): string
    {
        $slug = Str::slug($value) ?: Str::random(8);
        $base = $slug;
        $counter = 2;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
