<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseRequirement extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['course_id', 'requirement_id'];
    public $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['requirement_id', 'course_id', 'requirement'];

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
}
