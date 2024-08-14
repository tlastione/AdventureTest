<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidDateFormatException extends HttpException
{
    public function __construct(string $fieldName, string $dateString)
    {
        $message = "Invalid $fieldName format: $dateString";
        parent::__construct(statusCode: 422, message: $message);
    }
}
