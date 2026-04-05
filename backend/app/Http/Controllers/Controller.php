<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class Controller
{
    /**
     * Return a success JSON response.
     */
    protected function success(mixed $data, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Return a created (201) JSON response.
     */
    protected function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 201);
    }

    /**
     * Return an error JSON response.
     */
    protected function error(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $statusCode);
    }

    /**
     * Return an empty success (no payload) JSON response.
     * Use for delete operations or actions that return no data.
     */
    protected function emptySuccess(string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => null,
        ]);
    }

    /**
     * Return a paginated JSON response.
     * Flattens Laravel paginator metadata into the approved contract:
     * { success, message, data, hasNextPage, hasPreviousPage, totalPage, totalItem }
     */
    protected function paginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'hasNextPage' => $paginator->hasMorePages(),
            'hasPreviousPage' => $paginator->currentPage() > 1,
            'totalPage' => $paginator->lastPage(),
            'totalItem' => $paginator->total(),
        ]);
    }
}
