<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RolePermission Middleware
 * 
 * Enforces RBAC permissions based on feature codes.
 * Checks if user has access to the requested feature via their role assignments.
 */
class RolePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $featureCode
     */
    public function handle(Request $request, Closure $next, string $featureCode): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 401);
        }

        // Master users have full access
        if ($user->isMaster()) {
            return $next($request);
        }

        // Check if user has access to the feature
        if (!$user->hasFeatureAccess($featureCode)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You do not have permission to access this feature.',
                    'details' => [
                        'required_feature' => $featureCode,
                    ],
                ],
            ], 403);
        }

        return $next($request);
    }
}
