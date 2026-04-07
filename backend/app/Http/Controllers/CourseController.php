<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\CourseLesson;
use App\Models\CourseVideo;
use App\Models\CourseResource;
use App\Models\CourseImage;
use App\Models\CourseObjective;
use App\Models\CourseRequirement;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Display a listing of courses with optional filtering
     */
    public function index(Request $request)
    {
        $query = Course::with(['instructor.user', 'categories', 'images']);

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by language
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 12);
        $courses = $query->paginate($perPage);

        return $this->paginated($courses, 'Courses retrieved successfully');
    }

    /**
     * Display the specified course with full details
     */
    public function show(Request $request, $courseId)
    {
        $query = Course::with([
            'instructor.user',
            'categories',
            'images',
            'objectives',
            'requirements',
            'chapters.lessons.videos',
            'chapters.lessons.resources',
            'reviews.user'
        ]);

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        $course = $query->findOrFail($courseId);

        // Calculate average rating
        $averageRating = $course->reviews->avg('rating') ?? 0;
        $totalReviews = $course->reviews->count();

        return response()->json([
            'success' => true,
            'message' => 'Course retrieved successfully',
            'data' => [
                'course' => $course,
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $totalReviews,
            ],
        ]);
    }

    /**
     * Store a newly created course
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'difficulty' => 'nullable|string|in:Beginner,Intermediate,Expert,All Level',
            'language' => 'nullable|string|max:40',
        ]);

        $course = Course::create([
            'course_id' => 'course_' . Str::uuid(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'difficulty' => $request->difficulty,
            'language' => $request->language,
            'created_by' => $request->user()->instructor->instructor_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully',
            'data' => $course,
        ], 201);
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'difficulty' => 'nullable|string|in:Beginner,Intermediate,Expert,All Level',
            'language' => 'nullable|string|max:40',
        ]);

        $course->update($request->only(['title', 'description', 'price', 'difficulty', 'language', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully',
            'data' => $course,
        ]);
    }

    /**
     * Remove the specified course
     */
    public function destroy($courseId)
    {
        $course = Course::findOrFail($courseId);
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully',
            'data' => null,
        ]);
    }
}
