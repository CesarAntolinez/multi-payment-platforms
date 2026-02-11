<?php

namespace App\Providers;

use App\Services\CardService;
use App\Services\CustomerService;
use App\Services\PaymentGatewayManager;
use App\Services\PaymentLinkService;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Registrar PaymentGatewayManager como singleton
        $this->app->singleton(PaymentGatewayManager::class, function ($app) {
            return new PaymentGatewayManager();
        });

        // Registrar los servicios
        $this->app->singleton(CustomerService::class, function ($app) {
            return new CustomerService($app->make(PaymentGatewayManager::class));
        });

        $this->app->singleton(CardService::class, function ($app) {
            return new CardService($app->make(PaymentGatewayManager::class));
        });

        $this->app->singleton(PlanService::class, function ($app) {
            return new PlanService($app->make(PaymentGatewayManager::class));
        });

        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService($app->make(PaymentGatewayManager::class));
        });

        $this->app->singleton(PaymentLinkService::class, function ($app) {
            return new PaymentLinkService($app->make(PaymentGatewayManager::class));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
