<?php

namespace App\Services;

use App\Models\PaymentLink;
use Exception;
use Illuminate\Support\Facades\DB;

class PaymentLinkService
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Crear un link de pago
     */
    public function createPaymentLink(string $gateway, array $linkData): PaymentLink
    {
        return DB::transaction(function () use ($gateway, $linkData) {
            // Validar datos requeridos
            $this->validateLinkData($linkData);

            // Crear link en la pasarela
            $result = $this->gatewayManager->gateway($gateway)->createPaymentLink($linkData);

            if (!$result['success']) {
                throw new Exception("Error al crear link de pago: " . $result['error']);
            }

            // Guardar en la base de datos
            return PaymentLink::create([
                'gateway' => $gateway,
                'gateway_link_id' => $result['gateway_link_id'],
                'amount' => $linkData['amount'],
                'currency' => $linkData['currency'],
                'description' => $linkData['description'],
                'url' => $result['url'],
                'status' => 'active',
                'expires_at' => $linkData['expires_at'] ?? null,
                'metadata' => $linkData['metadata'] ?? [],
            ]);
        });
    }

    /**
     * Obtener links activos
     */
    public function getActiveLinks(string $gateway = null)
    {
        $query = PaymentLink::active();

        if ($gateway) {
            $query->where('gateway', $gateway);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Validar datos del link
     */
    protected function validateLinkData(array $linkData): void
    {
        $required = ['amount', 'currency', 'description'];

        foreach ($required as $field) {
            if (!isset($linkData[$field]) || empty($linkData[$field])) {
                throw new Exception("El campo {$field} es requerido");
            }
        }

        if ($linkData['amount'] <= 0) {
            throw new Exception("El monto debe ser mayor a 0");
        }
    }
}
