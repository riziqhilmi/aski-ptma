<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipRate extends Model
{
    use SoftDeletes;

    protected $fillable = ['account_type', 'name', 'amount', 'duration_months', 'active_from', 'active_until'];
    protected $casts = ['amount' => 'decimal:2', 'active_from' => 'date', 'active_until' => 'date'];

    public function scopeActiveFor($query, string $accountType, $date)
    {
        return $query->where('account_type', $accountType)->whereDate('active_from', '<=', $date)->where(fn ($rates) => $rates->whereNull('active_until')->orWhereDate('active_until', '>=', $date));
    }
}
