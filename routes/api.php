<?php

use App\Http\Controllers\AttendeeCronController;
use App\Http\Controllers\SpeakerCronController;
use App\Services\AirtableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/trigger', function (Request $request) {
    return 'hello-world';
});

Route::prefix('cron')->group(function () {
    Route::get('/event-attendees', [AttendeeCronController::class, 'attendees']);
    Route::get('/speakers', [SpeakerCronController::class, 'index']);
});

Route::prefix('webhook')->group(function () {
    Route::put('airtable', [AirtableService::class, 'airtableWebhook']);
});
