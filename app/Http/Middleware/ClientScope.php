<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Client Scope Middleware
 * Ensures all requests are scoped to the authenticated user's client
 * and validates client_id parameter when provided
 */
class ClientScope
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], 401);
        }

        // Get client_id from request or use user's default
        $requestedClientId = $request->input('client_id') ?? $request->query('client_id');

        if ($requestedClientId) {
            // Verify user has access to requested client
            if (!$this->userHasClientAccess($user, (int) $requestedClientId)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'Access denied to this client',
                    ],
                ], 403);
            }
            $request->merge(['client_id' => (int) $requestedClientId]);
        } else {
            // Use user's primary client
            $request->merge(['client_id' => $user->client_id]);
        }

        return $next($request);
    }

    /**
     * Check if user has access to the specified client
     */
    private function userHasClientAccess($user, int $clientId): bool
    {
        // Master admins can access all clients
        if ($user->isMasterAdmin()) {
            return true;
        }

        // Regular users can only access their own client
        return $user->client_id === $clientId;
    }
}
