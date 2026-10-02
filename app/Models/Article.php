<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
    ];

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'cover_image_path',
        'cover_image_mime',
        'excerpt',
        'body',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'cover_image_url',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === self::STATUS_PUBLISHED ? 'Terbit' : 'Draft';
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image_path ? route('public.articles.cover', $this->slug) : null;
    }
}
