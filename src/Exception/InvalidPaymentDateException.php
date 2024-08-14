<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidPaymentDateException extends HttpException
{
    public function __construct()
    {
        $message = "Payment date cannot be later than the travel start date.";
        parent::__construct(statusCode: 422, message: $message);
    }
}
