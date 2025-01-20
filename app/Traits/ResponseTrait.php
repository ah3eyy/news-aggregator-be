<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait ResponseTrait
{

    public function successResponse($data, $message): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message
        ], 200);
    }

    public function errorResponse($message, $code = 422): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message
        ], $code);
    }
}
