<?php

namespace App\Services;

use App\Models\PaymentPlan;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PlanService
{
    protected PaymentGatewayManager $gatewayManager;
    protected int $cacheTime = 3600; // 1 hora

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Crear un plan de suscripción
     */
    public function createPlan(string $gateway, array $planData): PaymentPlan
    {
        $plan = DB::transaction(function () use ($gateway, $planData) {
            $this->validatePlanData($planData);

            $result = $this->gatewayManager->gateway($gateway)->createPlan($planData);

            if (!$result['success']) {
                throw new Exception("Error al crear plan: " . $result['error']);
            }

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

        // Limpiar caché de planes
        $this->clearPlansCache($gateway);

        return $plan;
    }

    /**
     * Actualizar un plan
     */
    public function updatePlan(PaymentPlan $plan, array $updateData): PaymentPlan
    {
        $updatedPlan = DB::transaction(function () use ($plan, $updateData) {
            $result = $this->gatewayManager
                ->gateway($plan->gateway)
                ->updatePlan($plan->gateway_plan_id, $updateData);

            if (!$result['success']) {
                throw new Exception("Error al actualizar plan: " . $result['error']);
            }

            $plan->update([
                'name' => $updateData['name'] ?? $plan->name,
                'active' => $updateData['active'] ?? $plan->active,
                'metadata' => array_merge($plan->metadata ?? [], $updateData['metadata'] ?? []),
            ]);

            return $plan->fresh();
        });

        // Limpiar caché de planes
        $this->clearPlansCache($plan->gateway);

        return $updatedPlan;
    }

    /**
     * Obtener planes activos por pasarela (con caché)
     */
    public function getActivePlans(string $gateway = null)
    {
        $cacheKey = $gateway ? "plans.active.{$gateway}" : "plans.active.all";

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($gateway) {
            $query = PaymentPlan::active();

            if ($gateway) {
                $query->where('gateway', $gateway);
            }

            return $query->get();
        });
    }

    /**
     * Limpiar caché de planes
     */
    protected function clearPlansCache(?string $gateway = null): void
    {
        if ($gateway) {
            Cache::forget("plans.active.{$gateway}");
        }
        Cache::forget("plans.active.all");
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
