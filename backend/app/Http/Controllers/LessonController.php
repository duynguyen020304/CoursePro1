<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\CourseChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    /**
     * Get lessons for a chapter
     */
    public function index($chapterId)
    {
        $chapter = CourseChapter::findOrFail($chapterId);

        $lessons = CourseLesson::where('chapter_id', $chapterId)
            ->with(['videos', 'resources'])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lessons,
        ]);
    }

    /**
     * Store a new lesson
     */
    public function store(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|string|exists:course_chapters,chapter_id',
            'course_id' => 'required|string|exists:courses,course_id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $lesson = CourseLesson::create([
            'lesson_id' => 'lesson_' . Str::uuid(),
            'chapter_id' => $request->chapter_id,
            'course_id' => $request->course_id,
            'title' => $request->title,
            'content' => $request->content,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lesson created successfully',
            'data' => $lesson,
        ], 201);
    }

    /**
     * Display the specified lesson
     */
    public function show($lessonId)
    {
        $lesson = CourseLesson::with(['videos', 'resources', 'chapter'])->findOrFail($lessonId);

        return response()->json([
            'success' => true,
            'data' => $lesson,
        ]);
    }

    /**
     * Update a lesson
     */
    public function update(Request $request, $lessonId)
    {
        $lesson = CourseLesson::findOrFail($lessonId);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'sort_order' => 'sometimes|integer',
        ]);

        $lesson->update($request->only(['title', 'content', 'sort_order']));

        return response()->json([
            'success' => true,
            'message' => 'Lesson updated successfully',
            'data' => $lesson->fresh(['videos', 'resources']),
        ]);
    }

    /**
     * Delete a lesson
     */
    public function destroy($lessonId)
    {
        $lesson = CourseLesson::findOrFail($lessonId);
        $lesson->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully',
        ]);
    }
}
