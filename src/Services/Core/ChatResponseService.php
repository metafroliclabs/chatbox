<?php

namespace Metafroliclabs\LaravelChat\Services\Core;

use Illuminate\Http\JsonResponse;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;

class ChatResponseService implements ChatResponseContract
{
    public function success($data = [], int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $data,
        ], $code);
    }

    public function fail($errors = [], int $code = 422): JsonResponse
    {
        return response()->json([
            'status' => false,
            'errors' => $errors,
        ], $code);
    }
}
