<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\CourseLesson;
use App\Models\CourseImage;
use App\Models\CourseObjective;
use App\Models\CourseRequirement;
use App\Models\Category;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstructorCourseController extends Controller
{
    /**
     * Get all courses created by the authenticated instructor
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->instructor) {
            return response()->json([
                'success' => false,
                'message' => 'User is not an instructor',
            ], 403);
        }

        $query = Course::where('created_by', $user->instructor->instructor_id)
            ->with(['categories', 'images', 'chapters.lessons'])
            ->orderBy('created_at', 'desc');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $courses = $query->get();

        // Add stats for each course
        $coursesWithStats = $courses->map(function ($course) {
            $totalStudents = $course->orderDetails()->count();
            $totalRevenue = $course->orderDetails()->sum('price');
            $totalReviews = $course->reviews()->count();
            $averageRating = $course->reviews()->avg('rating') ?? 0;
            $totalLessons = $course->chapters->flatMap->lessons->count();

            return [
                'course' => $course,
                'stats' => [
                    'total_students' => $totalStudents,
                    'total_revenue' => $totalRevenue,
                    'total_reviews' => $totalReviews,
                    'average_rating' => round($averageRating, 1),
                    'total_lessons' => $totalLessons,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $coursesWithStats,
        ]);
    }

    /**
     * Create a new course for the authenticated instructor
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->instructor) {
            return response()->json([
                'success' => false,
                'message' => 'User is not an instructor',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'difficulty' => 'nullable|string|in:Beginner,Intermediate,Expert,All Level',
            'language' => 'nullable|string|max:40',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'objectives' => 'nullable|array',
            'objectives.*' => 'string|max:500',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:500',
        ]);

        $course = Course::create([
            'course_id' => Str::uuid(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'difficulty' => $request->difficulty ?? 'All Level',
            'language' => $request->language ?? 'Vietnamese',
            'created_by' => $user->instructor->instructor_id,
        ]);

        // Attach categories
        if ($request->filled('category_ids')) {
            $course->categories()->attach($request->category_ids);
        }

        // Create objectives
        if ($request->filled('objectives')) {
            foreach ($request->objectives as $index => $objective) {
                CourseObjective::create([
                    'objective_id' => Str::uuid(),
                    'course_id' => $course->course_id,
                    'objective' => $objective,
                    'sort_order' => $index,
                ]);
            }
        }

        // Create requirements
        if ($request->filled('requirements')) {
            foreach ($request->requirements as $index => $requirement) {
                CourseRequirement::create([
                    'requirement_id' => Str::uuid(),
                    'course_id' => $course->course_id,
                    'requirement' => $requirement,
                    'sort_order' => $index,
                ]);
            }
        }

        $course->load(['categories', 'objectives', 'requirements']);

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully',
            'data' => $course,
        ], 201);
    }

    /**
     * Get a specific course owned by the instructor
     */
    public function show(Request $request, $courseId)
    {
        $course = $this->getInstructorCourse($request, $courseId, $request->boolean('include_deleted', false));

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or you do not have permission to access it',
            ], 404);
        }

        $course->load([
            'categories',
            'images',
            'objectives',
            'requirements',
            'chapters.lessons.videos',
            'chapters.lessons.resources',
        ]);

        return response()->json([
            'success' => true,
            'data' => $course,
        ]);
    }

    /**
     * Update a course owned by the instructor
     */
    public function update(Request $request, $courseId)
    {
        $course = $this->getInstructorCourse($request, $courseId);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or you do not have permission to update it',
            ], 404);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'difficulty' => 'nullable|string|in:Beginner,Intermediate,Expert,All Level',
            'language' => 'nullable|string|max:40',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'objectives' => 'nullable|array',
            'objectives.*' => 'string|max:500',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:500',
        ]);

        $course->update($request->only(['title', 'description', 'price', 'difficulty', 'language', 'is_active']));

        // Sync categories
        if ($request->has('category_ids')) {
            $course->categories()->sync($request->category_ids);
        }

        // Update objectives
        if ($request->has('objectives')) {
            $course->objectives()->delete();
            foreach ($request->objectives as $index => $objective) {
                CourseObjective::create([
                    'objective_id' => Str::uuid(),
                    'course_id' => $course->course_id,
                    'objective' => $objective,
                    'sort_order' => $index,
                ]);
            }
        }

        // Update requirements
        if ($request->has('requirements')) {
            $course->requirements()->delete();
            foreach ($request->requirements as $index => $requirement) {
                CourseRequirement::create([
                    'requirement_id' => Str::uuid(),
                    'course_id' => $course->course_id,
                    'requirement' => $requirement,
                    'sort_order' => $index,
                ]);
            }
        }

        $course->load(['categories', 'objectives', 'requirements', 'chapters.lessons']);

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully',
            'data' => $course,
        ]);
    }

    /**
     * Delete a course owned by the instructor
     */
    public function destroy(Request $request, $courseId)
    {
        $course = $this->getInstructorCourse($request, $courseId);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or you do not have permission to delete it',
            ], 404);
        }

        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully',
        ]);
    }

    /**
     * Add an image to a course
     */
    public function addImage(Request $request, $courseId)
    {
        $course = $this->getInstructorCourse($request, $courseId);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or you do not have permission',
            ], 404);
        }

        $request->validate([
            'image_url' => 'required|string|max:500',
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // If setting as primary, unset other primary images
        if ($request->is_primary) {
            CourseImage::where('course_id', $courseId)->update(['is_primary' => false]);
        }

        $image = CourseImage::create([
            'image_id' => Str::uuid(),
            'course_id' => $courseId,
            'image_path' => $request->image_url,
            'is_primary' => $request->is_primary ?? false,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image added successfully',
            'data' => $image,
        ], 201);
    }

    /**
     * Delete an image from a course
     */
    public function deleteImage(Request $request, $courseId, $imageId)
    {
        $course = $this->getInstructorCourse($request, $courseId);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or you do not have permission',
            ], 404);
        }

        $image = CourseImage::where('image_id', $imageId)
            ->where('course_id', $courseId)
            ->first();

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found',
            ], 404);
        }

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    /**
     * Get instructor dashboard statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        if (!$user->instructor) {
            return response()->json([
                'success' => false,
                'message' => 'User is not an instructor',
            ], 403);
        }

        $courses = Course::where('created_by', $user->instructor->instructor_id)
            ->with(['orderDetails', 'reviews', 'chapters.lessons'])
            ->get();

        $totalCourses = $courses->count();
        $totalStudents = $courses->flatMap->orderDetails->unique('user_id')->count();
        $totalRevenue = $courses->flatMap->orderDetails->sum('price');
        $totalReviews = $courses->flatMap->reviews->count();
        $averageRating = $courses->flatMap->reviews->avg('rating') ?? 0;
        $totalLessons = $courses->flatMap->chapters->flatMap->lessons->count();

        // Recent courses
        $recentCourses = $courses->sortByDesc('created_at')->take(5)->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_courses' => $totalCourses,
                'total_students' => $totalStudents,
                'total_revenue' => $totalRevenue,
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating, 1),
                'total_lessons' => $totalLessons,
                'recent_courses' => $recentCourses,
            ],
        ]);
    }

    /**
     * Helper to get course owned by the authenticated instructor
     */
    private function getInstructorCourse(Request $request, $courseId, $withTrashed = false)
    {
        $user = $request->user();

        if (!$user->instructor) {
            return null;
        }

        $query = Course::where('course_id', $courseId)
            ->where('created_by', $user->instructor->instructor_id);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->first();
    }
}