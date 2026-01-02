<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Exceptions\Auth\{
    InvalidRefreshTokenException,
    TokenGenerationException
};
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TokenRefreshService
{
    public function refresh(string $refreshToken): array
    {
        Log::debug('Refresh token attempt', ['token' => $refreshToken]);
        
        // Validate token format first
        if (!preg_match('/^[a-zA-Z0-9]{60}$/', $refreshToken)) {
            Log::warning('Invalid refresh token format');
            throw new InvalidRefreshTokenException();
        }

        // Get user ID from Redis
        $userId = Redis::get("refresh_token:{$refreshToken}");
        
        Log::debug('Redis lookup', [
            'key' => "refresh_token:{$refreshToken}",
            'user_id' => $userId
        ]);

        if (!$userId) {
            Log::warning('Refresh token not found in Redis');
            throw new InvalidRefreshTokenException();
        }

        try {
            $user = User::findOrFail($userId);
            
            // Generate new tokens
            $accessToken = $user->createToken('API Token')->plainTextToken;
            $newRefreshToken = Str::random(60);
            
            // Get remaining TTL or use default (7 days)
            $ttl = Redis::ttl("refresh_token:{$refreshToken}");
            $expiration = $ttl > 0 ? $ttl : 604800; // 7 days in seconds
            
            // Store new refresh token
            Redis::setex("refresh_token:{$newRefreshToken}", $expiration, $userId);
            
            // Delete old refresh token
            Redis::del("refresh_token:{$refreshToken}");
            
            Log::info('Tokens refreshed', [
                'user_id' => $userId,
                'new_refresh_token' => substr($newRefreshToken, 0, 10).'...', // Log partial token
                'expires_in' => $expiration
            ]);
            
            return [
                'access_token' => $accessToken,
                'refresh_token' => $newRefreshToken,
                'expires_in' => $expiration
            ];
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('User not found for refresh token', [
                'user_id' => $userId,
                'token' => substr($refreshToken, 0, 10).'...'
            ]);
            throw new InvalidRefreshTokenException();
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new TokenGenerationException();
        }
    }
}