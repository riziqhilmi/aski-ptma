<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = ['bank_name', 'account_number', 'account_holder'];
    protected $appends = ['is_configured'];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function isConfigured(): bool
    {
        return filled($this->bank_name) && filled($this->account_number) && filled($this->account_holder);
    }

    public function getIsConfiguredAttribute(): bool
    {
        return $this->isConfigured();
    }
}
