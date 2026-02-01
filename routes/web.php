<?php

use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\RewardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API Routes for Location Tracking (requires authentication)
Route::middleware(['auth:web'])->prefix('api')->group(function () {
    Route::post('/tracking/log', [TrackingController::class, 'logLocation'])->name('api.tracking.log');
    Route::post('/tracking/batch', [TrackingController::class, 'batchUpload'])->name('api.tracking.batch');
    
    // Reward redemption
    Route::get('/rewards', [RewardController::class, 'index'])->name('api.rewards.index');
    Route::post('/rewards/{reward}/redeem', [RewardController::class, 'redeem'])->name('api.rewards.redeem');
});
