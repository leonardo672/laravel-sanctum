<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Traits\HttpResponses;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Registration\RegistrationService;
use App\Services\Registration\VerificationService;

class RegisterController extends Controller
{
    use HttpResponses;

    public function __construct(
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

        $this->verificationService->verifyEmail($user, $request->code);

        return $this->success(['message' => 'Email verified successfully.']);
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

        $this->verificationService->resendVerificationCode($user);

        return $this->success(['message' => 'New verification code sent.']);
    }
}
