<?php

namespace Ua0leg\Novaposhta\Exceptions;

class NovaPoshtaException extends \RuntimeException
{
    private ?string $apiErrorCode;

    public function __construct(
        string     $message,
        int        $code = 0,
        ?string    $apiErrorCode = null,
        \Throwable $previous = null
    )
    {
        $this->apiErrorCode = $apiErrorCode;
        parent::__construct($message, $code, $previous);
    }

    public function getApiErrorCode(): ?string
    {
        return $this->apiErrorCode;
    }
}
