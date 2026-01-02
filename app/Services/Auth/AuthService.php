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

class AuthService
{
    /**
     * Attempt user login with credentials.
     */
    public function attemptLogin(array $credentials): User
    {
        if (!Auth::attempt($credentials)) {
            throw new InvalidCredentialsException();
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();

        if (!$user->email_verified_at) {
            throw new AccountNotVerifiedException();
        }

        return $user;
    }

    /**
     * Generate access and refresh tokens and return auth response data.
     */
    public function generateAuthResponse(User $user): array
    {
        $accessToken = $user->createToken('API Token')->plainTextToken;
        $refreshToken = Str::random(60);

        // Store refresh token with 7 days TTL in Redis
        Redis::setex("refresh_token:{$refreshToken}", 60 * 60 * 24 * 7, $user->id);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_2fa_enabled' => (int)Redis::exists("user:{$user->id}:2fa_enabled"),
            ],
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 60 * 60 * 24 * 7,
        ];
    }

    /**
     * Revoke current user's token.
     */
    public function revokeToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
