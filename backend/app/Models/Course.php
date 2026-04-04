<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasAuditColumns;

    protected $primaryKey = 'course_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function getRouteKeyName()
    {
        return 'course_id';
    }

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'price',
        'difficulty',
        'language',
        'created_by',
        'is_active',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'created_by', 'instructor_id');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'course_instructor', 'course_id', 'instructor_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'course_category', 'course_id', 'category_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(CourseChapter::class, 'course_id', 'course_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CourseImage::class, 'course_id', 'course_id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(CourseObjective::class, 'course_id', 'course_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(CourseRequirement::class, 'course_id', 'course_id');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'course_id', 'course_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'course_id', 'course_id');
    }

    /**
     * Get the students who purchased this course.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'orders', 'course_id', 'user_id')
            ->distinct();
    }
}
