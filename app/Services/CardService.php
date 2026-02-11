<?php

namespace App\Services;

use App\Models\PaymentCard;
use App\Models\PaymentCustomer;
use Exception;
use Illuminate\Support\Facades\DB;

class CardService
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Crear una tarjeta para un cliente
     *
     * @param PaymentCustomer $customer
     * @param string $tokenOrCardData Token de la tarjeta (ej: Stripe token)
     * @param bool $setAsDefault
     * @return PaymentCard
     */
    public function createCard(
        PaymentCustomer $customer,
        string $tokenOrCardData,
        bool $setAsDefault = true
    ): PaymentCard {
        return DB::transaction(function () use ($customer, $tokenOrCardData, $setAsDefault) {
            // Crear tarjeta en la pasarela
            $result = $this->gatewayManager
                ->gateway($customer->gateway)
                ->createCard($customer->gateway_customer_id, $tokenOrCardData);

            if (!$result['success']) {
                throw new Exception("Error al crear tarjeta: " . $result['error']);
            }

            // Si se establece como predeterminada, actualizar las demás
            if ($setAsDefault) {
                PaymentCard::where('payment_customer_id', $customer->id)
                    ->update(['is_default' => false]);
            }

            // Guardar en la base de datos
            return PaymentCard::create([
                'payment_customer_id' => $customer->id,
                'gateway' => $customer->gateway,
                'gateway_card_id' => $result['gateway_card_id'],
                'brand' => $result['brand'],
                'last4' => $result['last4'],
                'exp_month' => $result['exp_month'],
                'exp_year' => $result['exp_year'],
                'is_default' => $setAsDefault,
            ]);
        });
    }

    /**
     * Obtener la tarjeta predeterminada de un cliente
     */
    public function getDefaultCard(PaymentCustomer $customer): ?PaymentCard
    {
        return PaymentCard::where('payment_customer_id', $customer->id)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Obtener todas las tarjetas de un cliente
     */
    public function getCustomerCards(PaymentCustomer $customer)
    {
        return PaymentCard::where('payment_customer_id', $customer->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Establecer una tarjeta como predeterminada
     */
    public function setAsDefault(PaymentCard $card): PaymentCard
    {
        return DB::transaction(function () use ($card) {
            // Actualizar todas las tarjetas del cliente
            PaymentCard::where('payment_customer_id', $card->payment_customer_id)
                ->update(['is_default' => false]);

            $card->update(['is_default' => true]);

            return $card;
        });
    }
}
