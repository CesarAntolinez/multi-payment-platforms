<?php

namespace App\Http\Livewire;

use App\Services\CustomerService;
use Livewire\Component;
use Exception;

class CreateCustomer extends Component
{
    public $gateway = 'stripe';
    public $successMessage = '';
    public $errorMessage = '';

    protected CustomerService $customerService;

    public function boot(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function createCustomer()
    {
        $this->resetMessages();

        try {
            $user = auth()->user();

            $customer = $this->customerService->createCustomer($user, $this->gateway);

            $this->successMessage = "Cliente creado exitosamente en {$this->gateway}. ID: {$customer->gateway_customer_id}";

            $this->emit('customerCreated', $customer->id);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.create-customer');
    }
}
