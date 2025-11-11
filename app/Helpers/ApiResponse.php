<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Log;

class ApiResponse
{

    protected static function getExecutionTime(): float
    {
        return round((microtime(true) - LARAVEL_START), 4); // seconds
    }

    public static function success($data = [], string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $data
        ];

        return response()->json($response, $code);
    }

    public static function error(string $message = 'Error', int $code = 500, $errors = []): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ];

        return response()->json($response, $code);
    }

    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }

    public static function validationError($validator): JsonResponse
    {
        return self::error('Validation Error', 422, $validator->errors());
    }

    public static function paginate(LengthAwarePaginator $paginator, $resourceCollection = null): JsonResponse
    {
        $response = [
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $resourceCollection ?: $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
            ]
        ];

        return response()->json($response, 200);
    }

    public static function paginateResponse($paginator, $resourceCollection = null): JsonResponse
    {
        $pagination = [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
        ];

        // Default for all
        $pagination['total'] = null;
        $pagination['last_page'] = null;

        // For normal paginate() (LengthAwarePaginator)
        if ($paginator instanceof LengthAwarePaginator) {
            $pagination['total']     = $paginator->total();
            $pagination['last_page'] = $paginator->lastPage();
        }

        // For cursorPaginate()
        if ($paginator instanceof CursorPaginator) {
            $pagination['next_cursor'] = $paginator->nextCursor()?->encode();
            $pagination['prev_cursor'] = $paginator->previousCursor()?->encode();
        }

        $response = [
            'status'     => true,
            'message'    => 'Fetched successfully',
            'data'       => $resourceCollection ?: $paginator->items(),
            'pagination' => $pagination
        ];

        if (config('app.env') === 'local') {
            $execution_time = self::getExecutionTime();
            Log::info('execution_time: '. $execution_time);
        }

        return response()->json($response, 200);
    }
}
