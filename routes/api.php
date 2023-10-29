<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CampaignController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user', [AuthController::class, 'updateUser']);
    Route::put('/user/password', [AuthController::class, 'changePassword']);

    // Campaign routes
    Route::post('/campaigns', [CampaignController::class, 'createCampaign']);
    Route::put('/campaigns/{campaign_id}', [CampaignController::class, 'updateCampaign']);
    Route::delete('/campaigns/{campaign_id}', [CampaignController::class, 'deleteCampaign']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('password/reset/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::group(['prefix' => 'email'], function () {
    Route::get('/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/resend', [AuthController::class, 'resendVerificationEmail'])->name('verification.resend');
});

// Campaign routes
Route::group(['prefix' => 'campaigns'], function () {
    Route::get('/', [CampaignController::class, 'getAllCampaigns']);
    Route::get('/{campaign_id}', [CampaignController::class, 'getCampaign']);
    Route::post('/{campaign_id}/donate', [CampaignController::class, 'makeDonation']);
    Route::get('/search', [CampaignController::class, 'searchCampaign']);
});