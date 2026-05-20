<?php

declare(strict_types=1);

namespace App\Services\Clients;

use App\Enums\HttpMethod;
use App\Exceptions\TwoIpClientException;
use App\Exceptions\TwoIpRequestFailedException;
use App\Exceptions\TwoIpServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TwoIpClient
{
    private const string BASE_URL = 'https://api.2ip.io';

    /**
     * Массив задержек перед повтором при слудующей попытки (мс)
     */
    private const array DELAY_MS = [500, 1000, 2000];

    public function __construct(
        private readonly string $token,
    ) {
    }

    /**
     * @throws TwoIpClientException
     * @throws Throwable
     */
    public function getGeoData(?string $ip = null): array
    {
        return $this->sendRequest(
            HttpMethod::GET,
            '/' . $ip,
            [
                'token' => $this->token,
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
        $pendingRequest = Http::baseUrl(self::BASE_URL)
            ->beforeSending(function () use (&$attempt, $method, $url, $options) {
                $attempt++;
                Log::channel('2ip-client')->debug('TwoIpClient: request sent', [
                    'attempt' => $attempt,
                    'method'  => $method->value,
                    'url'     => $url,
                    'options' => $options,
                ]);
            })
            ->afterResponse(function (Response $response) use (&$attempt) {
                Log::channel('2ip-client')->debug('TwoIpClient: response received', [
                    'status' => $response->status(),
                    'headers' => $response->getHeaders(),
                    'body' => $response->body(),
                ]);
            })
            ->retry(
                self::DELAY_MS,
                function (Throwable $e) {
                    Log::channel('2ip-client')->warning('TwoIpClient: request attempt failed', [
                        'error' => $e->getMessage(),
                    ]);

                    return $e instanceof ConnectionException;
                }
            );

        try {
            $response = $pendingRequest->send($method->value, $url, $options);
            if (!empty($response['error'])) {
                throw new TwoIpClientException($response['error']);
            }

            return $response;
        } catch (Throwable $e) {
            if ($e instanceof ConnectionException) {
                throw new TwoIpServiceUnavailableException(previous: $e);
            }

            if ($e instanceof RequestException) {
                throw new TwoIpRequestFailedException(previous: $e);
            }

            throw $e;
        }
    }
}
