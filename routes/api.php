<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\UserMediaController; // Add this line

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-code', [RegisterController::class, 'verifyCode']);
Route::post('/resend-code', [RegisterController::class, 'resendCode']);
Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
Route::post('/forgot-password', [PasswordResetController::class, 'requestPasswordReset']);
Route::get('/validate-reset-token', [PasswordResetController::class, 'validatePasswordResetToken']);
Route::post('/reset-password', [PasswordResetController::class, 'updatePassword']);
Route::post('/verify-2fa', [TwoFactorAuthController::class, 'verify2fa']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/toggle-2fa', [TwoFactorAuthController::class, 'toggle2fa']);
    
    // Add these new media routes within the auth group
    Route::prefix('users/{user}')->group(function () {
        // Upload/update profile picture
        Route::post('/media', [UserMediaController::class, 'store']);
        
        // Get profile picture
        Route::get('/media', [UserMediaController::class, 'show']);
        
        // Delete profile picture
        Route::delete('/media', [UserMediaController::class, 'destroy']);
    });
});