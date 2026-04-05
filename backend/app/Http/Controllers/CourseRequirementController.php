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
    public function index(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $query = CourseRequirement::where('course_id', $courseId)
            ->orderBy('sort_order');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $requirements = $query->get();

        return $this->success($requirements, 'Requirements retrieved successfully');
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

        return $this->created($requirement, 'Course requirement created successfully');
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

        $requirement->update($request->only(['requirement', 'sort_order', 'is_active']));

        return $this->success($requirement, 'Course requirement updated successfully');
    }

    /**
     * Delete a course requirement
     */
    public function destroy($requirementId)
    {
        $requirement = CourseRequirement::findOrFail($requirementId);
        $requirement->delete();

        return $this->emptySuccess('Course requirement deleted successfully');
    }
}
