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
    public function index(Request $request, Course $course)
    {
        $query = CourseChapter::where('course_id', $course->course_id)
            ->with(['lessons.videos', 'lessons.resources'])
            ->orderBy('sort_order');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $chapters = $query->get();

        return $this->success($chapters, 'Chapters retrieved successfully');
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

        return $this->created($chapter, 'Chapter created successfully');
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

        $chapter->update($request->only(['title', 'description', 'sort_order', 'is_active']));

        return $this->success($chapter->fresh(['lessons']), 'Chapter updated successfully');
    }

    /**
     * Delete a chapter
     */
    public function destroy(Course $course, CourseChapter $chapter)
    {
        $chapter->delete();

        return $this->emptySuccess('Chapter deleted successfully');
    }
}
