<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NominatimOpenstreetmapService;
use App\Services\TwoIpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class VisitController extends Controller
{
    /**
     * @param Request $request
     * @param NominatimOpenstreetmapService $nominatimOpenstreetmapService
     * @param TwoIpService $twoIpService
     * @return JsonResponse
     * @throws Throwable
     */
    public function track(
        Request $request,
        NominatimOpenstreetmapService $nominatimOpenstreetmapService,
        TwoIpService $twoIpService
    ): JsonResponse {
        $lat = $request->post('lat');
        $lon = $request->post('lon');
        $visit = !empty($lat) && !empty($lon)
            ? $nominatimOpenstreetmapService->store($request->ip(), $request->userAgent(), $lat, $lon)
            : $twoIpService->store($request);

        return response()->json([
            'message' => !empty($visit) ? 'Visit recorded' : 'Visit is not recorded',
            'visit' => $visit,
        ], 201);
    }
}

