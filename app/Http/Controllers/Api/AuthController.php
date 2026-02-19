<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentication Controller
 * Handles user login, logout, and password reset operations
 */
class AuthController extends BaseController
{
    /**
     * POST /api/v1/auth/login
     * Authenticate user and return JWT token
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $user->only(['id', 'email', 'first_name', 'last_name']),
            'expires_at' => now()->addDays(7)->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Invalidate current session token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Successfully logged out');
    }

    /**
     * POST /api/v1/auth/pin-login
     * Mobile PIN-based authentication
     */
    public function pinLogin(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'pin' => 'required|string|size:4',
        ]);

        // TODO: Implement PIN verification logic
        // $user = User::findOrFail($request->user_id);
        // Verify PIN against stored hash

        return $this->error('PIN login not implemented', 'NOT_IMPLEMENTED', 501);
    }

    /**
     * POST /api/v1/auth/forgot-password
     * Request password reset email
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:tr2_users,email',
        ]);

        // TODO: Implement password reset token generation and email
        // Password::sendResetLink($request->only('email'));

        return $this->success(null, 'Password reset email sent if account exists');
    }

    /**
     * POST /api/v1/auth/reset-password
     * Complete password reset with token
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // TODO: Implement password reset logic
        // Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), ...);

        return $this->success(null, 'Password has been reset');
    }
}
