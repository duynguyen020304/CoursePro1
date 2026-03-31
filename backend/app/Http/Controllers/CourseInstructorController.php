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
    public function index($courseId)
    {
        $course = Course::findOrFail($courseId);

        $instructors = $course->instructors()->with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $instructors,
        ]);
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
            return response()->json([
                'success' => false,
                'message' => 'Instructor already assigned to this course',
            ], 400);
        }

        $course->instructors()->attach($request->instructor_id);

        return response()->json([
            'success' => true,
            'message' => 'Instructor assigned successfully',
            'data' => $course->instructors()->with('user')->get(),
        ]);
    }

    /**
     * Remove an instructor from a course
     */
    public function destroy($courseId, $instructorId)
    {
        $course = Course::findOrFail($courseId);
        $instructor = Instructor::findOrFail($instructorId);

        $course->instructors()->detach($instructorId);

        return response()->json([
            'success' => true,
            'message' => 'Instructor removed from course successfully',
        ]);
    }
}
