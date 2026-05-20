<?php

declare(strict_types=1);

namespace App\Exceptions\TwoIp;

use RuntimeException;
use Throwable;

class TwoIpClientException extends RuntimeException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
