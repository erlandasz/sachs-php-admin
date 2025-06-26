<?php

use App\Http\Controllers\CronController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/trigger', function (Request $request) {
    return 'hello-world';
});

Route::prefix('cron')->group(function () {
    Route::get('/event-attendees', [CronController::class, 'attendees']);
});
