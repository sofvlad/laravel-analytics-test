<?php

namespace App\Http\Controllers;

use App\Repositories\VisitRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function __construct(
        private readonly VisitRepositoryInterface $repository,
    ) {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function hourlyStats(Request $request): JsonResponse
    {
        $days = $request->input('days', 7);

        $stats = $this->repository->getHourlyStats($days);

        return response()->json([
            'labels' => $stats->pluck('hour'),
            'data' => $stats->pluck('unique_visits'),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cityStats(Request $request): JsonResponse
    {
        $days = $request->input('days', 7);

        $stats = $this->repository->getCityStats($days);

        return response()->json([
            'labels' => $stats->pluck('city'),
            'data' => $stats->pluck('unique_visits'),
        ]);
    }
}

