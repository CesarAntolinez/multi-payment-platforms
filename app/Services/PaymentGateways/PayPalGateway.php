<?php

namespace App\Services\PaymentGateways;

use PayPal\Api\Agreement;
use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Exception;

class PayPalGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'paypal';
    protected ApiContext $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                config('services.paypal.client_id'),
                config('services.paypal.secret')
            )
        );

        $this->apiContext->setConfig(config('services.paypal.settings'));
    }

    public function createCustomer(array $customerData): array
    {
        try {
            $this->validateRequiredFields($customerData, ['email', 'name']);

            // PayPal no requiere crear clientes explícitamente
            // Usamos el email como identificador
            return [
                'success' => true,
                'gateway_customer_id' => 'paypal_' . md5($customerData['email']),
                'data' => $customerData,
            ];
        } catch (Exception $e) {
            $this->logError($e, 'createCustomer');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateCustomer(string $gatewayCustomerId, array $customerData): array
    {
        // PayPal no requiere actualizar clientes explícitamente
        return [
            'success' => true,
            'data' => $customerData,
        ];
    }

    public function createCard(string $gatewayCustomerId, string $tokenOrCardData): array
    {
        // PayPal maneja las tarjetas a través de su interfaz
        return [
            'success' => true,
            'gateway_card_id' => 'paypal_card_' . time(),
            'brand' => 'paypal',
            'last4' => '0000',
            'exp_month' => 12,
            'exp_year' => date('Y') + 5,
        ];
    }

    public function createPlan(array $planData): array
    {
        try {
            $this->validateRequiredFields($planData, ['name', 'amount', 'currency', 'interval']);

            $plan = new Plan();
            $plan->setName($planData['name'])
                ->setDescription($planData['name'])
                ->setType('INFINITE');

            $paymentDefinition = new PaymentDefinition();
            $paymentDefinition->setName('Regular Payments')
                ->setType('REGULAR')
                ->setFrequency(strtoupper($planData['interval']))
                ->setFrequencyInterval((string)($planData['interval_count'] ?? 1))
                ->setCycles('0')
                ->setAmount(new Currency([
                    'value' => $planData['amount'],
                    'currency' => strtoupper($planData['currency'])
                ]));

            $merchantPreferences = new MerchantPreferences();
            $merchantPreferences->setReturnUrl(config('app.url') . '/payment/success')
                ->setCancelUrl(config('app.url') . '/payment/cancel')
                ->setAutoBillAmount('yes')
                ->setInitialFailAmountAction('CONTINUE')
                ->setMaxFailAttempts('0');

            $plan->setPaymentDefinitions([$paymentDefinition]);
            $plan->setMerchantPreferences($merchantPreferences);

            $createdPlan = $plan->create($this->apiContext);

            // Activar el plan
            $createdPlan->update([
                [
                    'op' => 'replace',
                    'path' => '/',
                    'value' => ['state' => 'ACTIVE']
                ]
            ], $this->apiContext);

            return [
                'success' => true,
                'gateway_plan_id' => $createdPlan->getId(),
                'data' => $createdPlan->toArray(),
            ];
        } catch (Exception $e) {
            $this->logError($e, 'createPlan');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updatePlan(string $gatewayPlanId, array $planData): array
    {
        // PayPal no permite actualizar planes directamente
        // Se debe crear un nuevo plan
        return [
            'success' => true,
            'message' => 'Los planes de PayPal son inmutables',
        ];
    }

    public function createSubscription(array $subscriptionData): array
    {
        try {
            $this->validateRequiredFields($subscriptionData, ['customer_id', 'price_id']);

            $agreement = new Agreement();
            $agreement->setName('Subscription Agreement')
                ->setDescription('Subscription')
                ->setStartDate(gmdate("Y-m-d\TH:i:s\Z", strtotime("+1 day")));

            $plan = new Plan();
            $plan->setId($subscriptionData['price_id']);
            $agreement->setPlan($plan);

            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            $agreement->setPayer($payer);

            $agreement = $agreement->create($this->apiContext);

            return [
                'success' => true,
                'gateway_subscription_id' => $agreement->getId(),
                'status' => 'pending',
                'current_period_start' => time(),
                'current_period_end' => strtotime("+1 month"),
                'data' => $agreement->toArray(),
            ];
        } catch (Exception $e) {
            $this->logError($e, 'createSubscription');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateSubscription(string $gatewaySubscriptionId, array $subscriptionData): array
    {
        try {
            if (isset($subscriptionData['cancel'])) {
                $agreement = Agreement::get($gatewaySubscriptionId, $this->apiContext);
                $agreement->cancel([], $this->apiContext);

                return [
                    'success' => true,
                    'status' => 'canceled',
                ];
            }

            return [
                'success' => true,
                'status' => 'active',
            ];
        } catch (Exception $e) {
            $this->logError($e, 'updateSubscription');
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createPaymentLink(array $linkData): array
    {
        try {
            $this->validateRequiredFields($linkData, ['amount', 'currency', 'description']);

            // Crear un simple payment
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $amount = new \PayPal\Api\Amount();
            $amount->setTotal($linkData['amount'])
                ->setCurrency(strtoupper($linkData['currency']));

            $transaction = new \PayPal\Api\Transaction();
            $transaction->setAmount($amount)
                ->setDescription($linkData['description']);

            $redirectUrls = new \PayPal\Api\RedirectUrls();
            $redirectUrls->setReturnUrl(config('app.url') . '/payment/success')
                ->setCancelUrl(config('app.url') . '/payment/cancel');

            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);

            $payment->create($this->apiContext);

            $approvalUrl = $payment->getApprovalLink();

            return [
                'success' => true,
                'gateway_link_id' => $payment->getId(),
                'url' => $approvalUrl,
                'data' => $payment->toArray(),
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
