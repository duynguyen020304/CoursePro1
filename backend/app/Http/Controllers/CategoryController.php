<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        // Get nested categories if requested
        if ($request->boolean('nested', false)) {
            $query = Category::whereNull('parent_id')
                ->with(['children' => function ($query) {
                    $query->orderBy('sort_order');
                }])
                ->orderBy('sort_order');

            // Include soft-deleted records
            if ($request->boolean('include_deleted', false)) {
                $query->withTrashed();
            }

            // Filter by is_active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $categories = $query->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        }

        // Get all categories with parent info
        $query = Category::with('parent')
            ->orderBy('sort_order');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $categories = $query->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Display the specified category with its courses
     */
    public function show(Request $request, $slug)
    {
        $query = Category::with([
            'courses' => function ($query) {
                $query->with(['instructor.user', 'images'])
                    ->orderBy('created_at', 'desc');
            },
            'children'
        ])->where('slug', $slug);

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        $category = $query->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'sometimes|integer',
        ]);

        $category->update($request->only(['name', 'parent_id', 'sort_order', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
