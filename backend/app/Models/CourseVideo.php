<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use App\Services\VideoUploadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseVideo extends Model
{
    use HasAuditColumns;

    protected $primaryKey = 'video_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'video_id',
        'lesson_id',
        'url',
        'title',
        'duration',
        'sort_order',
        'is_active',
        'storage_disk',
        'storage_bucket',
        'storage_key',
        'mime_type',
        'file_size_bytes',
        'upload_status',
        'upload_id',
        'original_filename',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'sort_order' => 'integer',
            'file_size_bytes' => 'integer',
        ];
    }

    public function getRouteKeyName()
    {
        return 'video_id';
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id', 'lesson_id');
    }

    public function getUrlAttribute($value): ?string
    {
        if (! empty($this->attributes['storage_key'])) {
            if (($this->attributes['upload_status'] ?? 'ready') !== 'ready') {
                return null;
            }

            try {
                return app(VideoUploadService::class)->temporaryReadUrl($this);
            } catch (\Throwable) {
                return null;
            }
        }

        return $value;
    }
}
