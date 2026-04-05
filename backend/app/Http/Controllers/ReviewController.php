<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for a course
     */
    public function index(Request $request)
    {
        $query = Review::with('user')->orderBy('created_at', 'desc');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reviews = $query->paginate($request->get('per_page', 10));

        return $this->paginated($reviews, 'Reviews retrieved successfully');
    }

    /**
     * Store a newly created review
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|string|exists:courses,course_id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // Check if user already reviewed this course
        $existingReview = Review::where('user_id', $user->user_id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this course',
                'data' => null,
            ], 400);
        }

        $review = Review::create([
            'review_id' => 'review_' . Str::uuid(),
            'user_id' => $user->user_id,
            'course_id' => $request->course_id,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review->load('user'),
        ], 201);
    }

    /**
     * Update the specified review
     */
    public function update(Request $request, $reviewId)
    {
        $review = Review::findOrFail($reviewId);

        // Only allow user to update their own review
        if ($review->user_id !== $request->user()->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => null,
            ], 403);
        }

        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        $review->update($request->only(['rating', 'review_text', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review->load('user'),
        ]);
    }

    /**
     * Remove the specified review
     */
    public function destroy($reviewId)
    {
        $review = Review::findOrFail($reviewId);

        // Only allow user to delete their own review
        if ($review->user_id !== request()->user()->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => null,
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
            'data' => null,
        ]);
    }
}
