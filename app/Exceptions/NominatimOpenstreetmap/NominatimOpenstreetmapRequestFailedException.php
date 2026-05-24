<?php

declare(strict_types=1);

namespace App\Exceptions\NominatimOpenstreetmap;

use Throwable;

final class NominatimOpenstreetmapRequestFailedException extends NominatimOpenstreetmapClientException
{
    private const string MESSAGE = 'Не удалось выполнить запрос к внешнему сервису. Пожалуйста, попробуйте позже.';

    /**
     * @inheritDoc
     */
    public function __construct(string $message = self::MESSAGE, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
