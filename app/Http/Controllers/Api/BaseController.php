<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Base API Controller
 * Provides standardized response methods for all API controllers
 */
abstract class BaseController extends Controller
{
    /**
     * Return a successful response
     */
    protected function success($data = null, string $message = null, int $code = 200): JsonResponse
    {
        $response = ['success' => true];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a paginated response
     */
    protected function paginated($paginator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Return an error response
     */
    protected function error(string $message, string $code = 'ERROR', int $httpCode = 400, $details = null): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details !== null) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $httpCode);
    }

    /**
     * Return a validation error response
     */
    protected function validationError($errors): JsonResponse
    {
        return $this->error(
            'Validation failed',
            'VALIDATION_ERROR',
            422,
            $errors
        );
    }

    /**
     * Return a not found error response
     */
    protected function notFound(string $resource = 'Resource'): JsonResponse
    {
        return $this->error(
            "{$resource} not found",
            'NOT_FOUND',
            404
        );
    }

    /**
     * Return an unauthorized error response
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 'UNAUTHORIZED', 401);
    }

    /**
     * Return a forbidden error response
     */
    protected function forbidden(string $message = 'Access denied'): JsonResponse
    {
        return $this->error($message, 'FORBIDDEN', 403);
    }

    /**
     * Get the current client ID from request
     */
    protected function getClientId(): int
    {
        return request()->get('client_id') ?? auth()->user()->client_id;
    }
}
