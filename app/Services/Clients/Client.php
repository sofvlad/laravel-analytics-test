<?php

declare(strict_types=1);

namespace App\Services\Clients;

use App\Enums\HttpMethod;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Promises\LazyPromise;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class Client
{
    /**
     * Массив задержек перед повтором при слудующей попытки (мс)
     */
    private const array DELAY_MS = [500, 1000, 2000];

    protected LoggerInterface $logger;

    public function __construct(string $loggerChannelName)
    {
        $this->logger = Log::channel($loggerChannelName);
    }


    /**
     * Возвращает базовый URL для API клиента.
     *
     * @return string
     */
    abstract protected function getBaseURL(): string;

    /**
     * Выполняет запрос с повторными попытками и экспоненциальной задержкой.
     *
     * @param HttpMethod $method
     * @param string $url
     * @param array $options
     * @return Response
     * @throws Throwable
     */
    protected function sendRequest(HttpMethod $method, string $url, array $options = []): Response
    {
        $attempt = 0;
        $pendingRequest = Http::baseUrl($this->getBaseURL());

        if ($method === HttpMethod::GET || $method === HttpMethod::HEAD) {
            $pendingRequest = $pendingRequest->withQueryParameters($options);
        }

        $pendingRequest = $pendingRequest
            ->beforeSending(function () use (&$attempt, $method, $url, $options) {
                $attempt++;
                $this->logger->debug('Request sent', [
                    'attempt' => $attempt,
                    'method'  => $method->value,
                    'url'     => $url,
                    'options' => $options,
                ]);
            })
            ->afterResponse(function (Response $response) use (&$attempt) {
                $this->logger->debug('Response received', [
                    'status' => $response->status(),
                    'headers' => $response->getHeaders(),
                    'body' => $response->body(),
                ]);
            })
            ->retry(
                self::DELAY_MS,
                function (Throwable $e) {
                    $this->logger->warning('Request attempt failed', [
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

            return $this->handleResponse($response);
        } catch (Throwable $e) {
            $this->throwClientException($e);
        }
    }

    /**
     * @param Response|LazyPromise $response
     * @return Response|LazyPromise
     */
    protected function handleResponse(Response|LazyPromise $response): Response|LazyPromise
    {
        return $response;
    }

    /**
     * @param Throwable $e
     * @return void
     * @throws Throwable
     */
    protected function throwClientException(Throwable $e): void
    {
        throw $e;
    }
}
