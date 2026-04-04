<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseVideo extends Model
{
    use HasAuditColumns;

    protected $primaryKey = 'video_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['video_id', 'lesson_id', 'url', 'title', 'duration', 'sort_order', 'is_active'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id', 'lesson_id');
    }
}
