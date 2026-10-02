<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MembershipApplication extends Model
{
    use HasFactory;

    public const SUBMITTED = 'submitted';
    public const REVISION = 'revision';
    public const APPROVED_WAITING_PAYMENT = 'approved_waiting_payment';
    public const REJECTED = 'rejected';

    protected $fillable = ['user_id', 'status', 'admin_note', 'submitted_at'];

    protected $casts = ['submitted_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function histories(): HasMany { return $this->hasMany(MembershipApplicationHistory::class)->latest(); }
    public function invoice(): HasOne { return $this->hasOne(Invoice::class); }

    public static function statusLabels(): array
    {
        return [self::SUBMITTED => 'Diajukan', self::REVISION => 'Perlu revisi', self::APPROVED_WAITING_PAYMENT => 'Disetujui - menunggu pembayaran', self::REJECTED => 'Ditolak'];
    }
}
