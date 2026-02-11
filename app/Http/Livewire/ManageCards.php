<?php

namespace App\Http\Livewire;

use App\Services\CardService;
use App\Services\CustomerService;
use Livewire\Component;
use Exception;

class ManageCards extends Component
{
    public $gateway = 'stripe';
    public $cardToken = '';
    public $cards = [];
    public $successMessage = '';
    public $errorMessage = '';

    protected CardService $cardService;
    protected CustomerService $customerService;

    public function boot(CardService $cardService, CustomerService $customerService)
    {
        $this->cardService = $cardService;
        $this->customerService = $customerService;
    }

    public function mount()
    {
        $this->loadCards();
    }

    public function loadCards()
    {
        try {
            $user = auth()->user();
            $customer = $this->customerService->getCustomer($user, $this->gateway);

            if ($customer) {
                $this->cards = $this->cardService->getCustomerCards($customer)->toArray();
            }
        } catch (Exception $e) {
            $this->errorMessage = "Error al cargar tarjetas: " . $e->getMessage();
        }
    }

    public function addCard()
    {
        $this->resetMessages();

        $this->validate([
            'cardToken' => 'required|string',
        ]);

        try {
            $user = auth()->user();

            // Obtener o crear cliente
            $customer = $this->customerService->getOrCreateCustomer($user, $this->gateway);

            // Crear tarjeta
            $card = $this->cardService->createCard($customer, $this->cardToken);

            $this->successMessage = "Tarjeta {$card->brand} **** {$card->last4} agregada exitosamente";

            $this->cardToken = '';
            $this->loadCards();
            $this->emit('cardAdded', $card->id);
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
        return view('livewire.manage-cards');
    }
}
