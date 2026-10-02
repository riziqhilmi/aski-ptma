<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntellectualProperty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'application_number',
        'application_date',
        'creator',
        'rights_holder',
        'ip_type',
        'recordation_number',
        'title',
        'first_published_at',
        'protection_period',
    ];

    protected $casts = [
        'application_date' => 'date',
        'first_published_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
