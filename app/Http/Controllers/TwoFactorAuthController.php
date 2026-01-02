<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyCodeRequest;
use App\Http\Requests\Toggle2FARequest;
use App\Http\Requests\GenerateVerificationCodeRequest;
use App\Services\Auth\TwoFactorAuthService;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TwoFactorAuthController extends Controller
{
    use HttpResponses;

    public function __construct(
        private TwoFactorAuthService $twoFA
    ) {}

    /**
     * Verify the 2FA code provided by the user.
     */
    public function verify2fa(VerifyCodeRequest $request)
    {
        // Make sure Sanctum middleware is applied so this works:
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        $ip = $request->ip();
        $userAgent = Str::limit($request->userAgent(), 120);

        if ($request->bearerToken()) {
            $token = $user->currentAccessToken();

            if (!$token || !$token->can('verify-2fa')) {
                Log::warning('Invalid 2FA token scope', [
                    'user_id' => $user->id,
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                ]);
                return $this->error('Invalid authentication token', 403);
            }
        }

        $verified = $this->twoFA->verifyCode($user, $request->code, $ip, $userAgent);

        if (!$verified) {
            return $this->error('Invalid or expired verification code', 422);
        }

        // Remove temporary 2FA token
        if ($request->bearerToken()) {
            $user->currentAccessToken()->delete();
        }

        // Login the user and return full access token
        $authController = app(AuthController::class);
        return $authController->loginFrom2FA($user);
    }

    /**
     * Toggle 2FA on or off for the authenticated user.
     */
    public function toggle2fa(Toggle2FARequest $request)
    {
        $user = $request->user();
        $response = $this->twoFA->toggle2FA($user);

        return $this->success([
            'message' => "Two-factor authentication {$response['status']}.",
            'is_2fa_enabled' => $response['is_2fa_enabled']
        ]);
    }

    /**
     * Generate and send a 2FA verification code to the user's email.
     */
    public function generateVerificationCode(GenerateVerificationCodeRequest $request)
    {
        $user = $request->user();
        $this->twoFA->generateAndSendCode($user);

        return $this->success([
            'message' => 'Verification code sent to your email.',
        ]);
    }
}
