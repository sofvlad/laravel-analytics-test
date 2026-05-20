<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TwoIp\TwoIpClientException;
use App\Models\Visit;
use App\Repositories\VisitRepositoryInterface;
use App\Services\Clients\NominatimOpenstreetmapClient;
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
     * @return Visit|null
     * @throws Throwable
     */
    public function store(string $ip, string $userAgent, float $lat, float $lon): ?Visit
    {
        try {
            $geoData = $this->client->getReverse($lat, $lon, true);
        } catch (TwoIpClientException) {
            return null;
        }
        $address = $geoData['address'];

        return $this->repository->save([
            'ip' => $ip,
            'city' => $address['city'] ?? $address['town'] ?? null,
            'country' => $geoData['country'] ?: null,
            'user_agent' => $userAgent,
            'visited_at' => Carbon::now(),
        ]);
    }
}
