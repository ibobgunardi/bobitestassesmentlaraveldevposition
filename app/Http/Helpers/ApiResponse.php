<?php

namespace App\Http\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param  array|string  $data
     * @param  string  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = [], string $message = 'Operation successful', int $code = 200, $additionalData = [])
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'additionalData' => $additionalData
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  array|string|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $message = 'Something went wrong', int $code = 400, $data = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a validation error JSON response.
     *
     * @param  array  $errors
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function validationError(array $errors, string $message = 'Validation errors')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    /**
     * Return a not found JSON response.
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function notFound(string $message = 'Resource not found')
    {
        return self::error($message, 404);
    }

    /**
     * Return an unauthorized JSON response.
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized')
    {
        return self::error($message, 401);
    }

    /**
     * Return a forbidden JSON response.
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden')
    {
        return self::error($message, 403);
    }

    /**
     * Return a paginated JSON response.
     *
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $paginator
     * @param  string  $resourceKey The key to use for the paginated items (e.g., 'tasks', 'users')
     * @param  string  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function paginated(
        LengthAwarePaginator $paginator, 
        string $resourceKey, 
        string $message = 'Data retrieved successfully', 
        int $code = 200, 
        array $additionalData = []
    ) {
        $data = [
            $resourceKey => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ]
        ];
    
        // Merge additional data with the main data array
    
        return self::success($data, $message, $code, $additionalData);
    }
}
