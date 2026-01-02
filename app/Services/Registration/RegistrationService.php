<?php

namespace App\Services\Registration;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\Registration\VerificationService;

class RegistrationService
{
    public function __construct(
        private VerificationService $verificationService
    ) {}

    /**
     * Create a new user and send verification email.
     *
     * @return array{success: bool, user: User|null, error: string|null}
     */
    public function createUser(array $data): array
    {
        if ($this->emailExists($data['email'])) {
            return $this->failureResponse('User already exists.');
        }

        $user = $this->createNewUser($data);

        if (!$user || !$user->exists) {
            return $this->failureResponse('User not saved in DB.');
        }

        $this->verificationService->sendEmailVerification($user);

        return $this->successResponse($user);
    }

    /**
     * Check if email already exists.
     */
    private function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Create and persist a new user.
     */
    private function createNewUser(array $data): ?User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => null
        ]);
    }

    /**
     * Generate a standardized failure response.
     */
    private function failureResponse(string $message): array
    {
        return [
            'success' => false,
            'user' => null,
            'error' => $message,
        ];
    }

    /**
     * Generate a standardized success response.
     */
    private function successResponse(User $user): array
    {
        return [
            'success' => true,
            'user' => $user,
            'error' => null,
        ];
    }
}
