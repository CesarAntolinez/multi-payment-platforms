<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Services\PaymentGateways\StripeGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /**
     * Pasarelas registradas
     */
    protected array $gateways = [];

    /**
     * Constructor - Registrar las pasarelas disponibles
     */
    public function __construct()
    {
        $this->registerGateway('stripe', new StripeGateway());
        // Aquí puedes agregar más pasarelas:
        // $this->registerGateway('paypal', new PayPalGateway());
        // $this->registerGateway('mercadopago', new MercadoPagoGateway());
    }

    /**
     * Registrar una pasarela
     */
    public function registerGateway(string $name, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$name] = $gateway;
    }

    /**
     * Obtener una pasarela específica
     */
    public function gateway(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new InvalidArgumentException("La pasarela {$name} no está registrada");
        }

        return $this->gateways[$name];
    }

    /**
     * Obtener todas las pasarelas disponibles
     */
    public function getAvailableGateways(): array
    {
        return array_keys($this->gateways);
    }

    /**
     * Verificar si una pasarela está disponible
     */
    public function hasGateway(string $name): bool
    {
        return isset($this->gateways[$name]);
    }
}
