<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Get the authenticated student's profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        $student = Student::where('user_id', $user->user_id)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        // Get purchased courses
        $purchasedCourses = Order::with(['details.course.instructor.user'])
            ->where('user_id', $user->user_id)
            ->get()
            ->flatMap(function ($order) {
                return $order->details->map(function ($detail) {
                    return $detail->course;
                });
            })
            ->unique('course_id');

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $student->load('user.role'),
                'purchased_courses' => $purchasedCourses,
            ],
        ]);
    }

    /**
     * Check if student has purchased a course
     */
    public function hasPurchasedCourse(Request $request)
    {
        $request->validate([
            'course_id' => 'required|string|exists:courses,course_id',
        ]);

        $user = $request->user();

        $hasPurchased = Order::where('user_id', $user->user_id)
            ->whereHas('details', function ($query) use ($request) {
                $query->where('course_id', $request->course_id);
            })
            ->exists();

        return response()->json([
            'success' => true,
            'data' => ['has_purchased' => $hasPurchased],
        ]);
    }
}
