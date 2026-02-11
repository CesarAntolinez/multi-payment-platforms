<?php

namespace App\Services\PaymentGateways;

use Stripe\StripeClient;
use Exception;

class StripeGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'stripe';
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Crear un cliente en Stripe
     */
    public function createCustomer(array $customerData): array
    {
        try {
            $this->validateRequiredFields($customerData, ['email', 'name']);

            $customer = $this->stripe->customers->create([
                'email' => $customerData['email'],
                'name' => $customerData['name'],
                'metadata' => $customerData['metadata'] ?? [],
            ]);

            return [
                'success' => true,
                'gateway_customer_id' => $customer->id,
                'data' => $customer->toArray(),
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
     * Actualizar un cliente en Stripe
     */
    public function updateCustomer(string $gatewayCustomerId, array $customerData): array
    {
        try {
            $updateData = [];

            if (isset($customerData['email'])) {
                $updateData['email'] = $customerData['email'];
            }

            if (isset($customerData['name'])) {
                $updateData['name'] = $customerData['name'];
            }

            if (isset($customerData['metadata'])) {
                $updateData['metadata'] = $customerData['metadata'];
            }

            $customer = $this->stripe->customers->update($gatewayCustomerId, $updateData);

            return [
                'success' => true,
                'data' => $customer->toArray(),
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
            $paymentMethod = $this->stripe->paymentMethods->attach(
                $tokenOrCardData,
                ['customer' => $gatewayCustomerId]
            );

            // Establecer como método de pago predeterminado
            $this->stripe->customers->update($gatewayCustomerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod->id,
                ],
            ]);

            return [
                'success' => true,
                'gateway_card_id' => $paymentMethod->id,
                'brand' => $paymentMethod->card->brand,
                'last4' => $paymentMethod->card->last4,
                'exp_month' => $paymentMethod->card->exp_month,
                'exp_year' => $paymentMethod->card->exp_year,
                'data' => $paymentMethod->toArray(),
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

            // Primero crear el producto
            $product = $this->stripe->products->create([
                'name' => $planData['name'],
                'metadata' => $planData['metadata'] ?? [],
            ]);

            // Luego crear el precio (plan)
            $price = $this->stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => $planData['amount'] * 100, // Convertir a centavos
                'currency' => $planData['currency'],
                'recurring' => [
                    'interval' => $planData['interval'],
                    'interval_count' => $planData['interval_count'] ?? 1,
                ],
            ]);

            return [
                'success' => true,
                'gateway_plan_id' => $price->id,
                'data' => [
                    'price' => $price->toArray(),
                    'product' => $product->toArray(),
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
            // En Stripe, los precios son inmutables, pero podemos actualizar el producto
            $price = $this->stripe->prices->retrieve($gatewayPlanId);

            if (isset($planData['name']) || isset($planData['metadata'])) {
                $updateData = [];

                if (isset($planData['name'])) {
                    $updateData['name'] = $planData['name'];
                }

                if (isset($planData['metadata'])) {
                    $updateData['metadata'] = $planData['metadata'];
                }

                $this->stripe->products->update($price->product, $updateData);
            }

            // Actualizar el estado activo del precio
            if (isset($planData['active'])) {
                $this->stripe->prices->update($gatewayPlanId, [
                    'active' => $planData['active'],
                ]);
            }

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

            $subscription = $this->stripe->subscriptions->create([
                'customer' => $subscriptionData['customer_id'],
                'items' => [
                    ['price' => $subscriptionData['price_id']],
                ],
                'metadata' => $subscriptionData['metadata'] ?? [],
            ]);

            return [
                'success' => true,
                'gateway_subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start,
                'current_period_end' => $subscription->current_period_end,
                'data' => $subscription->toArray(),
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
            $updateData = [];

            if (isset($subscriptionData['cancel'])) {
                $subscription = $this->stripe->subscriptions->cancel($gatewaySubscriptionId);
            } else {
                if (isset($subscriptionData['price_id'])) {
                    $subscription = $this->stripe->subscriptions->retrieve($gatewaySubscriptionId);
                    $updateData['items'] = [
                        [
                            'id' => $subscription->items->data[0]->id,
                            'price' => $subscriptionData['price_id'],
                        ],
                    ];
                }

                if (isset($subscriptionData['metadata'])) {
                    $updateData['metadata'] = $subscriptionData['metadata'];
                }

                $subscription = $this->stripe->subscriptions->update($gatewaySubscriptionId, $updateData);
            }

            return [
                'success' => true,
                'status' => $subscription->status,
                'data' => $subscription->toArray(),
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
     * Crear un link de pago
     */
    public function createPaymentLink(array $linkData): array
    {
        try {
            $this->validateRequiredFields($linkData, ['amount', 'currency', 'description']);

            // Crear un precio único para el link
            $price = $this->stripe->prices->create([
                'unit_amount' => $linkData['amount'] * 100,
                'currency' => $linkData['currency'],
                'product_data' => [
                    'name' => $linkData['description'],
                ],
            ]);

            // Crear el payment link
            $paymentLink = $this->stripe->paymentLinks->create([
                'line_items' => [
                    [
                        'price' => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'metadata' => $linkData['metadata'] ?? [],
            ]);

            return [
                'success' => true,
                'gateway_link_id' => $paymentLink->id,
                'url' => $paymentLink->url,
                'data' => $paymentLink->toArray(),
            ];
        } catch (Exception $e) {
            $this->logError($e, 'createPaymentLink');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
