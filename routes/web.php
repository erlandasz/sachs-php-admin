<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['web', 'auth'])
    ->get('/private-pdf/badges/{filename}', function ($filename) {
        $path = 'pdf/badges/'.$filename;

        return response()->file(Storage::disk('private')->path($path));
    })
    ->name('private.pdf.badge');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
