<?php

namespace App\Http\Controllers;

use App\Models\CourseResource;
use App\Models\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    /**
     * Get resources for a lesson
     */
    public function index(Request $request, $lessonId)
    {
        $lesson = CourseLesson::findOrFail($lessonId);

        $query = CourseResource::where('lesson_id', $lessonId)
            ->orderBy('sort_order');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $resources = $query->get();

        return $this->success($resources, 'Resources retrieved successfully');
    }

    /**
     * Store a new resource
     */
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|string|exists:course_lessons,lesson_id',
            'resource_path' => 'required|string|max:500',
            'title' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $resource = CourseResource::create([
            'resource_id' => 'resource_' . Str::uuid(),
            'lesson_id' => $request->lesson_id,
            'resource_path' => $request->resource_path,
            'title' => $request->title,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return $this->created($resource, 'Resource created successfully');
    }

    /**
     * Update a resource
     */
    public function update(Request $request, $resourceId)
    {
        $resource = CourseResource::findOrFail($resourceId);

        $request->validate([
            'resource_path' => 'sometimes|string|max:500',
            'title' => 'sometimes|string|max:255',
            'sort_order' => 'sometimes|integer',
        ]);

        $resource->update($request->only(['resource_path', 'title', 'sort_order', 'is_active']));

        return $this->success($resource, 'Resource updated successfully');
    }

    /**
     * Delete a resource
     */
    public function destroy($resourceId)
    {
        $resource = CourseResource::findOrFail($resourceId);
        $resource->delete();

        return $this->emptySuccess('Resource deleted successfully');
    }
}
