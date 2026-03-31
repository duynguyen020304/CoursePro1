<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseObjective extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['course_id', 'objective_id'];
    public $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['objective_id', 'course_id', 'objective'];

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
