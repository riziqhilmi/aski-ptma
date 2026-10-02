<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    public const UNPAID = 'unpaid';
    protected $fillable = ['membership_application_id', 'user_id', 'membership_rate_id', 'number', 'amount', 'duration_months', 'issued_at', 'due_at', 'status'];
    protected $casts = ['amount' => 'decimal:2', 'issued_at' => 'date', 'due_at' => 'date'];
    public function application(): BelongsTo { return $this->belongsTo(MembershipApplication::class, 'membership_application_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function rate(): BelongsTo { return $this->belongsTo(MembershipRate::class, 'membership_rate_id'); }
    public function payments() { return $this->hasMany(Payment::class)->latest(); }
    public function membership() { return $this->hasOne(Membership::class); }
}
