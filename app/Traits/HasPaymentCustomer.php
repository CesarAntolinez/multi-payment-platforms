<?php

namespace App\Traits;

use App\Models\PaymentCustomer;
use App\Services\CustomerService;

trait HasPaymentCustomer
{
    /**
     * Relación con clientes de pago
     */
    public function paymentCustomers()
    {
        return $this->hasMany(PaymentCustomer::class, 'user_id');
    }

    /**
     * Obtener cliente de una pasarela específica
     */
    public function getPaymentCustomer(string $gateway): ?PaymentCustomer
    {
        return $this->paymentCustomers()->where('gateway', $gateway)->first();
    }

    /**
     * Verificar si tiene cliente en una pasarela
     */
    public function hasPaymentCustomer(string $gateway): bool
    {
        return $this->paymentCustomers()->where('gateway', $gateway)->exists();
    }

    /**
     * Obtener o crear cliente en una pasarela
     */
    public function getOrCreatePaymentCustomer(string $gateway): PaymentCustomer
    {
        $customerService = app(CustomerService::class);
        return $customerService->getOrCreateCustomer($this, $gateway);
    }

    /**
     * Obtener suscripciones activas
     */
    public function activeSubscriptions()
    {
        return $this->paymentCustomers()
            ->with(['subscriptions' => function ($query) {
                $query->active();
            }]);
    }
}
