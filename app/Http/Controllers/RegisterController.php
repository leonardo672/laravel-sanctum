<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Traits\HttpResponses;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use HttpResponses;

    public function register(StoreUserRequest $request)
    {
        try {
            $validated = $request->validated();
            
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => null
            ]);

            Log::info('User registered successfully', ['user_id' => $user->id]);

            $this->generateVerificationCode($user);

            return $this->success(['message' => 'User registered. Verification code sent to email.']);

        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return $this->error('', 'Registration failed. Please try again.', 500);
        }
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
        
            if (!$user) {
                Log::warning('Verify code failed: User not found', ['email' => $request->email]);
                return $this->error('', 'User not found.', 404);
            }
        
            $redisKey = "vc:{$user->id}";
            $storedCode = Redis::get($redisKey);
            
            if (!$storedCode || $storedCode != $request->code) {
                Log::warning('Invalid verification code attempt', ['user_id' => $user->id]);
                return $this->error('', 'Invalid or expired verification code.', 422);
            }
        
            $user->update(['email_verified_at' => now()]);
            Redis::del($redisKey);
            Log::info('Email verified successfully', ['user_id' => $user->id]);
        
            return $this->success(['message' => 'Email verified successfully.']);
    
        } catch (\Exception $e) {
            Log::error('Verification failed: ' . $e->getMessage());
            return $this->error('', 'Verification failed. Please try again.', 500);
        }
    }

    public function resendCode(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::warning('Resend code failed: User not found', ['email' => $request->email]);
                return $this->error('', 'User not found.', 404);
            }

            if ($user->email_verified_at) {
                Log::info('Resend code skipped: User already verified', ['user_id' => $user->id]);
                return $this->error('', 'User already verified.', 422);
            }

            Redis::del("verification_code:{$user->id}");
            $this->generateVerificationCode($user);

            return $this->success(['message' => 'New verification code sent.']);

        } catch (\Exception $e) {
            Log::error('Resend code failed: ' . $e->getMessage());
            return $this->error('', 'Failed to resend code. Please try again.', 500);
        }
    }

    public function generateVerificationCode(User $user)
    {
        $code = random_int(100000, 999999);
        $key = "vc:{$user->id}";
        Redis::connection()->client()->setex($key, 600, $code);
        Mail::to($user->email)->send(new VerificationCodeMail($code));
    }
}