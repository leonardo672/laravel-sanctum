<?php

namespace App\Services\Registration;

use App\Models\User;
use App\Mail\VerificationCodeMail;
use App\Exceptions\Registration\{
    VerificationException,
    VerificationCodeExpiredException,
    VerificationLimitExceededException
};
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class VerificationService
{
    private const MAX_ATTEMPTS = 5;
    private const CODE_EXPIRY = 600; // 10 minutes

    /**
     * Send a verification code to the user's email.
     *
     * @throws VerificationException
     * @throws VerificationLimitExceededException
     */
    public function sendEmailVerification(User $user): void
    {
        $this->checkRateLimit($user);

        $code = $this->generateCode($user);

        if (!$this->dispatchEmail($user->email, $code)) {
            throw new VerificationException('Failed to send verification code');
        }
    }

    /**
     * Verify the provided code matches the one stored for the user.
     *
     * @throws VerificationException
     * @throws VerificationCodeExpiredException
     */
    public function verifyEmail(User $user, string $code): bool
    {
        $storedCode = Redis::get("verification:{$user->id}");

        if (!$storedCode) {
            throw new VerificationCodeExpiredException();
        }

        if ($storedCode !== $code) {
            throw new VerificationException('Invalid verification code');
        }

        Redis::del("verification:{$user->id}");

        $user->email_verified_at = now();
        $user->save();

        return true;
    }

    /**
     * Re-send the verification code to the user.
     *
     * @throws VerificationException
     * @throws VerificationLimitExceededException
     */
    public function resendVerificationCode(User $user): void
    {
        $this->sendEmailVerification($user);
    }

    /**
     * Ensure the user hasn't exceeded the rate limit for requesting codes.
     *
     * @throws VerificationLimitExceededException
     */
    private function checkRateLimit(User $user): void
    {
        $key = "verification_attempts:{$user->id}";
        $attempts = Cache::get($key, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            throw new VerificationLimitExceededException();
        }

        Cache::put($key, $attempts + 1, now()->addHour());
    }

    /**
     * Generate and store a random verification code for the user.
     */
    private function generateCode(User $user): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Redis::setex("verification:{$user->id}", self::CODE_EXPIRY, $code);
        return $code;
    }

    /**
     * Attempt to send the verification email.
     */
    private function dispatchEmail(string $email, string $code): bool
    {
        try {
            Mail::to($email)->send(new VerificationCodeMail($code));
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
