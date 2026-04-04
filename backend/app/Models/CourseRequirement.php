<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseRequirement extends Model
{
    use HasAuditColumns;

    public $incrementing = false;
    protected $primaryKey = ['course_id', 'requirement_id'];
    public $keyType = 'string';

    protected $fillable = ['requirement_id', 'course_id', 'requirement', 'is_active'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
