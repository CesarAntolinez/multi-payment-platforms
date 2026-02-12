# 🔌 Agregar Nuevas Pasarelas de Pago

[⬅️ Anterior: Uso](05-USO.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Webhooks](07-WEBHOOKS.md)

---

## 📋 Tabla de Contenidos

- [Introducción](#introducción)
- [Requisitos Previos](#requisitos-previos)
- [Guía Paso a Paso](#guía-paso-a-paso)
- [Ejemplo Completo: Mercado Pago](#ejemplo-completo-mercado-pago)
- [Checklist de Implementación](#checklist-de-implementación)
- [Testing de Nueva Pasarela](#testing-de-nueva-pasarela)
- [Consideraciones Adicionales](#consideraciones-adicionales)

---

## Introducción

Gracias al **Patrón Strategy**, agregar una nueva pasarela de pago es un proceso simple y directo que **NO requiere modificar código existente**. Solo necesitas:

1. ✅ Crear una nueva clase que implemente `PaymentGatewayInterface`
2. ✅ Registrarla en `PaymentGatewayManager`
3. ✅ Configurar credenciales en `.env`
4. ✅ (Opcional) Agregar opciones en la UI

**Tiempo estimado**: 1-2 horas (dependiendo de la complejidad de la API)

---

## Requisitos Previos

### Conocimientos

- ✅ PHP 8.0+ y POO
- ✅ Consumo de APIs REST
- ✅ Composer para gestionar dependencias
- ✅ Entendimiento del Patrón Strategy

### Información Necesaria

Antes de empezar, obtén:

- 📄 Documentación de la API de la pasarela
- 🔑 Credenciales de prueba (sandbox)
- 📦 SDK oficial (si existe)
- 🔗 Endpoints de la API
- 📋 Formato de webhooks

---

## Guía Paso a Paso

### Paso 1: Instalar SDK (si existe)

Si la pasarela tiene un SDK oficial de PHP, instalarlo con Composer:

```bash
composer require nombre-vendor/sdk-pasarela
```

**Ejemplo con Mercado Pago:**
```bash
composer require mercadopago/dx-php
```

### Paso 2: Crear Clase de Gateway

Crear archivo en `app/Services/PaymentGateways/NombrePasarelaGateway.php`

**Template básico:**

```php
<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use Exception;

class NombrePasarelaGateway implements PaymentGatewayInterface
{
    protected $client;

    public function __construct()
    {
        // Inicializar SDK o cliente HTTP
        $this->client = $this->initializeClient();
    }

    /**
     * Inicializar cliente de la pasarela
     */
    private function initializeClient()
    {
        // Configurar SDK con credenciales desde config/services.php
        // Retornar instancia configurada
    }

    /**
     * Implementar: crear cliente
     */
    public function createCustomer(array $customerData): array
    {
        try {
            // Lógica para crear cliente en la pasarela
            // Retornar formato estándar
            return [
                'success' => true,
                'gateway_customer_id' => 'id_del_cliente',
                'data' => [/* datos adicionales */]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Implementar: actualizar cliente
     */
    public function updateCustomer(string $gatewayCustomerId, array $customerData): array
    {
        // Implementar...
    }

    /**
     * Implementar: crear tarjeta
     */
    public function createCard(string $gatewayCustomerId, string $tokenOrCardData): array
    {
        // Implementar...
    }

    /**
     * Implementar: crear plan
     */
    public function createPlan(array $planData): array
    {
        // Implementar...
    }

    /**
     * Implementar: actualizar plan
     */
    public function updatePlan(string $gatewayPlanId, array $planData): array
    {
        // Implementar...
    }

    /**
     * Implementar: crear suscripción
     */
    public function createSubscription(array $subscriptionData): array
    {
        // Implementar...
    }

    /**
     * Implementar: actualizar suscripción
     */
    public function updateSubscription(string $gatewaySubscriptionId, array $subscriptionData): array
    {
        // Implementar...
    }

    /**
     * Implementar: crear link de pago
     */
    public function createPaymentLink(array $linkData): array
    {
        // Implementar...
    }

    /**
     * Obtener nombre de la pasarela
     */
    public function getGatewayName(): string
    {
        return 'nombre_pasarela';
    }
}
```

### Paso 3: Registrar en PaymentGatewayManager

Editar `app/Services/PaymentGatewayManager.php`:

```php
use App\Services\PaymentGateways\NombrePasarelaGateway;

public function __construct()
{
    $this->registerGateway('stripe', new StripeGateway());
    $this->registerGateway('paypal', new PayPalGateway());
    
    // ⭐ Agregar nueva pasarela
    $this->registerGateway('nombre_pasarela', new NombrePasarelaGateway());
}
```

### Paso 4: Configurar Credenciales

**En `config/services.php`:**

```php
return [
    // ... otras configuraciones
    
    'nombre_pasarela' => [
        'key' => env('NOMBRE_PASARELA_KEY'),
        'secret' => env('NOMBRE_PASARELA_SECRET'),
        'mode' => env('NOMBRE_PASARELA_MODE', 'sandbox'),
        // Otros parámetros necesarios
    ],
];
```

**En `.env`:**

```env
NOMBRE_PASARELA_KEY=tu_clave_publica
NOMBRE_PASARELA_SECRET=tu_clave_secreta
NOMBRE_PASARELA_MODE=sandbox
```

---

## Ejemplo Completo: Mercado Pago

Implementación real de una nueva pasarela.

### 1. Instalar SDK

```bash
composer require mercadopago/dx-php
```

### 2. Crear MercadoPagoGateway.php

```php
<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use MercadoPago\SDK;
use MercadoPago\Customer;
use MercadoPago\Card;
use MercadoPago\Plan;
use MercadoPago\Subscription;
use MercadoPago\Preference;
use Exception;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        // Configurar SDK
        SDK::setAccessToken(config('services.mercadopago.access_token'));
    }

    /**
     * Crear cliente en Mercado Pago
     */
    public function createCustomer(array $customerData): array
    {
        try {
            $customer = new Customer();
            $customer->email = $customerData['email'];
            $customer->first_name = $customerData['name'];
            
            // Metadata
            if (isset($customerData['metadata'])) {
                $customer->metadata = $customerData['metadata'];
            }
            
            $customer->save();
            
            return [
                'success' => true,
                'gateway_customer_id' => $customer->id,
                'data' => [
                    'email' => $customer->email,
                    'name' => $customer->first_name,
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar cliente
     */
    public function updateCustomer(string $gatewayCustomerId, array $customerData): array
    {
        try {
            $customer = Customer::find_by_id($gatewayCustomerId);
            
            if (isset($customerData['email'])) {
                $customer->email = $customerData['email'];
            }
            
            if (isset($customerData['name'])) {
                $customer->first_name = $customerData['name'];
            }
            
            $customer->update();
            
            return [
                'success' => true,
                'gateway_customer_id' => $customer->id,
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear tarjeta
     */
    public function createCard(string $gatewayCustomerId, string $tokenOrCardData): array
    {
        try {
            $card = new Card();
            $card->customer_id = $gatewayCustomerId;
            $card->token = $tokenOrCardData;
            $card->save();
            
            return [
                'success' => true,
                'gateway_card_id' => $card->id,
                'data' => [
                    'last_four' => $card->last_four_digits,
                    'brand' => $card->payment_method->name,
                    'exp_month' => $card->expiration_month,
                    'exp_year' => $card->expiration_year,
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear plan
     */
    public function createPlan(array $planData): array
    {
        try {
            $plan = new Plan();
            $plan->description = $planData['name'];
            $plan->auto_recurring = [
                'frequency' => 1,
                'frequency_type' => $planData['interval'], // 'months' o 'years'
                'transaction_amount' => $planData['amount'] / 100, // De centavos a decimal
                'currency_id' => strtoupper($planData['currency']),
            ];
            
            $plan->save();
            
            return [
                'success' => true,
                'gateway_plan_id' => $plan->id,
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar plan
     */
    public function updatePlan(string $gatewayPlanId, array $planData): array
    {
        try {
            $plan = Plan::find_by_id($gatewayPlanId);
            
            if (isset($planData['name'])) {
                $plan->description = $planData['name'];
            }
            
            $plan->update();
            
            return [
                'success' => true,
                'gateway_plan_id' => $plan->id,
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear suscripción
     */
    public function createSubscription(array $subscriptionData): array
    {
        try {
            $subscription = new Subscription();
            $subscription->plan_id = $subscriptionData['price_id'];
            $subscription->payer = [
                'email' => $subscriptionData['customer_email'] ?? null,
            ];
            
            // Card token
            if (isset($subscriptionData['card_token'])) {
                $subscription->card_token_id = $subscriptionData['card_token'];
            }
            
            $subscription->save();
            
            return [
                'success' => true,
                'gateway_subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_start' => strtotime($subscription->date_created),
                'current_period_end' => strtotime($subscription->next_payment_date),
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar suscripción
     */
    public function updateSubscription(string $gatewaySubscriptionId, array $subscriptionData): array
    {
        try {
            $subscription = Subscription::find_by_id($gatewaySubscriptionId);
            
            if (isset($subscriptionData['status'])) {
                $subscription->status = $subscriptionData['status'];
            }
            
            $subscription->update();
            
            return [
                'success' => true,
                'gateway_subscription_id' => $subscription->id,
                'status' => $subscription->status,
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear link de pago
     */
    public function createPaymentLink(array $linkData): array
    {
        try {
            $preference = new Preference();
            
            $item = [
                'title' => $linkData['description'] ?? 'Pago',
                'quantity' => 1,
                'unit_price' => $linkData['amount'] / 100,
                'currency_id' => strtoupper($linkData['currency']),
            ];
            
            $preference->items = [$item];
            $preference->save();
            
            return [
                'success' => true,
                'gateway_link_id' => $preference->id,
                'url' => $preference->init_point, // URL de pago
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Nombre de la pasarela
     */
    public function getGatewayName(): string
    {
        return 'mercadopago';
    }
}
```

### 3. Registrar en Manager

```php
// app/Services/PaymentGatewayManager.php

use App\Services\PaymentGateways\MercadoPagoGateway;

public function __construct()
{
    $this->registerGateway('stripe', new StripeGateway());
    $this->registerGateway('paypal', new PayPalGateway());
    $this->registerGateway('mercadopago', new MercadoPagoGateway());
}
```

### 4. Configurar en services.php

```php
// config/services.php

'mercadopago' => [
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
],
```

### 5. Agregar en .env

```env
MERCADOPAGO_ACCESS_TOKEN=APP_USR-xxxxxxxxx
MERCADOPAGO_PUBLIC_KEY=APP_USR-xxxxxxxxx
```

### 6. Usar en la Aplicación

```php
use App\Services\CustomerService;

$customerService = app(CustomerService::class);

$customer = $customerService->createCustomer(
    user: auth()->user(),
    gateway: 'mercadopago'
);

echo "Cliente Mercado Pago creado: " . $customer->gateway_customer_id;
```

---

## Checklist de Implementación

Usa este checklist para asegurar una implementación completa:

### ✅ Código

- [ ] SDK instalado (si existe)
- [ ] Clase Gateway creada e implementa `PaymentGatewayInterface`
- [ ] Todos los métodos de la interfaz implementados
- [ ] Gateway registrado en `PaymentGatewayManager`
- [ ] Configuración agregada en `config/services.php`
- [ ] Variables de entorno documentadas en `.env.example`

### ✅ Manejo de Errores

- [ ] Try-catch en todos los métodos
- [ ] Retorno consistente (success/error)
- [ ] Logs de errores implementados
- [ ] Validación de datos de entrada

### ✅ Formato de Respuesta

Asegurar que todas las respuestas sigan el formato estándar:

```php
// Éxito
[
    'success' => true,
    'gateway_xxx_id' => 'id',
    'data' => [/* datos adicionales */]
]

// Error
[
    'success' => false,
    'error' => 'Mensaje de error'
]
```

### ✅ Testing

- [ ] Test unitario de la clase Gateway
- [ ] Test de integración con sandbox
- [ ] Test de todos los métodos principales
- [ ] Documentación de tarjetas/cuentas de prueba

### ✅ Documentación

- [ ] Comentarios PHPDoc en todos los métodos
- [ ] README con instrucciones de configuración
- [ ] Ejemplos de uso
- [ ] Webhook events documentados

### ✅ UI (Opcional)

- [ ] Opción agregada en selects de pasarela
- [ ] Logo/icono de la pasarela
- [ ] Instrucciones específicas para usuarios

---

## Testing de Nueva Pasarela

### Test Unitario Básico

Crear `tests/Unit/MercadoPagoGatewayTest.php`:

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymentGatewayManager;

class MercadoPagoGatewayTest extends TestCase
{
    protected $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        $manager = app(PaymentGatewayManager::class);
        $this->gateway = $manager->gateway('mercadopago');
    }

    /** @test */
    public function it_can_get_gateway_name()
    {
        $this->assertEquals('mercadopago', $this->gateway->getGatewayName());
    }

    /** @test */
    public function it_can_create_customer()
    {
        $result = $this->gateway->createCustomer([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('gateway_customer_id', $result);
    }

    /** @test */
    public function it_can_create_plan()
    {
        $result = $this->gateway->createPlan([
            'name' => 'Test Plan',
            'amount' => 2999,
            'currency' => 'usd',
            'interval' => 'month'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('gateway_plan_id', $result);
    }
}
```

### Test de Integración

```php
/** @test */
public function it_can_create_complete_subscription_flow()
{
    // 1. Crear cliente
    $customerResult = $this->gateway->createCustomer([
        'email' => 'test@example.com',
        'name' => 'Test User'
    ]);
    
    $this->assertTrue($customerResult['success']);
    
    // 2. Crear plan
    $planResult = $this->gateway->createPlan([
        'name' => 'Test Plan',
        'amount' => 999,
        'currency' => 'usd',
        'interval' => 'month'
    ]);
    
    $this->assertTrue($planResult['success']);
    
    // 3. Crear suscripción
    $subscriptionResult = $this->gateway->createSubscription([
        'customer_id' => $customerResult['gateway_customer_id'],
        'price_id' => $planResult['gateway_plan_id']
    ]);
    
    $this->assertTrue($subscriptionResult['success']);
    $this->assertArrayHasKey('gateway_subscription_id', $subscriptionResult);
}
```

### Ejecutar Tests

```bash
# Test específico
php artisan test --filter MercadoPagoGatewayTest

# Todos los tests
php artisan test
```

---

## Consideraciones Adicionales

### Webhooks

Agregar manejo de webhooks en `WebhookController.php`:

```php
public function mercadopago(Request $request)
{
    // Verificar firma
    // Procesar evento
    // Guardar en PaymentWebhook
    
    return response()->json(['status' => 'success']);
}
```

Registrar ruta en `routes/web.php`:

```php
Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadopago']);
```

### Conversión de Montos

Asegurar conversión correcta entre formatos:

```php
// La aplicación usa centavos
$amount = 2999; // $29.99

// Algunas pasarelas usan decimales
$amountDecimal = $amount / 100; // 29.99

// Otras usan centavos directamente
$amountCents = $amount; // 2999
```

### Intervalos de Suscripción

Mapear intervalos correctamente:

```php
// Aplicación usa: 'month', 'year', 'week', 'day'

// Mercado Pago usa: 'months', 'years'
$interval = $planData['interval'] === 'month' ? 'months' : 'years';

// PayPal usa: 'MONTH', 'YEAR'
$interval = strtoupper($planData['interval']);
```

### Logging

Agregar logs para debugging:

```php
\Log::info('MercadoPago: Creating customer', [
    'email' => $customerData['email']
]);

try {
    // ...
} catch (Exception $e) {
    \Log::error('MercadoPago: Error creating customer', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

---

## Próximos Pasos

¡Felicidades! Has agregado una nueva pasarela. Ahora:

1. **Configura webhooks**: [07-WEBHOOKS.md](07-WEBHOOKS.md)
2. **Ejecuta tests**: [08-TESTING.md](08-TESTING.md)
3. **Revisa buenas prácticas**: [09-BUENAS-PRACTICAS.md](09-BUENAS-PRACTICAS.md)

---

[⬅️ Anterior: Uso](05-USO.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Webhooks](07-WEBHOOKS.md)
