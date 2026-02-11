<?php

namespace App\Http\Livewire;

use App\Services\PaymentLinkService;
use Livewire\Component;
use Exception;

class CreatePaymentLink extends Component
{
    public $gateway = 'stripe';
    public $amount = '';
    public $currency = 'USD';
    public $description = '';
    public $generatedUrl = '';
    public $successMessage = '';
    public $errorMessage = '';

    protected PaymentLinkService $paymentLinkService;

    protected $rules = [
        'amount' => 'required|numeric|min:0.01',
        'currency' => 'required|string|size:3',
        'description' => 'required|string|min:5',
    ];

    public function boot(PaymentLinkService $paymentLinkService)
    {
        $this->paymentLinkService = $paymentLinkService;
    }

    public function createPaymentLink()
    {
        $this->resetMessages();

        $this->validate();

        try {
            $linkData = [
                'amount' => (float) $this->amount,
                'currency' => strtolower($this->currency),
                'description' => $this->description,
            ];

            $link = $this->paymentLinkService->createPaymentLink($this->gateway, $linkData);

            $this->generatedUrl = $link->url;
            $this->successMessage = "Link de pago creado exitosamente";

            $this->emit('paymentLinkCreated', $link->id);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function copyToClipboard()
    {
        $this->emit('copyUrl', $this->generatedUrl);
    }

    public function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->generatedUrl = '';
    }

    public function render()
    {
        return view('livewire.create-payment-link');
    }
}
