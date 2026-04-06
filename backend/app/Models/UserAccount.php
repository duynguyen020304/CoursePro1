<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class UserAccount extends Authenticatable
{
    use HasApiTokens, HasFactory, HasAuditColumns;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_account_id',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'is_active',
        'is_verified',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the name for authentication (required by Authenticatable)
     */
    public function getNameAttribute(): string
    {
        return $this->user ? "{$this->user->first_name} {$this->user->last_name}" : $this->email;
    }

    /**
     * Find by email for authentication
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)
            ->where('provider', 'email')
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Delegate role check to User model
     */
    public function hasRole(string $roleName): bool
    {
        return $this->user?->hasRole($roleName) ?? false;
    }

    /**
     * Delegate permission check to User model
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->user?->hasPermission($permissionName) ?? false;
    }

    /**
     * Delegate any permission check to User model
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->user?->hasAnyPermission($permissions) ?? false;
    }

    /**
     * Delegate all permissions check to User model
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return $this->user?->hasAllPermissions($permissions) ?? false;
    }

    /**
     * Get the user's role (delegates to User model)
     */
    public function getRoleAttribute(): ?\App\Models\Role
    {
        return $this->user?->role;
    }

    /**
     * Get role_id attribute (delegates to User model)
     */
    public function getRoleIdAttribute(): ?string
    {
        return $this->user?->role_id;
    }

    /**
     * Get the instructor profile (delegates to User model)
     * Allows $request->user()->instructor to work in controllers
     */
    public function getInstructorAttribute()
    {
        return $this->user?->instructor;
    }

    /**
     * Get the student profile (delegates to User model)
     * Allows $request->user()->student to work in controllers
     */
    public function getStudentAttribute()
    {
        return $this->user?->student;
    }
}