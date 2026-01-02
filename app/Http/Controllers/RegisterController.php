<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Traits\HttpResponses;
use App\Repositories\UserRepositoryInterface;
use App\Services\Registration\RegistrationService;
use App\Services\Registration\VerificationService;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use HttpResponses;

    public function __construct(
        private RegistrationService $registrationService,
        private VerificationService $verificationService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(StoreUserRequest $request)
    {
        $result = $this->registrationService->createUser($request->validated());

        return $result['success']
            ? $this->success([
                'message' => 'User registered. Verification code sent.',
                'user' => $result['user']
              ], 201)
            : $this->error('', $result['error'], 422);
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        if (!$user = $this->userRepository->findByEmail($request->email)) {
            return $this->error('', 'User not found.', 404);
        }

        $this->verificationService->verifyEmail($user, $request->code);

        return $this->success(['message' => 'Email verified successfully.']);
    }

    public function resendCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        if (!$user = $this->userRepository->findByEmail($request->email)) {
            return $this->error('', 'User not found.', 404);
        }

        if ($user->email_verified_at) {
            return $this->error('', 'User already verified.', 422);
        }

        $this->verificationService->resendVerificationCode($user);

        return $this->success(['message' => 'New verification code sent.']);
    }
}