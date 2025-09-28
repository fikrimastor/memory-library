<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

abstract class BaseApiController extends Controller
{
    /**
     * Return a standardized success response.
     */
    protected function success(mixed $data = null, ?string $message = null, array $meta = [], int $status = 200): JsonResponse
    {
        $payload = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * Return a standardized error response.
     */
    protected function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Format a resource collection with pagination metadata.
     */
    protected function resourceCollection(AnonymousResourceCollection $collection, ?string $message = null, int $status = 200): JsonResponse
    {
        $response = $collection->response();
        $content = $response->getData(true);

        $data = $content['data'] ?? [];
        unset($content['data']);

        return $this->success(
            data: $data,
            message: $message,
            meta: $content,
            status: $status,
        );
    }
}
