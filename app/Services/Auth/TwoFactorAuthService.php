<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerificationCodeMail;

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
     * Toggle the 2FA status (enable/disable).
     * If enabling, immediately generate and send verification code.
     */
    public function toggle2FA(User $user): array
    {
        $key = "user:{$user->id}:2fa_enabled";

        if (Redis::exists($key)) {
            Redis::del($key);
            $status = 'disabled';
            Log::info("2FA disabled", ['user_id' => $user->id]);
        } else {
            Redis::setex($key, 60 * 60 * 24 * 30, 'true'); // 30 days
            $status = 'enabled';
            Log::info("2FA enabled", ['user_id' => $user->id]);

            // Generate and send the verification code right after enabling
            $this->generateAndSendCode($user);
        }

        return [
            'status' => $status,
            'is_2fa_enabled' => (int) Redis::exists($key),
        ];
    }

    /**
     * Initiate 2FA process by generating a code and sending it to user.
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
     * Generate and send a 2FA code to the user.
     */
    public function generateAndSendCode(User $user): void
    {
        $code = $this->generateVerificationCode($user);
        $this->sendVerificationCode($user, $code);
    }

    /**
     * Verify the submitted 2FA code.
     */
    public function verifyCode(User $user, string $inputCode, string $ip, string $userAgent): bool
    {
        $redisKey = "vc:{$user->id}";
        [$storedCode, $deleted] = Redis::multi()
            ->get($redisKey)
            ->del($redisKey)
            ->exec();

        if (!$storedCode || !hash_equals((string) $storedCode, (string) $inputCode)) {
            Log::warning('Invalid 2FA attempt', [
                'user_id' => $user->id,
                'ip' => $ip,
                'failed_code' => $inputCode,
                'expected_code' => $storedCode ? '****' . substr($storedCode, -2) : null
            ]);
            return false;
        }

        Log::info('2FA verification succeeded', [
            'user_id' => $user->id,
            'ip' => $ip,
            'device' => Str::limit($userAgent, 120)
        ]);

        return true;
    }

    /**
     * Generate a 6-digit code and store in Redis.
     */
    private function generateVerificationCode(User $user): string
    {
        $code = random_int(100000, 999999);
        Redis::setex("vc:{$user->id}", 600, $code); // 10 minutes expiry
        return $code;
    }

    /**
     * Send the verification code via email.
     */
    private function sendVerificationCode(User $user, string $code): void
    {
        Mail::to($user->email)->send(new VerificationCodeMail($code));
    }
}
