<?php

namespace App\Exceptions\PaymentGateway;

use Exception;

class PaymentGatewayException extends Exception
{
    protected string $gateway;

    public function __construct(string $message, string $gateway, int $code = 0, Exception $previous = null)
    {
        $this->gateway = $gateway;
        parent::__construct($message, $code, $previous);
    }

    public function getGateway(): string
    {
        return $this->gateway;
    }

    public function context(): array
    {
        return [
            'gateway' => $this->gateway,
            'message' => $this->getMessage(),
        ];
    }
}
