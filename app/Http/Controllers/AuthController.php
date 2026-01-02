<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Traits\HttpResponses;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request)
    {
        try {
            $request->validated();

            if (!Auth::attempt($request->only(['email', 'password']))) {
                Log::warning('Login failed: Invalid credentials', ['email' => $request->email]);
                return $this->error('', 'Invalid credentials.', 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user->email_verified_at) {
                Log::info('Resending verification code for unverified user', ['user_id' => $user->id]);
                app('App\Http\Controllers\RegisterController')->generateVerificationCode($user);
                return $this->error('', 'Email not verified. A new verification code has been sent.', 403);
            }

            if (Redis::exists("user:{$user->id}:2fa_enabled")) {
                app('App\Http\Controllers\TwoFactorAuthController')->generateVerificationCode($user);
                Log::info('2FA code sent', ['user_id' => $user->id]);
                
                $tempToken = $user->createToken(
                    '2FA Temp Token', 
                    ['verify-2fa']
                )->plainTextToken;
                
                return $this->success([
                    'message' => 'Two-factor authentication code sent to your email.',
                    'requires_2fa' => true,
                    'temp_token' => $tempToken
                ]);
            }
           
            return $this->generateUserTokens($user);

        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return $this->error('', 'Login failed. Please try again.', 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            Log::info('User logged out', ['user_id' => $request->user()->id]);
            
            return $this->success(['message' => 'Logged out successfully.']);

        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return $this->error('', 'Logout failed. Please try again.', 500);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $request->validate(['refresh_token' => 'required']);
            
            $userId = Redis::get("refresh_token:{$request->refresh_token}");
            
            if (!$userId) {
                Log::warning('Invalid refresh token attempt', ['token' => $request->refresh_token]);
                return $this->error('', 'Invalid or expired refresh token.', 403);
            }

            $user = User::find($userId);
            $newAccessToken = $user->createToken('API Token of ' . $user->name)->plainTextToken;
            Log::info('Access token refreshed', ['user_id' => $userId]);
            
            return $this->success([
                'access_token' => $newAccessToken
            ]);

        } catch (\Exception $e) {
            Log::error('Token refresh failed: ' . $e->getMessage());
            return $this->error('', 'Token refresh failed. Please login again.', 500);
        }
    }

    public function generateUserTokens(User $user)
    {
        $accessToken = $user->createToken(
            'API Token of ' . $user->name,
            ['*']
        )->plainTextToken;
        
        $refreshToken = Str::random(60);
        Redis::setex("refresh_token:$refreshToken", 60 * 60 * 24 * 7, $user->id);
        
        Log::info('User tokens generated', ['user_id' => $user->id]);

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_2fa_enabled' => (int)Redis::exists("user:{$user->id}:2fa_enabled")
            ],
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ]);
    }
}