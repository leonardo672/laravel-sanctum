<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Exceptions\Auth\{
    InvalidCredentialsException,
    AccountNotVerifiedException,
    TokenGenerationException
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function attemptLogin(array $credentials): User
    {
        if (!Auth::attempt($credentials)) {
            throw new InvalidCredentialsException();
        }

        $user = User::where('email', $credentials['email'])->first();

        if (!$user->email_verified_at) {
            throw new AccountNotVerifiedException();
        }

        return $user;
    }

    public function generateAuthResponse(User $user): array
    {
        try {
            $accessToken = $user->createToken('API Token')->plainTextToken;
            $refreshToken = Str::random(60);
            
            // Store refresh token in Redis with 7-day expiration
            Redis::setex("refresh_token:{$refreshToken}", 60 * 60 * 24 * 7, $user->id); // Key, TTL (time to live): 60 * 60 * 24 * 7 → 7 days in seconds, value
            
            Log::debug('Tokens generated and stored', [  // Logs debug information for development purposes.
                'user_id' => $user->id, 
                'refresh_token_key' => "refresh_token:{$refreshToken}"
            ]);
    
            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_2fa_enabled' => (int)Redis::exists("user:{$user->id}:2fa_enabled")
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => 60 * 60 * 24 * 7 // 7 days in seconds
            ];
        } catch (\Exception $e) {
            Log::error('Token generation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            throw new TokenGenerationException();
        }
    }

    public function revokeToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}