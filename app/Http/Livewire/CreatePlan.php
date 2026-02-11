<?php

namespace App\Http\Livewire;

use App\Services\PlanService;
use Livewire\Component;
use Exception;

class CreatePlan extends Component
{
    public $gateway = 'stripe';
    public $name = '';
    public $amount = '';
    public $currency = 'USD';
    public $interval = 'month';
    public $intervalCount = 1;
    public $successMessage = '';
    public $errorMessage = '';

    protected PlanService $planService;

    protected $rules = [
        'name' => 'required|string|min:3',
        'amount' => 'required|numeric|min:0.01',
        'currency' => 'required|string|size:3',
        'interval' => 'required|in:day,week,month,year',
        'intervalCount' => 'required|integer|min:1',
    ];

    public function boot(PlanService $planService)
    {
        $this->planService = $planService;
    }

    public function createPlan()
    {
        $this->resetMessages();

        $this->validate();

        try {
            $planData = [
                'name' => $this->name,
                'amount' => (float) $this->amount,
                'currency' => strtolower($this->currency),
                'interval' => $this->interval,
                'interval_count' => (int) $this->intervalCount,
            ];

            $plan = $this->planService->createPlan($this->gateway, $planData);

            $this->successMessage = "Plan '{$plan->name}' creado exitosamente en {$this->gateway}";

            $this->resetForm();
            $this->emit('planCreated', $plan->id);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->amount = '';
        $this->currency = 'USD';
        $this->interval = 'month';
        $this->intervalCount = 1;
    }

    public function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.create-plan');
    }
}
