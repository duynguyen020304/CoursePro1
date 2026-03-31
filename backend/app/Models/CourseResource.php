<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseResource extends Model
{
    protected $primaryKey = 'resource_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['resource_id', 'lesson_id', 'resource_path', 'title', 'sort_order'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id', 'lesson_id');
    }
}
