<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\CourseChapter;
use App\Models\Course;
use App\Http\Controllers\Traits\EnsuresCourseOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    use EnsuresCourseOwnership;

    /**
     * Get lessons for a chapter
     */
    public function index(Request $request, Course $course, CourseChapter $chapter)
    {
        $query = CourseLesson::where('chapter_id', $chapter->chapter_id)
            ->with(['videos', 'resources'])
            ->orderBy('sort_order');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $lessons = $query->get();

        return $this->success($lessons, 'Lessons retrieved successfully');
    }

    /**
     * Store a new lesson
     */
    public function store(Request $request, Course $course, CourseChapter $chapter)
    {
        $error = $this->authorizeCourseOwner($course);
        if ($error) {
            return $error;
        }

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

        return $this->created($lesson, 'Lesson created successfully');
    }

    /**
     * Display the specified lesson
     */
    public function show(CourseLesson $lesson)
    {
        $lesson->load(['videos', 'resources', 'chapter']);

        return $this->success($lesson, 'Lesson retrieved successfully');
    }

    /**
     * Update a lesson
     */
    public function update(Request $request, CourseLesson $lesson)
    {
        $error = $this->authorizeLessonOwner($lesson);
        if ($error) {
            return $error;
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'sort_order' => 'sometimes|integer',
        ]);

        $lesson->update($request->only(['title', 'content', 'sort_order', 'is_active']));

        return $this->success($lesson->fresh(['videos', 'resources']), 'Lesson updated successfully');
    }

    /**
     * Delete a lesson
     */
    public function destroy(CourseLesson $lesson)
    {
        $error = $this->authorizeLessonOwner($lesson);
        if ($error) {
            return $error;
        }

        $lesson->delete();

        return $this->emptySuccess('Lesson deleted successfully');
    }
}
