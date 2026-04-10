<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\CourseVideo;
use App\Http\Controllers\Traits\EnsuresCourseOwnership;
use App\Services\VideoUploadService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    use EnsuresCourseOwnership;

    /**
     * Get videos for a lesson
     */
    public function index(Request $request, $lessonId)
    {
        $this->findLesson($lessonId);

        $query = CourseVideo::where('lesson_id', $lessonId)
            ->orderBy('sort_order');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $this->applyReadyVideoFilter($query);

        $videos = $query->get();

        return $this->success($videos, 'Videos retrieved successfully');
    }

    /**
     * Store a new video
     */
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|string|exists:course_lessons,lesson_id',
            'url' => 'required|string|max:2048',
            'title' => 'required|string|max:255',
            'duration' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);

        // Ownership check: lesson → course → owner
        $lesson = CourseLesson::where('lesson_id', $request->lesson_id)->first();
        if (!$lesson) {
            return $this->error('Lesson not found', 404);
        }

        $error = $this->authorizeLessonOwner($lesson);
        if ($error) {
            return $error;
        }

        $video = CourseVideo::create([
            'video_id' => Str::uuid(),
            'lesson_id' => $request->lesson_id,
            'url' => $request->url,
            'title' => $request->title,
            'duration' => $request->duration ?? 0,
            'sort_order' => $request->sort_order ?? 0,
            'upload_status' => 'ready',
        ]);

        return $this->created($video, 'Video created successfully');
    }

    /**
     * Initiate a direct-to-S3 upload.
     */
    public function initiateUpload(Request $request, string $lessonId, VideoUploadService $uploadService)
    {
        $lesson = $this->findLesson($lessonId);

        $error = $this->authorizeLessonOwner($lesson);
        if ($error) {
            return $error;
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'filename' => 'required|string|max:255',
            'mime_type' => 'required|string|in:video/mp4,video/webm,video/ogg,video/quicktime',
            'file_size_bytes' => 'required|integer|min:1|max:' . $uploadService->maxFileSizeBytes(),
            'duration' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $storageKey = $uploadService->generateStorageKey($lesson, $validated['filename']);
        $uploadMode = $uploadService->determineUploadMode((int) $validated['file_size_bytes']);

        $videoAttributes = [
            'video_id' => (string) Str::uuid(),
            'lesson_id' => $lesson->lesson_id,
            'title' => $validated['title'],
            'duration' => $this->normalizeDuration($validated['duration'] ?? null),
            'sort_order' => $validated['sort_order'] ?? 0,
            'storage_disk' => $uploadService->disk(),
            'storage_bucket' => $uploadService->bucket(),
            'storage_key' => $storageKey,
            'mime_type' => $validated['mime_type'],
            'file_size_bytes' => (int) $validated['file_size_bytes'],
            'upload_status' => 'uploading',
            'original_filename' => $validated['filename'],
            'url' => null,
        ];

        if ($uploadMode === 'single') {
            $singleUpload = $uploadService->createSingleUpload($storageKey, $validated['mime_type']);
            $video = CourseVideo::create($videoAttributes);

            return $this->created([
                'video_id' => $video->video_id,
                'upload_mode' => 'single',
                'storage_key' => $storageKey,
                'upload_id' => null,
                'part_size_bytes' => null,
                'single_upload' => $singleUpload,
                'multipart_parts' => null,
            ], 'Video upload initiated successfully');
        }

        $multipartUpload = $uploadService->createMultipartUpload(
            $storageKey,
            $validated['mime_type'],
            (int) $validated['file_size_bytes']
        );

        $video = CourseVideo::create([
            ...$videoAttributes,
            'upload_id' => $multipartUpload['upload_id'],
        ]);

        return $this->created([
            'video_id' => $video->video_id,
            'upload_mode' => 'multipart',
            'storage_key' => $storageKey,
            'upload_id' => $multipartUpload['upload_id'],
            'part_size_bytes' => $multipartUpload['part_size_bytes'],
            'single_upload' => null,
            'multipart_parts' => $multipartUpload['multipart_parts'],
        ], 'Video upload initiated successfully');
    }

    /**
     * Complete a direct-to-S3 upload.
     */
    public function completeUpload(Request $request, string $lessonId, string $videoId, VideoUploadService $uploadService)
    {
        $lesson = $this->findLesson($lessonId);
        $video = $this->findVideoForLesson($videoId, $lesson->lesson_id);

        $error = $this->authorizeLessonOwner($lesson);
        if ($error) {
            return $error;
        }

        if ($video->upload_status !== 'uploading') {
            return $this->error('This upload is no longer pending', 409);
        }

        if ($video->upload_id) {
            $validated = $request->validate([
                'upload_id' => 'required|string',
                'parts' => 'required|array|min:1',
                'parts.*.part_number' => 'required|integer|min:1',
                'parts.*.etag' => 'required|string',
            ]);

            if ($validated['upload_id'] !== $video->upload_id) {
                return $this->error('Upload ID mismatch', 422);
            }

            $uploadService->completeMultipartUpload(
                (string) $video->storage_key,
                (string) $video->upload_id,
                $validated['parts']
            );
        } else {
            $request->validate([
                'etag' => 'required|string',
            ]);
        }

        $video->forceFill([
            'upload_status' => 'ready',
            'upload_id' => null,
            'url' => $uploadService->stableObjectUrl((string) $video->storage_key),
        ])->save();

        return $this->success($video->fresh(), 'Video upload completed successfully');
    }

    /**
     * Abort a direct-to-S3 upload and remove the pending row.
     */
    public function abortUpload(Request $request, string $lessonId, string $videoId, VideoUploadService $uploadService)
    {
        $lesson = $this->findLesson($lessonId);
        $video = $this->findVideoForLesson($videoId, $lesson->lesson_id);

        $error = $this->authorizeLessonOwner($lesson);
        if ($error) {
            return $error;
        }

        if ($video->upload_status !== 'uploading') {
            return $this->error('Only pending uploads can be aborted', 409);
        }

        $validated = $request->validate([
            'upload_id' => 'nullable|string',
        ]);

        if ($video->upload_id && isset($validated['upload_id']) && $validated['upload_id'] !== $video->upload_id) {
            return $this->error('Upload ID mismatch', 422);
        }

        if ($video->upload_id) {
            $uploadService->abortMultipartUpload((string) $video->storage_key, (string) $video->upload_id);
        } elseif ($video->storage_key) {
            $uploadService->deleteObject((string) $video->storage_key);
        }

        $video->forceDelete();

        return $this->emptySuccess('Video upload aborted successfully');
    }

    /**
     * Update a video
     */
    public function update(Request $request, string $lessonOrVideoId, ?string $videoId = null)
    {
        $lessonId = $videoId !== null ? $lessonOrVideoId : request()->route('lesson');
        $resolvedVideoId = $videoId ?? $lessonOrVideoId;
        $video = $this->findVideoForLesson($resolvedVideoId, $lessonId);

        $error = $this->authorizeVideoOwner($video);
        if ($error) {
            return $error;
        }

        $request->validate([
            'url' => 'sometimes|string|max:2048',
            'title' => 'sometimes|string|max:255',
            'duration' => 'nullable|integer',
            'sort_order' => 'sometimes|integer',
        ]);

        $attributes = $request->only(['title', 'duration', 'sort_order', 'is_active']);
        if (! $video->storage_key && $request->has('url')) {
            $attributes['url'] = $request->string('url')->toString();
        }

        $video->update($attributes);

        return $this->success($video->fresh(), 'Video updated successfully');
    }

    /**
     * Delete a video
     */
    public function destroy(Request $request, string $lessonOrVideoId, ?string $videoId = null, ?VideoUploadService $uploadService = null)
    {
        $lessonId = $videoId !== null ? $lessonOrVideoId : $request->route('lesson');
        $resolvedVideoId = $videoId ?? $lessonOrVideoId;
        $video = $this->findVideoForLesson($resolvedVideoId, $lessonId);
        $uploadService ??= app(VideoUploadService::class);

        $error = $this->authorizeVideoOwner($video);
        if ($error) {
            return $error;
        }

        if ($video->upload_status === 'uploading' && $video->upload_id) {
            $uploadService->abortMultipartUpload((string) $video->storage_key, (string) $video->upload_id);
        }

        if ($video->storage_key) {
            $uploadService->deleteObject((string) $video->storage_key);
        }

        $video->delete();

        return $this->emptySuccess('Video deleted successfully');
    }

    private function findLesson(string $lessonId): CourseLesson
    {
        return CourseLesson::where('lesson_id', $lessonId)->firstOrFail();
    }

    private function findVideoForLesson(string $videoId, ?string $lessonId = null): CourseVideo
    {
        $query = CourseVideo::withTrashed()->where('video_id', $videoId);

        if ($lessonId !== null) {
            $query->where('lesson_id', $lessonId);
        }

        return $query->firstOrFail();
    }

    private function applyReadyVideoFilter(Builder $query): void
    {
        $query->where(function (Builder $builder) {
            $builder->whereNull('upload_status')
                ->orWhere('upload_status', 'ready');
        });
    }

    private function normalizeDuration(mixed $duration): int
    {
        return $duration === null ? 0 : (int) round((float) $duration);
    }
}
