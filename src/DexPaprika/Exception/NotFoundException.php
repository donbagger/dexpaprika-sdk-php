<?php

namespace DexPaprika\Exception;

use DexPaprika\Exceptions\DexPaprikaApiException;

/**
 * Exception thrown when a resource is not found
 */
class NotFoundException extends DexPaprikaApiException
{
    /**
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = 'Resource not found', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
