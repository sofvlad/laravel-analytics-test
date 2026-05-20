<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TwoIpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class VisitController extends Controller
{
    /**
     * @param Request $request
     * @param TwoIpService $twoIpService
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request, TwoIpService $twoIpService): JsonResponse
    {
        $visit = $twoIpService->store($request);

        return response()->json([
            'message' => !empty($visit) ? 'Visit recorded' : 'Visit is not recorded',
            'visit' => $visit,
        ], 201);
    }
}

