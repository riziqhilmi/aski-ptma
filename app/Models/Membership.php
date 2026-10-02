<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    protected $fillable = ['user_id', 'membership_application_id', 'invoice_id', 'starts_at', 'ends_at', 'status'];
    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
