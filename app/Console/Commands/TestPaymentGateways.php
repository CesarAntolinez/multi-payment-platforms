<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CustomerService;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use App\Services\PaymentLinkService;
use Illuminate\Console\Command;

class TestPaymentGateways extends Command
{
    protected $signature = 'payment:test {action}';
    protected $description = 'Probar funcionalidades de pasarelas de pago';

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'customer':
                $this->testCreateCustomer();
                break;
            case 'plan':
                $this->testCreatePlan();
                break;
            case 'subscription':
                $this->testCreateSubscription();
                break;
            case 'link':
                $this->testCreatePaymentLink();
                break;
            default:
                $this->error('Acción no válida. Usa: customer, plan, subscription, link');
        }
    }

    protected function testCreateCustomer()
    {
        $this->info('🧪 Probando creación de cliente...');

        $user = User::first();
        if (!$user) {
            $this->error('No hay usuarios en la base de datos');
            return;
        }

        $customerService = app(CustomerService::class);

        try {
            $customer = $customerService->createCustomer($user, 'stripe');
            $this->info("✅ Cliente creado: {$customer->gateway_customer_id}");
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
        }
    }

    protected function testCreatePlan()
    {
        $this->info('🧪 Probando creación de plan...');

        $planService = app(PlanService::class);

        try {
            $plan = $planService->createPlan('stripe', [
                'name' => 'Plan de Prueba',
                'amount' => 15.00,
                'currency' => 'usd',
                'interval' => 'month',
                'interval_count' => 1,
            ]);

            $this->info("✅ Plan creado: {$plan->name} - {$plan->gateway_plan_id}");
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
        }
    }

    protected function testCreateSubscription()
    {
        $this->info('🧪 Probando creación de suscripción...');

        $user = User::first();
        $customerService = app(CustomerService::class);
        $planService = app(PlanService::class);
        $subscriptionService = app(SubscriptionService::class);

        try {
            // Obtener o crear cliente
            $customer = $customerService->getOrCreateCustomer($user, 'stripe');

            // Obtener un plan
            $plans = $planService->getActivePlans('stripe');
            if ($plans->isEmpty()) {
                $this->error('No hay planes disponibles');
                return;
            }

            $plan = $plans->first();

            // Crear suscripción
            $subscription = $subscriptionService->createSubscription($customer, $plan);

            $this->info("✅ Suscripción creada: {$subscription->gateway_subscription_id}");
            $this->info("   Estado: {$subscription->status}");
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
        }
    }

    protected function testCreatePaymentLink()
    {
        $this->info('🧪 Probando creación de link de pago...');

        $paymentLinkService = app(PaymentLinkService::class);

        try {
            $link = $paymentLinkService->createPaymentLink('stripe', [
                'amount' => 50.00,
                'currency' => 'usd',
                'description' => 'Pago de prueba',
            ]);

            $this->info("✅ Link creado: {$link->url}");
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
        }
    }
}
