<?php

use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::prefix('visits')->group(function () {
        Route::get('/hourly-stats', [VisitController::class, 'hourlyStats'])->name('visits.hourly-stats');
        Route::get('/city-stats', [VisitController::class, 'cityStats'])->name('visits.city-stats');
    });
});

require __DIR__.'/settings.php';
