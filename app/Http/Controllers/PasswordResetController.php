<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Traits\HttpResponses;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    use HttpResponses;

    protected $users;

    public function __construct(UserRepositoryInterface $users)
    {
        $this->users = $users;
    }

    public function requestPasswordReset(PasswordResetRequest $request)
    {
        $user = $this->users->findByEmail($request->email);

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

        $user = $this->users->findById($userId);
        $this->users->updatePassword($user, $request->password);

        Redis::del("password_reset:{$token}");

        Log::info('Password updated successfully', ['user_id' => $user->id]);
        return $this->success(['message' => 'Password updated successfully.']);
    }
}
