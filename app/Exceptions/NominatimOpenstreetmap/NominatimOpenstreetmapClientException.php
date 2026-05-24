<?php

declare(strict_types=1);

namespace App\Exceptions\NominatimOpenstreetmap;

use RuntimeException;
use Throwable;

class NominatimOpenstreetmapClientException extends RuntimeException
{
    /**
     * @inheritDoc
     */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
