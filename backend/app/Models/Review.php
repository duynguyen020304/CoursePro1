<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasAuditColumns;

    protected $primaryKey = 'review_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['review_id', 'user_id', 'course_id', 'rating', 'review_text', 'is_active'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
