<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BaseController extends Controller
{
    /**
     * Standard success response.
     */
    protected function success($data = [], $message = null, $meta = null, $status = 200): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $data,
            'meta' => $meta,
            'error' => null
        ], $status);
    }

    /**
     * Standard error response.
     */
    protected function error($code, $message, $fields = null, $status = 400): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'data' => null,
            'meta' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'fields' => $fields
            ]
        ], $status);
    }

    /**
     * Response with pagination metadata.
     */
    protected function paginate(LengthAwarePaginator $paginator, $data = null): JsonResponse
    {
        $items = is_null($data) ? $paginator->items() : $data;

        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->values()->all();
        } elseif (is_array($items)) {
            $items = array_values($items);
        }

        return $this->success(
            $items,
            null,
            [
                'page' => (int)$paginator->currentPage(),
                'perPage' => (int)$paginator->perPage(),
                'total' => (int)$paginator->total(),
                'totalPages' => (int)$paginator->lastPage(),
            ]
        );
    }

    /**
     * Apply location filter to a query based on user permissions.
     */
    protected function applyLocationFilter($query, $column = 'location_id')
    {
        $permitted_locations = $this->getPermittedLocations();

        if ($permitted_locations !== 'all') {
            $query->whereIn($column, $permitted_locations);
        }

        return $query;
    }

    /**
     * Get permitted locations for the authenticated user.
     */
    protected function getPermittedLocations()
    {
        $user = auth()->user();
        return $user->permitted_locations();
    }
}
