<?php

declare(strict_types=1);

namespace App\Services\Clients;

use App\Enums\HttpMethod;
use App\Exceptions\NominatimOpenstreetmap\NominatimOpenstreetmapClientException;
use App\Exceptions\NominatimOpenstreetmap\NominatimOpenstreetmapRequestFailedException;
use App\Exceptions\NominatimOpenstreetmap\NominatimOpenstreetmapServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NominatimOpenstreetmapClient
{
    private const string BASE_URL = 'https://nominatim.openstreetmap.org';

    /**
     * Массив задержек перед повтором при слудующей попытки (мс)
     */
    private const array DELAY_MS = [500, 1000, 2000];

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
     * Выполняет запрос с повторными попытками и экспоненциальной задержкой.
     *
     * @param HttpMethod $method
     * @param string $url
     * @param array $options
     * @return Response
     * @throws Throwable
     */
    private function sendRequest(HttpMethod $method, string $url, array $options = []): Response
    {
        $attempt = 0;
        $pendingRequest = Http::baseUrl(self::BASE_URL);

        if ($method === HttpMethod::GET || $method === HttpMethod::HEAD) {
            $pendingRequest = $pendingRequest->withQueryParameters($options);
        }

        $pendingRequest = $pendingRequest
            ->beforeSending(function () use (&$attempt, $method, $url, $options) {
                $attempt++;
                Log::channel('nominatim-client')->debug('NominatimOpenstreetmapClient: request sent', [
                    'attempt' => $attempt,
                    'method'  => $method->value,
                    'url'     => $url,
                    'options' => $options,
                ]);
            })
            ->afterResponse(function (Response $response) use (&$attempt) {
                Log::channel('nominatim-client')->debug('NominatimOpenstreetmapClient: response received', [
                    'status' => $response->status(),
                    'headers' => $response->getHeaders(),
                    'body' => $response->body(),
                ]);
            })
            ->retry(
                self::DELAY_MS,
                function (Throwable $e) {
                    Log::channel('nominatim-client')->warning('NominatimOpenstreetmapClient: request attempt failed', [
                        'error' => $e->getMessage(),
                    ]);

                    return $e instanceof ConnectionException;
                }
            );

        try {
            $response = $pendingRequest->send(
                $method->value,
                $url,
                $method === HttpMethod::GET || $method === HttpMethod::HEAD ? [] : $options
            );

            if (!empty($response['error'])) {
                throw new NominatimOpenstreetmapClientException($response['error']);
            }

            return $response;
        } catch (Throwable $e) {
            if ($e instanceof ConnectionException) {
                throw new NominatimOpenstreetmapServiceUnavailableException(previous: $e);
            }

            if ($e instanceof RequestException) {
                throw new NominatimOpenstreetmapRequestFailedException(previous: $e);
            }

            throw $e;
        }
    }
}
