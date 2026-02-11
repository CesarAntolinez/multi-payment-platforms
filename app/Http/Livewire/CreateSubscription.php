<?php

namespace App\Http\Livewire;

use App\Models\PaymentPlan;
use App\Services\CustomerService;
use App\Services\SubscriptionService;
use Livewire\Component;
use Exception;

class CreateSubscription extends Component
{
    public $gateway = 'stripe';
    public $selectedPlanId;
    public $plans = [];
    public $successMessage = '';
    public $errorMessage = '';

    protected CustomerService $customerService;
    protected SubscriptionService $subscriptionService;

    public function boot(CustomerService $customerService, SubscriptionService $subscriptionService)
    {
        $this->customerService = $customerService;
        $this->subscriptionService = $subscriptionService;
    }

    public function mount()
    {
        $this->loadPlans();
    }

    public function updatedGateway()
    {
        $this->loadPlans();
        $this->selectedPlanId = null;
    }

    public function loadPlans()
    {
        $this->plans = PaymentPlan::where('gateway', $this->gateway)
            ->active()
            ->get();
    }

    public function createSubscription()
    {
        $this->resetMessages();

        $this->validate([
            'selectedPlanId' => 'required|exists:payment_plans,id',
        ]);

        try {
            $user = auth()->user();

            // Obtener o crear cliente
            $customer = $this->customerService->getOrCreateCustomer($user, $this->gateway);

            // Obtener plan
            $plan = PaymentPlan::findOrFail($this->selectedPlanId);

            // Crear suscripción
            $subscription = $this->subscriptionService->createSubscription($customer, $plan);

            $this->successMessage = "Suscripción creada exitosamente. Estado: {$subscription->status}";

            $this->emit('subscriptionCreated', $subscription->id);
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
        return view('livewire.create-subscription');
    }
}
