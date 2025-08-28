<?php

use App\Http\Controllers\CronController;
use App\Services\AirtableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/trigger', function (Request $request) {
    return 'hello-world';
});

Route::prefix('cron')->group(function () {
    Route::get('/event-attendees', [CronController::class, 'attendees']);
});

Route::prefix('webhook')->group(function () {
    Route::put('airtable', [AirtableService::class, 'airtableWebhook']);
});
