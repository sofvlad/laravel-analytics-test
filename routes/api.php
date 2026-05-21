<?php

use App\Http\Controllers\Api\VisitController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/visits/track', [VisitController::class, 'track'])->name('visits.track');
});
