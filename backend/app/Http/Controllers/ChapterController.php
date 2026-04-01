<?php

namespace App\Http\Controllers;

use App\Models\CourseChapter;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChapterController extends Controller
{
    /**
     * Get chapters for a course
     */
    public function index(Course $course)
    {
        $chapters = CourseChapter::where('course_id', $course->course_id)
            ->with(['lessons.videos', 'lessons.resources'])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $chapters,
        ]);
    }

    /**
     * Store a new chapter
     */
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $chapter = CourseChapter::create([
            'chapter_id' => Str::uuid(),
            'course_id' => $course->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chapter created successfully',
            'data' => $chapter,
        ], 201);
    }

    /**
     * Update a chapter
     */
    public function update(Request $request, Course $course, CourseChapter $chapter)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'sometimes|integer',
        ]);

        $chapter->update($request->only(['title', 'description', 'sort_order']));

        return response()->json([
            'success' => true,
            'message' => 'Chapter updated successfully',
            'data' => $chapter->fresh(['lessons']),
        ]);
    }

    /**
     * Delete a chapter
     */
    public function destroy(Course $course, CourseChapter $chapter)
    {
        $chapter->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chapter deleted successfully',
        ]);
    }
}
