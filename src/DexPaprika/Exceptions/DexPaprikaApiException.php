<?php

namespace DexPaprika\Exceptions;

use Exception;

class DexPaprikaApiException extends Exception
{
    /**
     * @var array<string, mixed>|null Additional error data
     */
    private ?array $errorData = null;

    /**
     * Set additional error data
     *
     * @param array<string, mixed> $data The error data
     * @return self
     */
    public function setErrorData(array $data): self
    {
        $this->errorData = $data;
        return $this;
    }

    /**
     * Get the additional error data
     *
     * @return array<string, mixed>|null
     */
    public function getErrorData(): ?array
    {
        return $this->errorData;
    }
} 