<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Visit;
use App\Repositories\VisitRepositoryInterface;
use App\Services\Clients\NominatimOpenstreetmapClient;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Throwable;

readonly class NominatimOpenstreetmapService
{
    public function __construct(
        private NominatimOpenstreetmapClient $client,
        private VisitRepositoryInterface $repository,
    ) {
    }

    /**
     * @param string $ip
     * @param string $userAgent
     * @param float $lat
     * @param float $lon
     * @param Carbon $visitedAt
     * @return Visit
     * @throws Throwable
     */
    public function store(
        string $ip,
        string $userAgent,
        float $lat,
        float $lon,
        Carbon $visitedAt
    ): Visit {
        $geoData = $this->client->getReverse($lat, $lon, true);
        $address = $geoData['address'];

        return $this->repository->save([
            'ip' => $ip,
            'lat' => $lat,
            'lon' => $lon,
            'city' => $address['city'] ?? $address['town'] ?? null,
            'country' => $address['country'] ?: null,
            'user_agent' => $userAgent,
            'visited_at' => $visitedAt,
        ]);
    }
}
