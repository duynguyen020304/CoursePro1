<?php

namespace App\Http\Controllers;

use App\Models\CourseRequirement;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseRequirementController extends Controller
{
    /**
     * Get requirements for a course
     */
    public function index($courseId)
    {
        $course = Course::findOrFail($courseId);

        $requirements = CourseRequirement::where('course_id', $courseId)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requirements,
        ]);
    }

    /**
     * Store a new course requirement
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|string|exists:courses,course_id',
            'requirement' => 'required|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        $requirement = CourseRequirement::create([
            'requirement_id' => 'requirement_' . Str::uuid(),
            'course_id' => $request->course_id,
            'requirement' => $request->requirement,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Course requirement created successfully',
            'data' => $requirement,
        ], 201);
    }

    /**
     * Update a course requirement
     */
    public function update(Request $request, $requirementId)
    {
        $requirement = CourseRequirement::findOrFail($requirementId);

        $request->validate([
            'requirement' => 'sometimes|string|max:500',
            'sort_order' => 'sometimes|integer',
        ]);

        $requirement->update($request->only(['requirement', 'sort_order']));

        return response()->json([
            'success' => true,
            'message' => 'Course requirement updated successfully',
            'data' => $requirement,
        ]);
    }

    /**
     * Delete a course requirement
     */
    public function destroy($requirementId)
    {
        $requirement = CourseRequirement::findOrFail($requirementId);
        $requirement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course requirement deleted successfully',
        ]);
    }
}
