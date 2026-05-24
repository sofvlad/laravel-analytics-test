<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVisitTrackJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitController extends Controller
{
    public function track(Request $request): JsonResponse
    {
        ProcessVisitTrackJob::dispatch(
            ip: config('services.2ip.test_ip') ?? $request->ip(),
            userAgent: $request->userAgent(),
            latitude: $request->post('lat'),
            longitude: $request->post('lon'),
            visitedAt: Carbon::now()
        )->onQueue('visit-track');

        return response()->json([
            'message' => 'Visit tracking scheduled',
        ], 202);
    }
}

