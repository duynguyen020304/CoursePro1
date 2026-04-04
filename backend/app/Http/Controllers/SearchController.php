<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search courses by title
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->input('q');

        $courseQuery = Course::with(['instructor.user', 'images', 'categories'])
            ->where('title', 'like', '%' . $query . '%')
            ->orWhereHas('instructor', function ($q) use ($query) {
                $q->whereHas('user', function ($qu) use ($query) {
                    $qu->where('first_name', 'like', '%' . $query . '%')
                       ->orWhere('last_name', 'like', '%' . $query . '%');
                });
            });

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $courseQuery->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $courseQuery->where('is_active', $request->boolean('is_active'));
        }

        $courses = $courseQuery->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }
}
