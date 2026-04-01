<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseChapter extends Model
{
    protected $primaryKey = 'chapter_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function getRouteKeyName()
    {
        return 'chapter_id';
    }

    protected $fillable = ['chapter_id', 'course_id', 'title', 'description', 'sort_order'];

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

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class, 'chapter_id', 'chapter_id');
    }
}
