<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseImage extends Model
{
    use HasAuditColumns;

    protected $primaryKey = 'image_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['image_id', 'course_id', 'image_path', 'caption', 'is_primary', 'sort_order', 'is_active'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
