<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Exceptions\Auth\VerificationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class VerificationService
{
    public function resendVerification(User $user): void
    {
        try {
            $code = $this->generateVerificationCode($user);
            Mail::to($user->email)->send(new VerificationCodeMail($code));
        } catch (\Exception $e) {
            throw new VerificationException();
        }
    }

    private function generateVerificationCode(User $user): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->verification_code = $code;
        $user->save();
        return $code;
    }
}