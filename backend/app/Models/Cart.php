<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasAuditColumns;

    protected $primaryKey = 'cart_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['cart_id', 'user_id', 'is_active'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'cart_id');
    }
}
