<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    /**
     * Get the authenticated user
     *
     * @return \App\Models\User|null
     */
    public static function user()
    {
        return Auth::user();
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public static function check(): bool
    {
        return Auth::check();
    }

    /**
     * Get the authentication token from request
     *
     * @return string|null
     */
    public static function getTokenFromRequest(): ?string
    {
        $header = request()->header('Authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    /**
     * Revoke all tokens for a user
     *
     * @param int $userId
     * @return void
     */
    public static function revokeAllTokens(int $userId): void
    {
        \App\Models\User::find($userId)?->tokens()->delete();
    }

    /**
     * Get user's active tokens count
     *
     * @param int $userId
     * @return int
     */
    public static function getActiveTokensCount(int $userId): int
    {
        return \App\Models\User::find($userId)?->tokens()->count() ?? 0;
    }
}
