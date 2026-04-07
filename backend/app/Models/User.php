<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasApiTokens, HasFactory, Notifiable, HasAuditColumns;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'role_id',
        'profile_image',
        'is_active',
    ];

    protected $hidden = [];

    protected function casts(): array
    {
        return [];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function userAccount(): HasOne
    {
        return $this->hasOne(UserAccount::class, 'user_id', 'user_id');
    }

    public function instructor(): HasOne
    {
        return $this->hasOne(Instructor::class, 'user_id', 'user_id');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id', 'user_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by', 'instructor_id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'user_id', 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id', 'user_id');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->role_id === $roleName;
    }

    public function hasPermission(string $permissionName): bool
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->hasPermission($permissionName);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasAnyPermission($permissions);
    }

    public function hasAllPermissions(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasAllPermissions($permissions);
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->hasPermission('admin.access');
    }

    public function canAccessInstructorPanel(): bool
    {
        return $this->hasAnyPermission(['instructor.access', 'admin.access']);
    }

    public function canManageAnyCourse(): bool
    {
        return $this->hasAnyPermission(['courses.manage.any', 'admin.access']);
    }

    public function canManageOwnCourses(): bool
    {
        return $this->canManageAnyCourse() || $this->hasPermission('courses.manage.own');
    }

    public function canManageOrders(): bool
    {
        return $this->hasAnyPermission(['orders.manage', 'admin.access']);
    }

    public function canManagePayments(): bool
    {
        return $this->hasAnyPermission(['payments.manage', 'admin.access']);
    }

    /**
     * Accessor for email (delegates to userAccount)
     */
    public function getEmailAttribute(): ?string
    {
        return $this->userAccount?->email;
    }

    /**
     * Accessor to check if user is verified
     */
    public function getIsVerifiedAttribute(): bool
    {
        return $this->userAccount?->is_verified ?? false;
    }
}
