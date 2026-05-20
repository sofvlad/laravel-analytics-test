<?php

use App\Http\Controllers\Api\VisitController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/visit', [VisitController::class, 'store'])->name('visits.store');
});
