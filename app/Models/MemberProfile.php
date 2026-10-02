<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_type',
        'phone',
        'address',
        'institution_name',
        'responsible_name',
        'responsible_position',
    ];

    protected $appends = ['completion_status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isComplete(): bool
    {
        if (! in_array($this->account_type, ['individual', 'institution'], true) || blank($this->phone) || blank($this->address)) {
            return false;
        }

        return $this->account_type === 'individual'
            || (filled($this->institution_name) && filled($this->responsible_name));
    }

    public function getCompletionStatusAttribute(): string
    {
        return $this->isComplete() ? 'lengkap' : 'belum lengkap';
    }
}
