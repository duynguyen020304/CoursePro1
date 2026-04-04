<?php

namespace App\Http\Controllers;

use App\Models\CourseVideo;
use App\Models\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    /**
     * Get videos for a lesson
     */
    public function index(Request $request, $lessonId)
    {
        $lesson = CourseLesson::findOrFail($lessonId);

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

        $videos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $videos,
        ]);
    }

    /**
     * Store a new video
     */
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|string|exists:course_lessons,lesson_id',
            'url' => 'required|string|max:500',
            'title' => 'required|string|max:255',
            'duration' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);

        $video = CourseVideo::create([
            'video_id' => 'video_' . Str::uuid(),
            'lesson_id' => $request->lesson_id,
            'url' => $request->url,
            'title' => $request->title,
            'duration' => $request->duration ?? 0,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Video created successfully',
            'data' => $video,
        ], 201);
    }

    /**
     * Update a video
     */
    public function update(Request $request, $videoId)
    {
        $video = CourseVideo::findOrFail($videoId);

        $request->validate([
            'url' => 'sometimes|string|max:500',
            'title' => 'sometimes|string|max:255',
            'duration' => 'nullable|integer',
            'sort_order' => 'sometimes|integer',
        ]);

        $video->update($request->only(['url', 'title', 'duration', 'sort_order', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Video updated successfully',
            'data' => $video,
        ]);
    }

    /**
     * Delete a video
     */
    public function destroy($videoId)
    {
        $video = CourseVideo::findOrFail($videoId);
        $video->delete();

        return response()->json([
            'success' => true,
            'message' => 'Video deleted successfully',
        ]);
    }
}
