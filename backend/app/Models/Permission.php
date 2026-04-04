<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasAuditColumns;

    protected $primaryKey = 'permission_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'permission_id',
        'name',
        'display_name',
        'description',
        'is_active',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }
}