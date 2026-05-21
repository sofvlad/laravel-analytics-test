<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Visit;
use App\Repositories\VisitRepositoryInterface;
use App\Services\Clients\TwoIpClient;
use Illuminate\Support\Carbon;
use Throwable;

readonly class TwoIpService
{
    public function __construct(
        private TwoIpClient $client,
        private VisitRepositoryInterface $repository,
    ) {
    }

    /**
     * @param string $ip
     * @param string $userAgent
     * @param Carbon $visitedAt
     * @return Visit
     * @throws Throwable
     */
    public function store(string $ip, string $userAgent, Carbon $visitedAt): Visit
    {
        $geoData = $this->client->getGeoData($ip);

        return $this->repository->save([
            'ip' => $ip,
            'city' => $geoData['city'] ?? null,
            'country' => $geoData['country'] ?? null,
            'user_agent' => $userAgent,
            'visited_at' => $visitedAt,
        ]);
    }
}
