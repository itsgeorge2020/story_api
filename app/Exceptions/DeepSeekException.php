<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class DeepSeekException extends Exception
{
    private const MESSAGE = 'Deepseek API error';

    public function __construct(string $message = self::MESSAGE, int $code = ResponseAlias::HTTP_BAD_REQUEST, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
