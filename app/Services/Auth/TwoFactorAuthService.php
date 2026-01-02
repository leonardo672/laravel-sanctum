<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Exceptions\Auth\TwoFactorRequiredException;
use Illuminate\Support\Facades\Redis;

class TwoFactorAuthService
{
    public function isEnabled(User $user): bool
    {
        return Redis::exists("user:{$user->id}:2fa_enabled"); // Checks if the Redis key and returns true/false
    }

    public function initiate2FA(User $user): array
    {
        $code = $this->generateVerificationCode($user);
        $this->sendVerificationCode($user, $code);

        return [
            'message' => 'Two-factor authentication code sent to your email.',
            'requires_2fa' => true,
            'temp_token' => $user->createToken('2FA Temp Token', ['verify-2fa'])->plainTextToken
        ];
    }

    private function generateVerificationCode(User $user): string
    {
        $code = random_int(100000, 999999);
        Redis::setex("2fa:code:{$user->id}", 300, $code);
        return $code;
    }

    private function sendVerificationCode(User $user, string $code): void
    {
        // Implement your 2FA code delivery (email/SMS)
    }
}