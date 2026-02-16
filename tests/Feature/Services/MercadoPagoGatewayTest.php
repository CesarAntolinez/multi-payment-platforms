<?php

namespace Tests\Feature\Services;

use App\Services\PaymentGateways\MercadoPagoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MercadoPagoGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected MercadoPagoGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Only initialize the gateway if credentials are configured
        if (config('services.mercadopago.access_token')) {
            $this->gateway = new MercadoPagoGateway();
        }
    }

    /** @test */
    public function it_returns_correct_gateway_name()
    {
        if (!config('services.mercadopago.access_token')) {
            $this->markTestSkipped('Mercado Pago credentials not configured');
        }

        $this->assertEquals('mercadopago', $this->gateway->getGatewayName());
    }

    /** @test */
    public function it_can_create_a_customer()
    {
        if (!config('services.mercadopago.access_token')) {
            $this->markTestSkipped('Mercado Pago credentials not configured');
        }

        $customerData = [
            'email' => 'test@example.com',
            'name' => 'John Doe',
        ];

        $result = $this->gateway->createCustomer($customerData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('gateway_customer_id', $result);
    }

    /** @test */
    public function it_validates_required_fields_for_customer_creation()
    {
        if (!config('services.mercadopago.access_token')) {
            $this->markTestSkipped('Mercado Pago credentials not configured');
        }

        $result = $this->gateway->createCustomer([]);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /** @test */
    public function it_can_create_a_plan()
    {
        if (!config('services.mercadopago.access_token')) {
            $this->markTestSkipped('Mercado Pago credentials not configured');
        }

        $planData = [
            'name' => 'Test Plan',
            'amount' => 9.99,
            'currency' => 'MXN',
            'interval' => 'month',
        ];

        $result = $this->gateway->createPlan($planData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('gateway_plan_id', $result);
    }

    /** @test */
    public function it_validates_required_fields_for_plan_creation()
    {
        if (!config('services.mercadopago.access_token')) {
            $this->markTestSkipped('Mercado Pago credentials not configured');
        }

        $result = $this->gateway->createPlan([]);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /** @test */
    public function it_can_create_a_payment_link()
    {
        if (!config('services.mercadopago.access_token')) {
            $this->markTestSkipped('Mercado Pago credentials not configured');
        }

        $linkData = [
            'amount' => 100.00,
            'currency' => 'MXN',
            'description' => 'Test Payment',
        ];

        $result = $this->gateway->createPaymentLink($linkData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('gateway_link_id', $result);
        $this->assertArrayHasKey('url', $result);
    }

    /** @test */
    public function it_validates_required_fields_for_payment_link_creation()
    {
        if (!config('services.mercadopago.access_token')) {
            $this->markTestSkipped('Mercado Pago credentials not configured');
        }

        $result = $this->gateway->createPaymentLink([]);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /** @test */
    public function it_handles_errors_gracefully()
    {
        if (!config('services.mercadopago.access_token')) {
            $this->markTestSkipped('Mercado Pago credentials not configured');
        }

        // Attempt to update a non-existent customer
        $result = $this->gateway->updateCustomer('invalid_customer_id', [
            'name' => 'Test Name',
        ]);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }
}
