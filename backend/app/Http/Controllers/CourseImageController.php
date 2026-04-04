<?php

namespace App\Http\Controllers;

use App\Models\CourseImage;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseImageController extends Controller
{
    /**
     * Get images for a course
     */
    public function index(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $query = CourseImage::where('course_id', $courseId)
            ->orderBy('sort_order');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $images = $query->get();

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    /**
     * Store a new course image
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|string|exists:courses,course_id',
            'image_url' => 'required|string|max:500',
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // If is_primary, unset other primary images
        if ($request->boolean('is_primary')) {
            CourseImage::where('course_id', $request->course_id)
                ->update(['is_primary' => false]);
        }

        $image = CourseImage::create([
            'image_id' => 'image_' . Str::uuid(),
            'course_id' => $request->course_id,
            'image_url' => $request->image_url,
            'is_primary' => $request->boolean('is_primary', false),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Course image created successfully',
            'data' => $image,
        ], 201);
    }

    /**
     * Update a course image
     */
    public function update(Request $request, $imageId)
    {
        $image = CourseImage::findOrFail($imageId);

        $request->validate([
            'image_url' => 'sometimes|string|max:500',
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        // If setting as primary, unset others
        if ($request->boolean('is_primary')) {
            CourseImage::where('course_id', $image->course_id)
                ->where('image_id', '!=', $imageId)
                ->update(['is_primary' => false]);
        }

        $image->update($request->only(['image_url', 'is_primary', 'sort_order', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Course image updated successfully',
            'data' => $image,
        ]);
    }

    /**
     * Delete a course image
     */
    public function destroy($imageId)
    {
        $image = CourseImage::findOrFail($imageId);
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course image deleted successfully',
        ]);
    }
}
