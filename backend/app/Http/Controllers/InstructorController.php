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
        $query = Instructor::with(['user.userAccount']);

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%');
            });
        }

        $instructors = $query->paginate($request->get('per_page', 15));

        return $this->paginated($instructors, 'Instructors retrieved successfully');
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
            return $this->error('Instructor profile not found', 404);
        }

        return $this->success(
            $instructor->load('user.role'),
            'Instructor profile retrieved successfully'
        );
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

        return $this->success($instructor, 'Instructor retrieved successfully');
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
            return $this->error('User is already an instructor', 400);
        }

        $instructor = Instructor::create([
            'instructor_id' => 'instructor_' . Str::uuid(),
            'user_id' => $user->user_id,
            'biography' => $request->biography,
        ]);

        return $this->created($instructor, 'Instructor profile created successfully');
    }

    /**
     * Update instructor biography
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $instructor = Instructor::where('user_id', $user->user_id)->first();

        if (!$instructor) {
            return $this->error('Instructor profile not found', 404);
        }

        $request->validate([
            'biography' => 'sometimes|string|max:2000',
        ]);

        $instructor->update($request->only(['biography', 'is_active']));

        return $this->success(
            $instructor->fresh('user'),
            'Instructor profile updated successfully'
        );
    }
}
