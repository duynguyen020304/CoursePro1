<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseObjective extends Model
{
    use HasAuditColumns;

    public $incrementing = false;
    protected $primaryKey = ['course_id', 'objective_id'];
    public $keyType = 'string';

    protected $fillable = ['objective_id', 'course_id', 'objective', 'is_active'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
