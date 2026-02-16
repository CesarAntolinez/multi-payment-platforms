<?php

namespace App\Services\PaymentGateways;

use MercadoPago\SDK;
use MercadoPago\Customer;
use MercadoPago\Plan;
use MercadoPago\Subscription;
use MercadoPago\Preference;
use Exception;

class MercadoPagoGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'mercadopago';

    public function __construct()
    {
        SDK::setAccessToken(config('services.mercadopago.access_token'));
    }

    /**
     * Crear un cliente en Mercado Pago
     */
    public function createCustomer(array $customerData): array
    {
        try {
            $this->validateRequiredFields($customerData, ['email', 'name']);

            $customer = new Customer();
            $customer->email = $customerData['email'];
            
            // Separar nombre y apellido
            $nameParts = explode(' ', $customerData['name'], 2);
            $customer->first_name = $nameParts[0];
            $customer->last_name = $nameParts[1] ?? '';
            
            $customer->save();

            return [
                'success' => true,
                'gateway_customer_id' => $customer->id,
                'data' => [
                    'id' => $customer->id,
                    'email' => $customer->email,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                ],
            ];
        } catch (Exception $e) {
            $this->logError($e, 'createCustomer');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Actualizar un cliente en Mercado Pago
     */
    public function updateCustomer(string $gatewayCustomerId, array $customerData): array
    {
        try {
            $customer = Customer::find_by_id($gatewayCustomerId);

            if (isset($customerData['email'])) {
                $customer->email = $customerData['email'];
            }

            if (isset($customerData['name'])) {
                $nameParts = explode(' ', $customerData['name'], 2);
                $customer->first_name = $nameParts[0];
                $customer->last_name = $nameParts[1] ?? '';
            }

            $customer->update();

            return [
                'success' => true,
                'data' => [
                    'id' => $customer->id,
                    'email' => $customer->email,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                ],
            ];
        } catch (Exception $e) {
            $this->logError($e, 'updateCustomer');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Crear una tarjeta para un cliente
     */
    public function createCard(string $gatewayCustomerId, string $tokenOrCardData): array
    {
        try {
            $customer = Customer::find_by_id($gatewayCustomerId);
            
            // En Mercado Pago, las tarjetas se asocian mediante tokens
            $card = $customer->cards->create([
                'token' => $tokenOrCardData
            ]);

            return [
                'success' => true,
                'gateway_card_id' => $card->id,
                'brand' => $card->payment_method->id ?? 'unknown',
                'last4' => $card->last_four_digits ?? '0000',
                'exp_month' => $card->expiration_month ?? 12,
                'exp_year' => $card->expiration_year ?? date('Y') + 5,
                'data' => [
                    'id' => $card->id,
                    'payment_method' => $card->payment_method->id ?? null,
                    'last_four_digits' => $card->last_four_digits ?? null,
                ],
            ];
        } catch (Exception $e) {
            $this->logError($e, 'createCard');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Crear un plan de suscripción
     */
    public function createPlan(array $planData): array
    {
        try {
            $this->validateRequiredFields($planData, ['name', 'amount', 'currency', 'interval']);

            $plan = new Plan();
            $plan->description = $planData['name'];
            
            // Mercado Pago usa auto_recurring para planes de suscripción
            $plan->auto_recurring = [
                'frequency' => 1,
                'frequency_type' => $this->convertIntervalToMercadoPago($planData['interval']),
                'transaction_amount' => (float) $planData['amount'],
                'currency_id' => strtoupper($planData['currency']),
            ];

            $plan->back_url = config('app.url') . '/subscriptions';

            $plan->save();

            return [
                'success' => true,
                'gateway_plan_id' => $plan->id,
                'data' => [
                    'id' => $plan->id,
                    'description' => $plan->description,
                    'auto_recurring' => $plan->auto_recurring,
                ],
            ];
        } catch (Exception $e) {
            $this->logError($e, 'createPlan');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Actualizar un plan de suscripción
     */
    public function updatePlan(string $gatewayPlanId, array $planData): array
    {
        try {
            $plan = Plan::find_by_id($gatewayPlanId);

            if (isset($planData['name'])) {
                $plan->description = $planData['name'];
            }

            if (isset($planData['active'])) {
                $plan->status = $planData['active'] ? 'active' : 'inactive';
            }

            $plan->update();

            return [
                'success' => true,
                'message' => 'Plan actualizado correctamente',
            ];
        } catch (Exception $e) {
            $this->logError($e, 'updatePlan');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Crear una suscripción
     */
    public function createSubscription(array $subscriptionData): array
    {
        try {
            $this->validateRequiredFields($subscriptionData, ['customer_id', 'price_id']);

            $subscription = new Subscription();
            $subscription->payer_email = $this->getCustomerEmail($subscriptionData['customer_id']);
            $subscription->preapproval_plan_id = $subscriptionData['price_id'];
            $subscription->back_url = config('app.url') . '/subscriptions';
            
            $subscription->save();

            return [
                'success' => true,
                'gateway_subscription_id' => $subscription->id,
                'status' => $subscription->status ?? 'pending',
                'current_period_start' => time(),
                'current_period_end' => strtotime("+1 month"),
                'data' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status ?? 'pending',
                    'init_point' => $subscription->init_point ?? null,
                ],
            ];
        } catch (Exception $e) {
            $this->logError($e, 'createSubscription');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Actualizar una suscripción
     */
    public function updateSubscription(string $gatewaySubscriptionId, array $subscriptionData): array
    {
        try {
            $subscription = Subscription::find_by_id($gatewaySubscriptionId);

            if (isset($subscriptionData['cancel'])) {
                $subscription->status = 'cancelled';
                $subscription->update();

                return [
                    'success' => true,
                    'status' => 'canceled',
                ];
            }

            if (isset($subscriptionData['status'])) {
                $subscription->status = $subscriptionData['status'];
            }

            $subscription->update();

            return [
                'success' => true,
                'status' => $subscription->status,
                'data' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                ],
            ];
        } catch (Exception $e) {
            $this->logError($e, 'updateSubscription');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Crear un link de pago (Preference)
     */
    public function createPaymentLink(array $linkData): array
    {
        try {
            $this->validateRequiredFields($linkData, ['amount', 'currency', 'description']);

            $preference = new Preference();
            
            $item = new \MercadoPago\Item();
            $item->title = $linkData['description'];
            $item->quantity = 1;
            $item->unit_price = (float) $linkData['amount'];
            $item->currency_id = strtoupper($linkData['currency']);

            $preference->items = [$item];
            $preference->back_urls = [
                'success' => config('app.url') . '/payment/success',
                'failure' => config('app.url') . '/payment/cancel',
                'pending' => config('app.url') . '/payment/pending',
            ];
            $preference->auto_return = 'approved';

            $preference->save();

            return [
                'success' => true,
                'gateway_link_id' => $preference->id,
                'url' => $preference->init_point,
                'data' => [
                    'id' => $preference->id,
                    'init_point' => $preference->init_point,
                    'sandbox_init_point' => $preference->sandbox_init_point ?? null,
                ],
            ];
        } catch (Exception $e) {
            $this->logError($e, 'createPaymentLink');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Convertir intervalo a formato de Mercado Pago
     */
    protected function convertIntervalToMercadoPago(string $interval): string
    {
        $intervalMap = [
            'day' => 'days',
            'week' => 'days', // Mercado Pago no tiene week, usamos days
            'month' => 'months',
            'year' => 'years',
        ];

        return $intervalMap[$interval] ?? 'months';
    }

    /**
     * Obtener email del cliente
     */
    protected function getCustomerEmail(string $gatewayCustomerId): string
    {
        try {
            $customer = Customer::find_by_id($gatewayCustomerId);
            return $customer->email ?? '';
        } catch (Exception $e) {
            return '';
        }
    }
}
