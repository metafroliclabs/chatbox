<?php

namespace Metafroliclabs\LaravelChat\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ChatException extends HttpException
{
    public function __construct(string $message = "Chat error", int $statusCode = 400)
    {
        parent::__construct($statusCode, $message);
    }
}