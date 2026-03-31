<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstructorController extends Controller
{
    /**
     * Display a listing of all instructors
     */
    public function index(Request $request)
    {
        $query = Instructor::with(['user' => function ($q) {
            $q->select('user_id', 'first_name', 'last_name', 'email', 'profile_image');
        }]);

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%');
            });
        }

        $instructors = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $instructors,
        ]);
    }

    /**
     * Get the authenticated instructor's profile and courses
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        $instructor = Instructor::where('user_id', $user->user_id)
            ->with(['courses' => function ($q) {
                $q->withCount(['students', 'reviews'])
                    ->orderBy('created_at', 'desc');
            }])
            ->first();

        if (!$instructor) {
            return response()->json([
                'success' => false,
                'message' => 'Instructor profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $instructor->load('user.role'),
        ]);
    }

    /**
     * Get instructor by ID with their courses
     */
    public function show($id)
    {
        $instructor = Instructor::with(['user', 'courses' => function ($q) {
            $q->with(['images', 'categories'])
                ->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $instructor,
        ]);
    }

    /**
     * Create instructor profile
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'biography' => 'required|string|max:2000',
        ]);

        $existingInstructor = Instructor::where('user_id', $user->user_id)->first();

        if ($existingInstructor) {
            return response()->json([
                'success' => false,
                'message' => 'User is already an instructor',
            ], 400);
        }

        $instructor = Instructor::create([
            'instructor_id' => 'instructor_' . Str::uuid(),
            'user_id' => $user->user_id,
            'biography' => $request->biography,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Instructor profile created successfully',
            'data' => $instructor,
        ], 201);
    }

    /**
     * Update instructor biography
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $instructor = Instructor::where('user_id', $user->user_id)->first();

        if (!$instructor) {
            return response()->json([
                'success' => false,
                'message' => 'Instructor profile not found',
            ], 404);
        }

        $request->validate([
            'biography' => 'sometimes|string|max:2000',
        ]);

        $instructor->update($request->only(['biography']));

        return response()->json([
            'success' => true,
            'message' => 'Instructor profile updated successfully',
            'data' => $instructor->fresh('user'),
        ]);
    }
}
