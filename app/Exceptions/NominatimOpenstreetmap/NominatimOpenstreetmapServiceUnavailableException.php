<?php

declare(strict_types=1);

namespace App\Exceptions\NominatimOpenstreetmap;

use Throwable;

final class NominatimOpenstreetmapServiceUnavailableException extends NominatimOpenstreetmapClientException
{
    private const string MESSAGE = 'Сервис временно недоступен. Пожалуйста, попробуйте позже.';

    public function __construct(string $message = self::MESSAGE, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
