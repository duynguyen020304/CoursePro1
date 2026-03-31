<?php

namespace App\Http\Controllers;

use App\Models\CourseObjective;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseObjectiveController extends Controller
{
    /**
     * Get objectives for a course
     */
    public function index($courseId)
    {
        $course = Course::findOrFail($courseId);

        $objectives = CourseObjective::where('course_id', $courseId)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $objectives,
        ]);
    }

    /**
     * Store a new course objective
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|string|exists:courses,course_id',
            'objective' => 'required|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        $objective = CourseObjective::create([
            'objective_id' => 'objective_' . Str::uuid(),
            'course_id' => $request->course_id,
            'objective' => $request->objective,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Course objective created successfully',
            'data' => $objective,
        ], 201);
    }

    /**
     * Update a course objective
     */
    public function update(Request $request, $objectiveId)
    {
        $objective = CourseObjective::findOrFail($objectiveId);

        $request->validate([
            'objective' => 'sometimes|string|max:500',
            'sort_order' => 'sometimes|integer',
        ]);

        $objective->update($request->only(['objective', 'sort_order']));

        return response()->json([
            'success' => true,
            'message' => 'Course objective updated successfully',
            'data' => $objective,
        ]);
    }

    /**
     * Delete a course objective
     */
    public function destroy($objectiveId)
    {
        $objective = CourseObjective::findOrFail($objectiveId);
        $objective->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course objective deleted successfully',
        ]);
    }
}
