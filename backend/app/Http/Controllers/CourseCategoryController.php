<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;

class CourseCategoryController extends Controller
{
    /**
     * Get categories for a course
     */
    public function index(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $query = $course->categories()->with('parent');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $categories = $query->get();

        return $this->success($categories, 'Categories retrieved successfully');
    }

    /**
     * Assign a category to a course
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|string|exists:courses,course_id',
            'category_id' => 'required|string|exists:categories,id',
        ]);

        $course = Course::findOrFail($request->course_id);
        $category = Category::findOrFail($request->category_id);

        // Check if already assigned
        $exists = $course->categories()->where('category_id', $request->category_id)->exists();

        if ($exists) {
            return $this->error('Category already assigned to this course', 400);
        }

        $course->categories()->attach($request->category_id);

        return $this->success($course->categories()->with('parent')->get(), 'Category assigned successfully');
    }

    /**
     * Remove a category from a course
     */
    public function destroy($courseId, $categoryId)
    {
        $course = Course::findOrFail($courseId);
        $category = Category::findOrFail($categoryId);

        $course->categories()->detach($categoryId);

        return $this->emptySuccess('Category removed from course successfully');
    }
}
