<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationMember extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'position',
        'description',
        'photo_path',
        'photo_mime',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'photo_url',
        'status_label',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? route('public.organization-members.photo', $this) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }
}
