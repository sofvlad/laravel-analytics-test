<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TwoIpClientException;
use App\Models\Visit;
use App\Repositories\VisitRepositoryInterface;
use App\Services\Clients\TwoIpClient;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @return Visit|null
     * @throws Throwable
     */
    public function store(Request $request): ?Visit
    {
        try {
            $geoData = $this->client->getGeoData($request->ip());
        } catch (TwoIpClientException) {
            return null;
        }

        return $this->repository->save([
            'ip_address' => $request->ip(),
            'city' => $geoData['city'],
            'country' => $geoData['country'],
            'user_agent' => $request->userAgent(),
            'visited_at' => Carbon::now(),
        ]);
    }
}
