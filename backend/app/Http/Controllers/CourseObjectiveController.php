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
    public function index(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $query = CourseObjective::where('course_id', $courseId)
            ->orderBy('sort_order');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $objectives = $query->get();

        return $this->success($objectives, 'Objectives retrieved successfully');
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

        return $this->created($objective, 'Course objective created successfully');
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

        $objective->update($request->only(['objective', 'sort_order', 'is_active']));

        return $this->success($objective, 'Course objective updated successfully');
    }

    /**
     * Delete a course objective
     */
    public function destroy($objectiveId)
    {
        $objective = CourseObjective::findOrFail($objectiveId);
        $objective->delete();

        return $this->emptySuccess('Course objective deleted successfully');
    }
}
