<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCredential;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

/**
 * Auth Service
 * Handles authentication, token management, and password reset
 */
class AuthService
{
    /**
     * Attempt to authenticate user with email and password
     */
    public function attemptLogin(string $email, string $password): ?User
    {
        $user = User::where('email', $email)
            ->where('disabled', false)
            ->first();

        if (!$user) {
            return null;
        }

        $credential = $user->credential;
        if (!$credential || !$this->verifyPassword($password, $credential->password_hash)) {
            return null;
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        return $user;
    }

    /**
     * Verify password against stored hash
     * Supports bcrypt and legacy SHA-256
     */
    private function verifyPassword(string $password, string $hash): bool
    {
        // Try bcrypt first
        if (Hash::check($password, $hash)) {
            return true;
        }

        // Check legacy SHA-256 hash
        if (hash('sha256', $password) === $hash) {
            return true;
        }

        return false;
    }

    /**
     * Generate API token for user
     */
    public function generateToken(User $user): string
    {
        return $user->createToken('api-token')->plainTextToken;
    }

    /**
     * Revoke all user tokens
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Initiate password reset process
     */
    public function initiatePasswordReset(string $email): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            // Return true anyway to prevent email enumeration
            return true;
        }

        $credential = $user->credential;
        if (!$credential) {
            $credential = UserCredential::create([
                'user_id' => $user->id,
            ]);
        }

        $token = Str::random(64);
        $credential->update([
            'reset_token' => hash('sha256', $token),
            'reset_token_expires_at' => Carbon::now()->addHours(24),
        ]);

        // TODO: Send password reset email
        // Mail::to($user->email)->send(new PasswordResetMail($token));

        return true;
    }

    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $hashedToken = hash('sha256', $token);
        
        $credential = UserCredential::where('reset_token', $hashedToken)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$credential) {
            return false;
        }

        $credential->update([
            'password_hash' => Hash::make($newPassword),
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        return true;
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        $credential = $user->credential;
        
        if (!$credential || !$this->verifyPassword($currentPassword, $credential->password_hash)) {
            return false;
        }

        $credential->update([
            'password_hash' => Hash::make($newPassword),
        ]);

        return true;
    }

    /**
     * Migrate legacy password hash to bcrypt
     */
    public function migratePasswordHash(User $user, string $password): void
    {
        $credential = $user->credential;
        
        if ($credential && !password_get_info($credential->password_hash)['algo']) {
            // Current hash is not bcrypt, migrate it
            $credential->update([
                'password_hash' => Hash::make($password),
            ]);
        }
    }
}
