<?php

namespace App\Services\Registration;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\Registration\VerificationService;

class RegistrationService
{
    public function __construct( // the constructer 
        private VerificationService $verificationService
    ) {}

    /**
     * Create a new user and send verification email.
     * Returns an array: ['success' => bool, 'user' => User|null, 'error' => string|null]
     */
    public function createUser(array $data): array
    {
        if ($this->emailExists($data['email'])) {
            Log::info("Email already exists: " . $data['email']);
            return [
                'success' => false,
                'user' => null,
                'error' => 'User already exists.'
            ];
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => null
            ]);

            if (!$user || !$user->exists) {
                Log::error('User not saved in DB');
                return [
                    'success' => false,
                    'user' => null,
                    'error' => 'User not saved in DB.'
                ];
            }

            $this->verificationService->sendEmailVerification($user);

            return [
                'success' => true,
                'user' => $user,
                'error' => null
            ];

        } catch (\Exception $e) {
            Log::error("Registration failed: " . $e->getMessage());
            return [
                'success' => false,
                'user' => null,
                'error' => 'User creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if email exists
     */
    private function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }
}
