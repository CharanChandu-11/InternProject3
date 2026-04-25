<?php
// app/Providers/ApiServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // API Response Macros
        Response::macro('apiSuccess', function($data, $message = '', $code = 200) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => $message,
                'timestamp' => now()->toDateTimeString()
            ], $code);
        });

        Response::macro('apiError', function($message, $errors = [], $code = 400) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
                'timestamp' => now()->toDateTimeString()
            ], $code);
        });

        Response::macro('apiPaginate', function($data, $message = '') {
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
        });
    }

    public function register()
    {
        //
    }
}