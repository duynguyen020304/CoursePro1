<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Http\Request;

class CourseInstructorController extends Controller
{
    /**
     * Get instructors for a course
     */
    public function index(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $query = $course->instructors()->with('user');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $instructors = $query->get();

        return $this->success($instructors, 'Instructors retrieved successfully');
    }

    /**
     * Assign an instructor to a course
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|string|exists:courses,course_id',
            'instructor_id' => 'required|string|exists:instructors,instructor_id',
        ]);

        $course = Course::findOrFail($request->course_id);
        $instructor = Instructor::findOrFail($request->instructor_id);

        // Check if already assigned
        $exists = $course->instructors()->where('instructor_id', $request->instructor_id)->exists();

        if ($exists) {
            return $this->error('Instructor already assigned to this course', 400);
        }

        $course->instructors()->attach($request->instructor_id);

        return $this->success($course->instructors()->with('user')->get(), 'Instructor assigned successfully');
    }

    /**
     * Remove an instructor from a course
     */
    public function destroy($courseId, $instructorId)
    {
        $course = Course::findOrFail($courseId);
        $instructor = Instructor::findOrFail($instructorId);

        $course->instructors()->detach($instructorId);

        return $this->emptySuccess('Instructor removed from course successfully');
    }
}
