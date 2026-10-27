<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\StoryController;
use App\Telegram\Http\Controllers\TelegramWebhookController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['throttle:600,1'])->group(function() {
    Route::resource('stories', StoryController::class);
    Route::post('telegram/webhook/{bot}', TelegramWebhookController::class)
        ->withoutMiddleware([PreventRequestForgery::class]);

    Route::prefix('analytics')->middleware(['api.token'])->group(function() {
        Route::get('users', [AnalyticsController::class, 'users']);
        Route::get('stories', [AnalyticsController::class, 'stories']);
    });
});




