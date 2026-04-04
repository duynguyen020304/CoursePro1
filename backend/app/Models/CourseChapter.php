<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseChapter extends Model
{
    use HasAuditColumns;

    protected $primaryKey = 'chapter_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function getRouteKeyName()
    {
        return 'chapter_id';
    }

    protected $fillable = ['chapter_id', 'course_id', 'title', 'description', 'sort_order', 'is_active'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class, 'chapter_id', 'chapter_id');
    }
}
