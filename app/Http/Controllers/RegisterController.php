<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Traits\HttpResponses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Registration\RegistrationService;
use App\Services\Registration\VerificationService;
use App\Exceptions\Registration\{
    VerificationException,
    VerificationCodeExpiredException,
    VerificationLimitExceededException
};

class RegisterController extends Controller
{
    use HttpResponses;

    public function __construct( // function is a constructor that automatically runs when the AuthController is created, injecting required service dependencies. 
        private RegistrationService $registrationService,
        private VerificationService $verificationService
    ) {}

    public function register(StoreUserRequest $request)
    {
        $result = $this->registrationService->createUser($request->validated());

        if (!$result['success']) {
            return $this->error('', $result['error'], 422);
        }

        return $this->success([
            'message' => 'User registered. Verification code sent to email.',
            'user' => $result['user']
        ]);
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('', 'User not found.', 404);
        }

        try {
            $this->verificationService->verifyEmail($user, $request->code);
            return $this->success(['message' => 'Email verified successfully.']);
        } catch (VerificationCodeExpiredException $e) {
            return $this->error('', 'Verification code expired.', 410);
        } catch (VerificationException $e) {
            return $this->error('', $e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Verification error: ' . $e->getMessage());
            return $this->error('', 'Verification failed. Try again later.', 500);
        }
    }

    public function resendCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('', 'User not found.', 404);
        }

        if ($user->email_verified_at) {
            return $this->error('', 'User already verified.', 422);
        }

        try {
            $this->verificationService->resendVerificationCode($user);
            return $this->success(['message' => 'New verification code sent.']);
        } catch (VerificationLimitExceededException $e) {
            return $this->error('', 'Verification limit exceeded. Try again later.', 429);
        } catch (VerificationException $e) {
            return $this->error('', $e->getMessage(), 500);
        }
    }
}
