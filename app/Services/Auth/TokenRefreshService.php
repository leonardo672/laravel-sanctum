<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Exceptions\Auth\{
    InvalidRefreshTokenException,
    TokenGenerationException
};
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class TokenRefreshService
{
    /**
     * Refresh access and refresh tokens using a valid refresh token.
     */
    public function refresh(string $refreshToken): array
    {
        if (!preg_match('/^[a-zA-Z0-9]{60}$/', $refreshToken)) {
            throw new InvalidRefreshTokenException();
        }

        $userId = Redis::get("refresh_token:{$refreshToken}");

        if (!$userId) {
            throw new InvalidRefreshTokenException();
        }

        $user = User::findOrFail($userId);

        $accessToken = $user->createToken('API Token')->plainTextToken;
        $newRefreshToken = Str::random(60);

        $ttl = Redis::ttl("refresh_token:{$refreshToken}");
        $expiration = $ttl > 0 ? $ttl : 60 * 60 * 24 * 7;

        Redis::setex("refresh_token:{$newRefreshToken}", $expiration, $userId);
        Redis::del("refresh_token:{$refreshToken}");

        return [
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in' => $expiration,
        ];
    }
}
