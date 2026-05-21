<?php

declare(strict_types=1);

namespace App\Services\Clients;

use App\Enums\HttpMethod;
use App\Exceptions\TwoIp\TwoIpClientException;
use App\Exceptions\TwoIp\TwoIpRequestFailedException;
use App\Exceptions\TwoIp\TwoIpServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Promises\LazyPromise;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Throwable;

class TwoIpClient extends Client
{
    private const string BASE_URL = 'https://api.2ip.io';

    private const string LOGGER_CHANNEL_NAME = '2ip-client';

    /**
     * @param string $token
     */
    public function __construct(
        private readonly string $token,
    ) {
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
     * @inheritDoc
     */
    protected function handleResponse(LazyPromise|Response $response): Response|LazyPromise
    {
        if (!empty($response['error'])) {
            throw new TwoIpClientException($response['error']);
        }

        return parent::handleResponse($response);
    }

    /**
     * @inheritDoc
     */
    protected function throwClientException(Throwable $e): void
    {
        if ($e instanceof ConnectionException) {
            throw new TwoIpServiceUnavailableException(previous: $e);
        }

        if ($e instanceof RequestException) {
            throw new TwoIpRequestFailedException(previous: $e);
        }

        parent::throwClientException($e);
    }
}
