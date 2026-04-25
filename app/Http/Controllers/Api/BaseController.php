<?php
// app/Http/Controllers/Api/BaseController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Send success response
     */
    protected function sendResponse($data, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'timestamp' => now()->toDateTimeString()
        ], $code);
    }

    /**
     * Send paginated response
     */
    protected function sendPaginatedResponse($data, string $message = ''): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'message' => $message,
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl()
            ],
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Send error response
     */
    protected function sendError(string $message, array $errors = [], int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toDateTimeString()
        ], $code);
    }
}