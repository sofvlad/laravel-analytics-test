<?php

namespace App\Repositories;

use App\Models\Visit;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitRepository implements VisitRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function save(array $data): Visit
    {
        return Visit::create($data);
    }

    /**
     * @inheritDoc
     */
    public function getHourlyStats(int $days): Collection
    {
        return Visit::selectRaw("strftime('%Y-%m-%d %H:00', visited_at) as hour, COUNT(DISTINCT ip_address) as unique_visits")
            ->where('visited_at', '>=', Carbon::now()->subDays($days))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getCityStats(int $days): Collection
    {
        return Visit::selectRaw('city, COUNT(DISTINCT ip_address) as unique_visits')
            ->where('visited_at', '>=', Carbon::now()->subDays($days))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('unique_visits')
            ->get();
    }
}
