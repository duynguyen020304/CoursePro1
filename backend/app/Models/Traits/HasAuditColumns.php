<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Trait HasAuditColumns
 *
 * Provides standard audit columns for Eloquent models:
 * - SoftDeletes (deleted_at timestamp instead of hard delete)
 * - is_active boolean with default true
 * - Query scopes: active(), notDeleted()
 * - Inherited from SoftDeletes: withTrashed(), onlyTrashed(), restore()
 *
 * Usage:
 *   class MyModel extends Model {
 *       use HasAuditColumns;
 *   }
 *
 * Compatible with UUID primary keys (public $incrementing = false; protected $keyType = 'string';)
 */
trait HasAuditColumns
{
    use SoftDeletes;

    /**
     * Boot the HasAuditColumns trait.
     *
     * Sets is_active = true by default when creating a new model.
     */
    protected static function bootHasAuditColumns(): void
    {
        static::creating(function ($model) {
            if (! isset($model->attributes['is_active'])) {
                $model->is_active = true;
            }
        });
    }

    /**
     * Initialize the HasAuditColumns trait.
     *
     * Merges is_active boolean cast with any existing model casts.
     */
    protected function initializeHasAuditColumns(): void
    {
        $this->mergeCasts([
            'is_active' => 'boolean',
        ]);
    }

    /**
     * Scope: only active records (is_active = true).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: only non-deleted records (where deleted_at is null).
     *
     * This is the default behavior of SoftDeletes, but provided
     * as an explicit named scope for readability in complex queries.
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->whereNull($this->getQualifiedDeletedAtColumn());
    }
}
