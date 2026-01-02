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

        try {
            $code = $this->generateCode($user);
            Mail::to($user->email)->send(new VerificationCodeMail($code));
        } catch (\Exception $e) {
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
    
        if ($storedCode !== $code) { // cheacks if both not identical - means "not identical" 
            throw new VerificationException('Invalid verification code');
        }
    
        Redis::del("verification:{$user->id}"); // To remove a stored verification-related value for that user from Redis.
    
        // Update email verification timestamp
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
        $attempts = Cache::get($key, 0); // Means: Get the number of verification attempts from the cache. -  If not found: Defaults to 0.
    
            if ($attempts >= self::MAX_ATTEMPTS) {
            throw new VerificationLimitExceededException();
        }

        Cache::put($key, $attempts + 1, now()->addHour()); // (name, value, expiration time) / updates the attempt counter for that user in the cache and keeps it for 1 hour.
    }

    /**
     * Generate and store a random verification code for the user.
     */
    private function generateCode(User $user): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Redis::setex("verification:{$user->id}", self::CODE_EXPIRY, $code); // set and expire - key-value 
        return $code;
    }
}
