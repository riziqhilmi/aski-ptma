<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicArticleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Articles/Index', [
            'articles' => Article::with('author')
                ->where('status', Article::STATUS_PUBLISHED)
                ->latest('published_at')
                ->paginate(9),
        ]);
    }

    public function show(Article $article): Response
    {
        abort_unless($article->status === Article::STATUS_PUBLISHED, 404);

        return Inertia::render('Public/Articles/Show', [
            'article' => $article->load('author'),
        ]);
    }

    public function cover(Article $article): StreamedResponse
    {
        abort_unless($article->status === Article::STATUS_PUBLISHED && $article->cover_image_path, 404);
        abort_unless(Storage::disk('local')->exists($article->cover_image_path), 404);

        return Storage::disk('local')->response($article->cover_image_path, null, [
            'Content-Type' => $article->cover_image_mime ?: 'image/jpeg',
        ]);
    }
}
