<?php

namespace App\Services;

use App\Models\PaymentCustomer;
use App\Models\PaymentPlan;
use App\Models\PaymentSubscription;
use Exception;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Crear una suscripción
     */
    public function createSubscription(
        PaymentCustomer $customer,
        PaymentPlan $plan
    ): PaymentSubscription {
        // Verificar que el cliente y el plan sean de la misma pasarela
        if ($customer->gateway !== $plan->gateway) {
            throw new Exception("El cliente y el plan deben ser de la misma pasarela");
        }

        return DB::transaction(function () use ($customer, $plan) {
            // Preparar datos de la suscripción
            $subscriptionData = [
                'customer_id' => $customer->gateway_customer_id,
                'price_id' => $plan->gateway_plan_id,
                'metadata' => [
                    'user_id' => $customer->user_id,
                    'plan_id' => $plan->id,
                ],
            ];

            // Crear suscripción en la pasarela
            $result = $this->gatewayManager
                ->gateway($customer->gateway)
                ->createSubscription($subscriptionData);

            if (!$result['success']) {
                throw new Exception("Error al crear suscripción: " . $result['error']);
            }

            // Guardar en la base de datos
            return PaymentSubscription::create([
                'payment_customer_id' => $customer->id,
                'payment_plan_id' => $plan->id,
                'gateway' => $customer->gateway,
                'gateway_subscription_id' => $result['gateway_subscription_id'],
                'status' => $result['status'],
                'current_period_start' => $result['current_period_start']
                    ? date('Y-m-d H:i:s', $result['current_period_start'])
                    : null,
                'current_period_end' => $result['current_period_end']
                    ? date('Y-m-d H:i:s', $result['current_period_end'])
                    : null,
            ]);
        });
    }

    /**
     * Actualizar una suscripción (cambiar de plan)
     */
    public function updateSubscription(
        PaymentSubscription $subscription,
        PaymentPlan $newPlan = null,
        bool $cancel = false
    ): PaymentSubscription {
        return DB::transaction(function () use ($subscription, $newPlan, $cancel) {
            $updateData = [];

            if ($cancel) {
                $updateData['cancel'] = true;
            } elseif ($newPlan) {
                // Verificar que el nuevo plan sea de la misma pasarela
                if ($newPlan->gateway !== $subscription->gateway) {
                    throw new Exception("El nuevo plan debe ser de la misma pasarela");
                }

                $updateData['price_id'] = $newPlan->gateway_plan_id;
            }

            // Actualizar en la pasarela
            $result = $this->gatewayManager
                ->gateway($subscription->gateway)
                ->updateSubscription($subscription->gateway_subscription_id, $updateData);

            if (!$result['success']) {
                throw new Exception("Error al actualizar suscripción: " . $result['error']);
            }

            // Actualizar en la base de datos
            $dataToUpdate = [
                'status' => $result['status'],
            ];

            if ($newPlan) {
                $dataToUpdate['payment_plan_id'] = $newPlan->id;
            }

            if ($cancel) {
                $dataToUpdate['canceled_at'] = now();
                $dataToUpdate['status'] = 'canceled';
            }

            $subscription->update($dataToUpdate);

            return $subscription->fresh();
        });
    }

    /**
     * Cancelar una suscripción
     */
    public function cancelSubscription(PaymentSubscription $subscription): PaymentSubscription
    {
        return $this->updateSubscription($subscription, null, true);
    }

    /**
     * Obtener suscripciones activas de un cliente
     */
    public function getActiveSubscriptions(PaymentCustomer $customer)
    {
        return PaymentSubscription::where('payment_customer_id', $customer->id)
            ->active()
            ->with('plan')
            ->get();
    }

    /**
     * Verificar si un cliente tiene una suscripción activa
     */
    public function hasActiveSubscription(PaymentCustomer $customer): bool
    {
        return PaymentSubscription::where('payment_customer_id', $customer->id)
            ->active()
            ->exists();
    }
}
