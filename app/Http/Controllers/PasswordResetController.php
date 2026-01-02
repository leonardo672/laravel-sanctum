<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Traits\HttpResponses;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    use HttpResponses;

    public function requestPasswordReset(PasswordResetRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::info('Password reset requested for non-existent email', ['email' => $request->email]);
            return $this->success(['message' => 'If the email exists, a password reset link has been sent.']);
        }

        $token = Str::random(60);
        Redis::setex("password_reset:{$token}", 7200, $user->id);

        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token;
        Mail::to($user->email)->send(new PasswordResetMail($resetUrl));

        Log::info('Password reset link sent', ['user_id' => $user->id]);
        return $this->success(['message' => 'If the email exists, a password reset link has been sent.']);
    }

    public function validatePasswordResetToken(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return $this->error('', 'Invalid token.', 400);
        }

        $userId = Redis::get("password_reset:{$token}");

        if (!$userId) {
            return $this->error('', 'Invalid or expired token.', 404);
        }

        return $this->success(['valid' => true, 'token' => $token]);
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        $token = $request->token;
        $userId = Redis::get("password_reset:{$token}");

        if (!$userId) {
            Log::warning('Invalid password reset token attempt');
            return $this->error('', 'Invalid or expired token.', 404);
        }

        $user = User::findOrFail($userId);
        $user->password = Hash::make($request->password);
        $user->save();

        Redis::del("password_reset:{$token}");
        $user->tokens()->delete();

        Log::info('Password updated successfully', ['user_id' => $user->id]);
        return $this->success(['message' => 'Password updated successfully.']);
    }
}
