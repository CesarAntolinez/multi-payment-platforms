<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\CustomerService;
use App\Services\PlanService;
use Illuminate\Database\Seeder;

class PaymentTestSeeder extends Seeder
{
    public function run()
    {
        // Crear un usuario de prueba si no existe
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Usuario de Prueba',
                'password' => bcrypt('password'),
            ]
        );

        echo "✅ Usuario de prueba creado: {$user->email}\n";
        echo "   Contraseña: password\n\n";

        // Crear planes de ejemplo
        $planService = app(PlanService::class);

        $plansData = [
            [
                'name' => 'Plan Básico Mensual',
                'amount' => 9.99,
                'currency' => 'usd',
                'interval' => 'month',
                'interval_count' => 1,
            ],
            [
                'name' => 'Plan Premium Mensual',
                'amount' => 19.99,
                'currency' => 'usd',
                'interval' => 'month',
                'interval_count' => 1,
            ],
            [
                'name' => 'Plan Anual',
                'amount' => 99.99,
                'currency' => 'usd',
                'interval' => 'year',
                'interval_count' => 1,
            ],
        ];

        foreach ($plansData as $planData) {
            try {
                $plan = $planService->createPlan('stripe', $planData);
                echo "✅ Plan creado: {$plan->name} - \${$plan->amount} {$plan->currency}\n";
            } catch (\Exception $e) {
                echo "⚠️  Error al crear plan '{$planData['name']}': {$e->getMessage()}\n";
            }
        }

        echo "\n✨ Seeder completado!\n";
    }
}
