<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORY_GUIDE = 'guide';
    public const CATEGORY_NOTIFICATION = 'notification';
    public const CATEGORY_CERTIFICATE_TEMPLATE = 'certificate_template';
    public const CATEGORY_CARD_TEMPLATE = 'card_template';
    public const CATEGORY_OTHER_TEMPLATE = 'other_template';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const CATEGORIES = [
        self::CATEGORY_GUIDE,
        self::CATEGORY_NOTIFICATION,
        self::CATEGORY_CERTIFICATE_TEMPLATE,
        self::CATEGORY_CARD_TEMPLATE,
        self::CATEGORY_OTHER_TEMPLATE,
    ];

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
    ];

    protected $fillable = [
        'uploaded_by',
        'title',
        'category',
        'status',
        'description',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'category_label',
        'status_label',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_GUIDE => 'Pedoman',
            self::CATEGORY_NOTIFICATION => 'Notifikasi',
            self::CATEGORY_CERTIFICATE_TEMPLATE => 'Template Sertifikat',
            self::CATEGORY_CARD_TEMPLATE => 'Template Kartu Anggota',
            self::CATEGORY_OTHER_TEMPLATE => 'Template Dokumen',
            default => 'Dokumen',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === self::STATUS_PUBLISHED ? 'Terbit' : 'Draft';
    }
}
