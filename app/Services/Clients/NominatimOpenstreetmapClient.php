<?php

declare(strict_types=1);

namespace App\Services\Clients;

use App\Enums\HttpMethod;
use App\Exceptions\NominatimOpenstreetmap\NominatimOpenstreetmapClientException;
use App\Exceptions\NominatimOpenstreetmap\NominatimOpenstreetmapRequestFailedException;
use App\Exceptions\NominatimOpenstreetmap\NominatimOpenstreetmapServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Promises\LazyPromise;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Throwable;

class NominatimOpenstreetmapClient extends Client
{
    private const string BASE_URL = 'https://nominatim.openstreetmap.org';

    private const string LOGGER_CHANNEL_NAME = 'nominatim-client';

    public function __construct()
    {
        parent::__construct(static::LOGGER_CHANNEL_NAME);
    }

    /**
     * @inheritDoc
     */
    protected function getBaseURL(): string
    {
        return static::BASE_URL;
    }

    /**
     * @throws NominatimOpenstreetmapClientException
     * @throws Throwable
     */
    public function getReverse(float $latitude, float $longitude, bool $addressDetails = false): array
    {
        return $this->sendRequest(
            HttpMethod::GET,
            '/reverse',
            [
                'lat' => $latitude,
                'lon' => $longitude,
                'addressdetails' => (int)$addressDetails,
                'format' => 'json',
                'accept-language' => 'en',
            ]
        )->json();
    }

    /**
     * @inheritDoc
     */
    protected function handleResponse(LazyPromise|Response $response): Response|LazyPromise
    {
        if (!empty($response['error'])) {
            throw new NominatimOpenstreetmapClientException($response['error']);
        }

        return parent::handleResponse($response);
    }

    /**
     * @inheritDoc
     */
    protected function throwClientException(Throwable $e): void
    {
        if ($e instanceof ConnectionException) {
            throw new NominatimOpenstreetmapServiceUnavailableException(previous: $e);
        }

        if ($e instanceof RequestException) {
            throw new NominatimOpenstreetmapRequestFailedException(previous: $e);
        }

        parent::throwClientException($e);
    }
}
