<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Nombre de la pasarela
     */
    protected string $gatewayName;

    /**
     * Registrar errores
     *
     * @param \Exception $e
     * @param string $action
     * @return void
     */
    protected function logError(\Exception $e, string $action): void
    {
        Log::error("Error en {$this->gatewayName} - {$action}", [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Validar datos requeridos
     *
     * @param array $data
     * @param array $requiredFields
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function validateRequiredFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("El campo {$field} es requerido");
            }
        }
    }

    /**
     * Obtener el nombre de la pasarela
     *
     * @return string
     */
    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }
}
