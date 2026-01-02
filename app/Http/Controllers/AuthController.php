<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Services\Auth\AuthService;
use App\Services\Auth\TwoFactorAuthService;
use App\Services\Auth\TokenRefreshService;
use App\Services\Auth\VerificationService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use HttpResponses;

    public function __construct(
        private AuthService $authService,
        private TwoFactorAuthService $twoFactorAuthService,
        private TokenRefreshService $tokenRefreshService,
        private VerificationService $verificationService
    ) {}

    public function login(LoginUserRequest $request)
    {
        $user = $this->authService->attemptLogin($request->validated());

        if (!$user->email_verified_at) {
            $this->verificationService->resendVerification($user);
            throw new \App\Exceptions\Auth\AccountNotVerifiedException();
        }

        if ($this->twoFactorAuthService->isEnabled($user)) {
            $response = $this->twoFactorAuthService->initiate2FA($user);
            throw new \App\Exceptions\Auth\TwoFactorRequiredException($response);
        }

        return $this->success($this->authService->generateAuthResponse($user));
    }

    public function logout(Request $request)
    {
        $this->authService->revokeToken($request->user());

        return $this->success(['message' => 'Logged out successfully']);
    }

    public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string|size:60',
        ]);

        $tokens = $this->tokenRefreshService->refresh($request->refresh_token);

        return $this->success($tokens, 'Tokens refreshed successfully');
    }

    /**
     * Complete login after successful 2FA verification.
     */
    public function loginFrom2FA($user)
    {
        return $this->success($this->authService->generateAuthResponse($user));
    }
}
