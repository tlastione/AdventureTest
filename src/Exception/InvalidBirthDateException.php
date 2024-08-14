<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidBirthDateException extends HttpException
{
    public function __construct()
    {
        $message = "Birth date cannot be later than the travel start date.";
        parent::__construct(statusCode: 422, message: $message);
    }
}
