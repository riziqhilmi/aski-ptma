<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class IssuedMembershipDocument extends Model
{
    public const CERTIFICATE = 'certificate';
    public const CARD = 'card';
    protected $fillable = ['membership_id','user_id','type','number','file_path','snapshot','issued_at'];
    protected $casts = ['snapshot' => 'array', 'issued_at' => 'datetime'];
    public function membership(): BelongsTo { return $this->belongsTo(Membership::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
