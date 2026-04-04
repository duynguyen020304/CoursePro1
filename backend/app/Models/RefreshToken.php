<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    use HasFactory, HasAuditColumns;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'token',
        'expires_at',
        'ip_address',
        'user_agent',
        'is_revoked',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'user_id' => 'string',
            'expires_at' => 'datetime',
            'is_revoked' => 'boolean',
        ];
    }

    /**
     * Relationship to UserAccount
     */
    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }

    /**
     * Relationship to User (via UserAccount)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Check if token is valid (not revoked, deleted, or expired)
     */
    public function isValid(): bool
    {
        return !$this->is_revoked
            && !$this->trashed()
            && $this->expires_at->isFuture();
    }

    /**
     * Check if token needs rotation (less than 24 hours remaining)
     */
    public function needsRotation(): bool
    {
        return $this->expires_at->diffInHours(now()) < 24;
    }

    /**
     * Revoke this token
     */
    public function revoke(): void
    {
        $this->update(['is_revoked' => true]);
    }

    /**
     * Delete this token (uses SoftDeletes via HasAuditColumns)
     */
    public function softDelete(): void
    {
        $this->delete();
    }

    /**
     * Find valid token by hashed token value
     */
    public static function findValidByToken(string $hashedToken): ?self
    {
        return static::where('token', $hashedToken)
            ->where('is_revoked', false)
            ->whereNull('deleted_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Revoke all tokens for a user
     */
    public static function revokeAllForUser(string $userId): int
    {
        return static::where('user_id', $userId)
            ->where('is_revoked', false)
            ->update(['is_revoked' => true]);
    }
}
