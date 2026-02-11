<?php

namespace App\Services;

use App\Models\PaymentCustomer;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Crear un cliente en una pasarela de pago
     */
    public function createCustomer(User $user, string $gateway, array $additionalData = []): PaymentCustomer
    {
        // Verificar si el usuario ya tiene un cliente en esta pasarela
        $existingCustomer = PaymentCustomer::where('user_id', $user->id)
            ->where('gateway', $gateway)
            ->first();

        if ($existingCustomer) {
            throw new Exception("El usuario ya tiene un cliente en la pasarela {$gateway}");
        }

        return DB::transaction(function () use ($user, $gateway, $additionalData) {
            // Preparar datos del cliente
            $customerData = [
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => array_merge([
                    'user_id' => $user->id,
                ], $additionalData['metadata'] ?? []),
            ];

            // Crear cliente en la pasarela
            $result = $this->gatewayManager->gateway($gateway)->createCustomer($customerData);

            if (!$result['success']) {
                throw new Exception("Error al crear cliente: " . $result['error']);
            }

            // Guardar en la base de datos
            return PaymentCustomer::create([
                'user_id' => $user->id,
                'gateway' => $gateway,
                'gateway_customer_id' => $result['gateway_customer_id'],
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => $customerData['metadata'],
            ]);
        });
    }

    /**
     * Actualizar un cliente
     */
    public function updateCustomer(PaymentCustomer $customer, array $updateData): PaymentCustomer
    {
        return DB::transaction(function () use ($customer, $updateData) {
            // Actualizar en la pasarela
            $result = $this->gatewayManager
                ->gateway($customer->gateway)
                ->updateCustomer($customer->gateway_customer_id, $updateData);

            if (!$result['success']) {
                throw new Exception("Error al actualizar cliente: " . $result['error']);
            }

            // Actualizar en la base de datos
            $customer->update([
                'email' => $updateData['email'] ?? $customer->email,
                'name' => $updateData['name'] ?? $customer->name,
                'metadata' => array_merge($customer->metadata ?? [], $updateData['metadata'] ?? []),
            ]);

            return $customer->fresh();
        });
    }

    /**
     * Obtener cliente por usuario y pasarela
     */
    public function getCustomer(User $user, string $gateway): ?PaymentCustomer
    {
        return PaymentCustomer::where('user_id', $user->id)
            ->where('gateway', $gateway)
            ->first();
    }

    /**
     * Obtener o crear un cliente
     */
    public function getOrCreateCustomer(User $user, string $gateway): PaymentCustomer
    {
        $customer = $this->getCustomer($user, $gateway);

        if (!$customer) {
            $customer = $this->createCustomer($user, $gateway);
        }

        return $customer;
    }
}
