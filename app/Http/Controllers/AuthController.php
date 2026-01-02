<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Services\Auth\AuthService;
use App\Services\Auth\TwoFactorAuthService;
use App\Services\Auth\TokenRefreshService;
use App\Services\Auth\VerificationService;
use App\Traits\HttpResponses;
use App\Exceptions\Auth\{
    InvalidCredentialsException,
    AccountNotVerifiedException,
    TwoFactorRequiredException,
    InvalidRefreshTokenException,
    TokenGenerationException
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        try {
            $user = $this->authService->attemptLogin($request->validated());

            if (!$user->email_verified_at) {
                $this->verificationService->resendVerification($user);
                throw new AccountNotVerifiedException();
            }

            if ($this->twoFactorAuthService->isEnabled($user)) {
                $response = $this->twoFactorAuthService->initiate2FA($user);
                throw new TwoFactorRequiredException($response);
            }

            return $this->success($this->authService->generateAuthResponse($user));

        } catch (InvalidCredentialsException $e) {
            Log::warning('Login failed: Invalid credentials', ['email' => $request->email]);
            return $this->error('', $e->getMessage(), $e->getCode());

        } catch (AccountNotVerifiedException $e) {
            Log::info('Login attempt with unverified email', ['email' => $request->email]);
            return $this->error('', $e->getMessage(), $e->getCode());

        } catch (TwoFactorRequiredException $e) {
            Log::info('2FA required for login', ['email' => $request->email]);
            return $this->success($e->getData());

        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return $this->error('', 'Login failed. Please try again.', 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->authService->revokeToken($request->user());
            Log::info('User logged out', ['user_id' => $request->user()->id]);
            
            return $this->success(['message' => 'Logged out successfully']);

        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return $this->error('', 'Logout failed. Please try again.', 500);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $request->validate([
                'refresh_token' => 'required|string|min:60|max:60'
            ]);
    
            $tokens = $this->tokenRefreshService->refresh($request->refresh_token);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Tokens refreshed successfully',
                'data' => $tokens
            ]);
    
        } catch (InvalidRefreshTokenException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], $e->getCode());
    
        } catch (TokenGenerationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate new tokens',
                'data' => null
            ], 500);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'data' => null
            ], 500);
        }
    }
}