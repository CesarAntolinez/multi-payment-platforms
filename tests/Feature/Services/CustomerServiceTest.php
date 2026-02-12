<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerService $customerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerService = app(CustomerService::class);
    }

    /** @test */
    public function it_can_create_a_customer()
    {
        $user = User::factory()->create();

        $customer = $this->customerService->createCustomer($user, 'stripe');

        $this->assertDatabaseHas('payment_customers', [
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'email' => $user->email,
        ]);

        $this->assertNotNull($customer->gateway_customer_id);
    }

    /** @test */
    public function it_prevents_duplicate_customers()
    {
        $user = User::factory()->create();

        $this->customerService->createCustomer($user, 'stripe');

        $this->expectException(\Exception::class);
        $this->customerService->createCustomer($user, 'stripe');
    }

    /** @test */
    public function it_can_update_a_customer()
    {
        $user = User::factory()->create();
        $customer = $this->customerService->createCustomer($user, 'stripe');

        $updatedCustomer = $this->customerService->updateCustomer($customer, [
            'name' => 'Nuevo Nombre',
        ]);

        $this->assertEquals('Nuevo Nombre', $updatedCustomer->name);
    }

    /** @test */
    public function it_can_get_or_create_customer()
    {
        $user = User::factory()->create();

        $customer1 = $this->customerService->getOrCreateCustomer($user, 'stripe');
        $customer2 = $this->customerService->getOrCreateCustomer($user, 'stripe');

        $this->assertEquals($customer1->id, $customer2->id);
    }
}
