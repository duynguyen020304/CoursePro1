<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\CourseChapter;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    /**
     * Get lessons for a chapter
     */
    public function index(Course $course, CourseChapter $chapter)
    {
        $lessons = CourseLesson::where('chapter_id', $chapter->chapter_id)
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
    public function store(Request $request, Course $course, CourseChapter $chapter)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $lesson = CourseLesson::create([
            'lesson_id' => Str::uuid(),
            'chapter_id' => $chapter->chapter_id,
            'course_id' => $course->course_id,
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
    public function show(CourseLesson $lesson)
    {
        $lesson->load(['videos', 'resources', 'chapter']);

        return response()->json([
            'success' => true,
            'data' => $lesson,
        ]);
    }

    /**
     * Update a lesson
     */
    public function update(Request $request, CourseLesson $lesson)
    {
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
    public function destroy(CourseLesson $lesson)
    {
        $lesson->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully',
        ]);
    }
}
