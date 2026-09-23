<?php

use App\Http\Controllers\Api\V1\StoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['throttle:600,1'])->group(function() {
    Route::resource('stories', StoryController::class);
});
