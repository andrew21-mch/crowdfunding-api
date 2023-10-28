<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user', [AuthController::class, 'updateUser']);
    Route::put('/user/password', [AuthController::class, 'changePassword']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('password/reset/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::group(['prefix' => 'email'], function () {
    Route::get('/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/resend', [AuthController::class, 'resendVerificationEmail'])->name('verification.resend');
});