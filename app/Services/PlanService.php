<?php

namespace App\Services;

use App\Models\PaymentPlan;
use Exception;
use Illuminate\Support\Facades\DB;

class PlanService
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Crear un plan de suscripción
     */
    public function createPlan(string $gateway, array $planData): PaymentPlan
    {
        return DB::transaction(function () use ($gateway, $planData) {
            // Validar datos requeridos
            $this->validatePlanData($planData);

            // Crear plan en la pasarela
            $result = $this->gatewayManager->gateway($gateway)->createPlan($planData);

            if (!$result['success']) {
                throw new Exception("Error al crear plan: " . $result['error']);
            }

            // Guardar en la base de datos
            return PaymentPlan::create([
                'gateway' => $gateway,
                'gateway_plan_id' => $result['gateway_plan_id'],
                'name' => $planData['name'],
                'amount' => $planData['amount'],
                'currency' => $planData['currency'],
                'interval' => $planData['interval'],
                'interval_count' => $planData['interval_count'] ?? 1,
                'active' => $planData['active'] ?? true,
                'metadata' => $planData['metadata'] ?? [],
            ]);
        });
    }

    /**
     * Actualizar un plan
     */
    public function updatePlan(PaymentPlan $plan, array $updateData): PaymentPlan
    {
        return DB::transaction(function () use ($plan, $updateData) {
            // Actualizar en la pasarela
            $result = $this->gatewayManager
                ->gateway($plan->gateway)
                ->updatePlan($plan->gateway_plan_id, $updateData);

            if (!$result['success']) {
                throw new Exception("Error al actualizar plan: " . $result['error']);
            }

            // Actualizar en la base de datos
            $plan->update([
                'name' => $updateData['name'] ?? $plan->name,
                'active' => $updateData['active'] ?? $plan->active,
                'metadata' => array_merge($plan->metadata ?? [], $updateData['metadata'] ?? []),
            ]);

            return $plan->fresh();
        });
    }

    /**
     * Obtener planes activos por pasarela
     */
    public function getActivePlans(string $gateway = null)
    {
        $query = PaymentPlan::active();

        if ($gateway) {
            $query->where('gateway', $gateway);
        }

        return $query->get();
    }

    /**
     * Validar datos del plan
     */
    protected function validatePlanData(array $planData): void
    {
        $required = ['name', 'amount', 'currency', 'interval'];

        foreach ($required as $field) {
            if (!isset($planData[$field]) || empty($planData[$field])) {
                throw new Exception("El campo {$field} es requerido");
            }
        }

        $validIntervals = ['day', 'week', 'month', 'year'];
        if (!in_array($planData['interval'], $validIntervals)) {
            throw new Exception("El intervalo debe ser uno de: " . implode(', ', $validIntervals));
        }
    }
}
