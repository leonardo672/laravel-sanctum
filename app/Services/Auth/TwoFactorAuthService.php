<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Redis;

class TwoFactorAuthService
{
    /**
     * Check if 2FA is enabled for the user.
     */
    public function isEnabled(User $user): bool
    {
        return Redis::exists("user:{$user->id}:2fa_enabled");
    }

    /**
     * Initiate the 2FA process by generating and sending a code.
     */
    public function initiate2FA(User $user): array
    {
        $code = $this->generateVerificationCode($user);
        $this->sendVerificationCode($user, $code);

        return [
            'message' => 'Two-factor authentication code sent to your email.',
            'requires_2fa' => true,
            'temp_token' => $user->createToken('2FA Temp Token', ['verify-2fa'])->plainTextToken,
        ];
    }

    /**
     * Generate and store a 6-digit verification code in Redis.
     */
    private function generateVerificationCode(User $user): string
    {
        $code = random_int(100000, 999999);
        Redis::setex("2fa:code:{$user->id}", 300, $code); // Expires in 5 minutes
        return $code;
    }

    /**
     * Stub for sending the verification code.
     */
    private function sendVerificationCode(User $user, string $code): void
    {
        // Implement email or SMS sending logic here
    }
}
