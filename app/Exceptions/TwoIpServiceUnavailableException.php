<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

final class TwoIpServiceUnavailableException extends TwoIpClientException
{
    private const string MESSAGE = 'Сервис временно недоступен. Пожалуйста, попробуйте позже.';

    public function __construct(string $message = self::MESSAGE, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
