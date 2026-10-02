<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $article = $this->route('article');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('articles', 'slug')->ignore($article?->id),
            ],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::in(Article::STATUSES)],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul',
            'slug' => 'slug',
            'excerpt' => 'ringkasan',
            'body' => 'isi artikel',
            'status' => 'status',
            'cover_image' => 'gambar utama',
        ];
    }
}
