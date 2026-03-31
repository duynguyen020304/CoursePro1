<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLesson extends Model
{
    protected $primaryKey = 'lesson_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['lesson_id', 'course_id', 'chapter_id', 'title', 'content', 'sort_order'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(CourseChapter::class, 'chapter_id', 'chapter_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(CourseVideo::class, 'lesson_id', 'lesson_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(CourseResource::class, 'lesson_id', 'lesson_id');
    }
}
