<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyCodeRequest;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Mail\VerificationCodeMail;


class TwoFactorAuthController extends Controller
{
    use HttpResponses;

    public function verify2fa(VerifyCodeRequest $request)
    {
        if (!Auth::check()) {
            if (!$request->has(['email', 'password'])) {
                return $this->error('', 'Authentication credentials required', 401);
            }
    
            if (!Auth::attempt($request->only(['email', 'password']))) {
                return $this->error('', 'Invalid credentials', 401);
            }
        }
    
        $user = $request->user() ?? Auth::user();
        $ip = $request->ip();
        $userAgent = $request->userAgent();
    
        if ($request->bearerToken()) {
            $token = $user->currentAccessToken();
            
            if (!$token || !$token->can('verify-2fa')) {
                Log::warning('Invalid 2FA token scope', [
                    'user_id' => $user->id,
                    'ip' => $ip,
                    'user_agent' => Str::limit($userAgent, 120)
                ]);
                return $this->error('', 'Invalid authentication token', 403);
            }
        }
    
        $redisKey = "vc:{$user->id}";
        [$storedCode, $deleted] = Redis::multi()
            ->get($redisKey)
            ->del($redisKey)
            ->exec();
    
        if (!$storedCode || !hash_equals((string)$storedCode, (string)$request->code)) {
            Log::warning('Invalid 2FA attempt', [
                'user_id' => $user->id,
                'ip' => $ip,
                'failed_code' => $request->code,
                'expected_code' => $storedCode ? '****' . substr($storedCode, -2) : null
            ]);
            return $this->error('', 'Invalid or expired verification code', 422);
        }
    
        if ($request->bearerToken()) {
            $user->currentAccessToken()->delete();
        }
    
        Log::info('2FA verification succeeded', [
            'user_id' => $user->id,
            'ip' => $ip,
            'device' => Str::limit($userAgent, 120)
        ]);
    
        return app('App\Http\Controllers\AuthController')->generateUserTokens($user);
    }

    public function toggle2fa(Request $request)
    {
        try {
            $user = $request->user();
            $key = "user:{$user->id}:2fa_enabled";

            if (Redis::exists($key)) {
                Redis::del($key);
                $status = 'disabled';
                Log::info("2FA disabled", ['user_id' => $user->id]);
            } else {
                Redis::setex($key, 60 * 60 * 24 * 30, 'true');
                $status = 'enabled';
                Log::info("2FA enabled", ['user_id' => $user->id]);
            }

            return $this->success([
                'message' => "Two-factor authentication {$status}.",
                'is_2fa_enabled' => (int)Redis::exists($key)
            ]);

        } catch (\Exception $e) {
            Log::error('2FA toggle failed: ' . $e->getMessage());
            return $this->error('', 'Failed to update 2FA settings.', 500);
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