# 🧪 Guía de Testing

[⬅️ Anterior: Webhooks](07-WEBHOOKS.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Buenas Prácticas](09-BUENAS-PRACTICAS.md)

---

## 📋 Tabla de Contenidos

- [Configuración del Entorno](#configuración-del-entorno)
- [Estructura de Tests](#estructura-de-tests)
- [Ejecutar Tests](#ejecutar-tests)
- [Tests Unitarios](#tests-unitarios)
- [Tests de Integración](#tests-de-integración)
- [Crear Nuevos Tests](#crear-nuevos-tests)
- [Mocking de Pasarelas](#mocking-de-pasarelas)
- [Best Practices](#best-practices)

---

## Configuración del Entorno

### Base de Datos de Testing

Crear `.env.testing` en la raíz del proyecto:

```env
APP_ENV=testing
APP_KEY=base64:GENERADA_POR_ARTISAN
APP_DEBUG=true

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# O usar MySQL de testing
# DB_CONNECTION=mysql
# DB_DATABASE=multi_payment_platforms_testing

# Credentials de testing (usar claves de test)
STRIPE_KEY=pk_test_XXXXXXXXXXXXXXXX
STRIPE_SECRET=sk_test_XXXXXXXXXXXXXXXX
STRIPE_WEBHOOK_SECRET=whsec_test_XXXXXXXXX

PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=test_XXXXXXXX
PAYPAL_SECRET=test_XXXXXXXX

# Disable external APIs durante tests
MAIL_MAILER=log
QUEUE_CONNECTION=sync
```

### Configurar PHPUnit

El archivo `phpunit.xml` ya está configurado:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit ...>
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

### Migrar Base de Datos de Testing

```bash
# Si usas MySQL
php artisan migrate --env=testing

# Si usas SQLite en memoria, se crea automáticamente
```

---

## Estructura de Tests

```
tests/
├── CreatesApplication.php         # Trait para bootstrapping
├── TestCase.php                    # Clase base para todos los tests
│
├── Unit/                           # Tests unitarios
│   ├── ExampleTest.php
│   ├── PaymentGatewayManagerTest.php
│   └── Services/
│       ├── CustomerServiceTest.php
│       ├── PlanServiceTest.php
│       └── SubscriptionServiceTest.php
│
└── Feature/                        # Tests de integración
    ├── AuthenticationTest.php
    ├── CustomerManagementTest.php
    ├── SubscriptionFlowTest.php
    └── WebhookTest.php
```

### Diferencia entre Unit y Feature

| Tipo | Propósito | Características |
|------|-----------|-----------------|
| **Unit** | Probar unidades aisladas | Mock de dependencias, sin BD, rápidos |
| **Feature** | Probar flujos completos | Usa BD real, HTTP requests, más lentos |

---

## Ejecutar Tests

### Comandos Básicos

```bash
# Ejecutar todos los tests
php artisan test

# O con PHPUnit directamente
./vendor/bin/phpunit
```

### Tests Específicos

```bash
# Ejecutar solo tests unitarios
php artisan test --testsuite=Unit

# Ejecutar solo tests de feature
php artisan test --testsuite=Feature

# Ejecutar un archivo específico
php artisan test tests/Unit/PaymentGatewayManagerTest.php

# Ejecutar un test específico
php artisan test --filter test_can_register_gateway

# Ejecutar tests de una clase
php artisan test --filter PaymentGatewayManagerTest
```

### Con Cobertura

```bash
# Generar reporte de cobertura (requiere Xdebug)
php artisan test --coverage

# Cobertura en HTML
php artisan test --coverage-html coverage
# Ver: coverage/index.html

# Cobertura mínima requerida
php artisan test --coverage --min=80
```

### Modo Verbose

```bash
# Ver output detallado
php artisan test --verbose

# Ver queries ejecutadas
php artisan test --verbose --env=testing
```

### Parallel Testing

```bash
# Ejecutar tests en paralelo (Laravel 8+)
php artisan test --parallel

# Especificar procesos
php artisan test --parallel --processes=4
```

---

## Tests Unitarios

### Ejemplo: PaymentGatewayManagerTest

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymentGatewayManager;
use App\Contracts\PaymentGatewayInterface;

class PaymentGatewayManagerTest extends TestCase
{
    protected PaymentGatewayManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(PaymentGatewayManager::class);
    }

    /** @test */
    public function it_can_get_available_gateways()
    {
        $gateways = $this->manager->getAvailableGateways();

        $this->assertIsArray($gateways);
        $this->assertContains('stripe', $gateways);
        $this->assertContains('paypal', $gateways);
    }

    /** @test */
    public function it_can_get_a_specific_gateway()
    {
        $stripe = $this->manager->gateway('stripe');

        $this->assertInstanceOf(PaymentGatewayInterface::class, $stripe);
        $this->assertEquals('stripe', $stripe->getGatewayName());
    }

    /** @test */
    public function it_throws_exception_for_invalid_gateway()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('no está registrada');

        $this->manager->gateway('invalid_gateway');
    }

    /** @test */
    public function it_can_check_if_gateway_exists()
    {
        $this->assertTrue($this->manager->hasGateway('stripe'));
        $this->assertFalse($this->manager->hasGateway('nonexistent'));
    }
}
```

### Ejemplo: CustomerServiceTest (con Mocking)

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CustomerService;
use App\Services\PaymentGatewayManager;
use App\Contracts\PaymentGatewayInterface;
use App\Models\User;
use Mockery;

class CustomerServiceTest extends TestCase
{
    /** @test */
    public function it_can_create_customer()
    {
        // Mock del gateway
        $mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $mockGateway->shouldReceive('createCustomer')
            ->once()
            ->with(Mockery::on(function ($data) {
                return isset($data['email']) && isset($data['name']);
            }))
            ->andReturn([
                'success' => true,
                'gateway_customer_id' => 'cus_test_123',
            ]);

        // Mock del manager
        $mockManager = Mockery::mock(PaymentGatewayManager::class);
        $mockManager->shouldReceive('gateway')
            ->with('stripe')
            ->andReturn($mockGateway);

        // Inyectar mock
        $this->app->instance(PaymentGatewayManager::class, $mockManager);

        // Test
        $user = User::factory()->create();
        $service = app(CustomerService::class);

        $customer = $service->createCustomer(
            user: $user,
            gateway: 'stripe'
        );

        $this->assertEquals('stripe', $customer->gateway);
        $this->assertEquals('cus_test_123', $customer->gateway_customer_id);
        $this->assertEquals($user->id, $customer->user_id);
    }

    /** @test */
    public function it_prevents_duplicate_customers()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ya tiene un cliente');

        $user = User::factory()->create();
        $service = app(CustomerService::class);

        // Crear primer cliente (mock)
        // ... setup mock ...

        // Intentar crear segundo cliente
        $service->createCustomer($user, 'stripe');
        $service->createCustomer($user, 'stripe'); // Debe fallar
    }
}
```

---

## Tests de Integración

### Ejemplo: SubscriptionFlowTest

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PaymentCustomer;
use App\Models\PaymentPlan;
use App\Services\{CustomerService, PlanService, SubscriptionService};
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_complete_subscription_flow()
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $customerService = app(CustomerService::class);
        $planService = app(PlanService::class);
        $subscriptionService = app(SubscriptionService::class);

        // Act - Crear cliente
        $customer = $customerService->createCustomer(
            user: $user,
            gateway: 'stripe'
        );

        // Act - Crear plan
        $plan = $planService->createPlan(
            gateway: 'stripe',
            name: 'Test Plan',
            amount: 999,
            currency: 'usd',
            interval: 'month'
        );

        // Act - Crear suscripción
        $subscription = $subscriptionService->createSubscription(
            customer: $customer,
            plan: $plan
        );

        // Assert
        $this->assertDatabaseHas('payment_customers', [
            'user_id' => $user->id,
            'gateway' => 'stripe',
        ]);

        $this->assertDatabaseHas('payment_plans', [
            'name' => 'Test Plan',
            'amount' => 999,
        ]);

        $this->assertDatabaseHas('payment_subscriptions', [
            'payment_customer_id' => $customer->id,
            'payment_plan_id' => $plan->id,
        ]);

        $this->assertEquals('active', $subscription->status);
    }

    /** @test */
    public function cannot_create_subscription_with_mismatched_gateways()
    {
        $this->expectException(\Exception::class);

        $user = User::factory()->create();

        // Cliente en Stripe
        $stripeCustomer = PaymentCustomer::factory()->create([
            'user_id' => $user->id,
            'gateway' => 'stripe',
        ]);

        // Plan en PayPal
        $paypalPlan = PaymentPlan::factory()->create([
            'gateway' => 'paypal',
        ]);

        // Debe fallar
        $service = app(SubscriptionService::class);
        $service->createSubscription(
            customer: $stripeCustomer,
            plan: $paypalPlan
        );
    }
}
```

### Ejemplo: WebhookTest

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PaymentWebhook;
use App\Models\PaymentSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_handles_stripe_webhook()
    {
        $payload = [
            'id' => 'evt_test_123',
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => 'sub_123',
                    'status' => 'active',
                ]
            ]
        ];

        $response = $this->postJson('/webhooks/stripe', $payload, [
            'Stripe-Signature' => $this->generateStripeSignature($payload)
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payment_webhooks', [
            'gateway' => 'stripe',
            'event_type' => 'customer.subscription.updated',
            'event_id' => 'evt_test_123',
        ]);
    }

    private function generateStripeSignature($payload)
    {
        // Generar firma válida para testing
        $timestamp = time();
        $secret = config('services.stripe.webhook_secret');
        $signedPayload = $timestamp . '.' . json_encode($payload);
        $signature = hash_hmac('sha256', $signedPayload, $secret);

        return "t={$timestamp},v1={$signature}";
    }
}
```

---

## Crear Nuevos Tests

### Comando Artisan

```bash
# Test unitario
php artisan make:test Unit/MyServiceTest --unit

# Test de feature
php artisan make:test Feature/MyFeatureTest
```

### Template de Test Unitario

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;

class MyServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Inicializar servicio, mocks, etc.
        $this->service = app(MyService::class);
    }

    /** @test */
    public function it_does_something()
    {
        // Arrange - Preparar datos
        $input = 'test';

        // Act - Ejecutar acción
        $result = $this->service->doSomething($input);

        // Assert - Verificar resultado
        $this->assertEquals('expected', $result);
    }

    protected function tearDown(): void
    {
        // Cleanup
        Mockery::close();
        parent::tearDown();
    }
}
```

### Template de Test de Feature

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_do_something()
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $response = $this->post('/api/endpoint', [
            'data' => 'value'
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('table', [
            'user_id' => $user->id,
        ]);
    }
}
```

---

## Mocking de Pasarelas

### Mockear Gateway Completo

```php
use App\Contracts\PaymentGatewayInterface;
use Mockery;

$mockGateway = Mockery::mock(PaymentGatewayInterface::class);

// Configurar expectativas
$mockGateway->shouldReceive('createCustomer')
    ->once()
    ->andReturn([
        'success' => true,
        'gateway_customer_id' => 'mock_123',
    ]);

$mockGateway->shouldReceive('getGatewayName')
    ->andReturn('mock_gateway');

// Usar mock
$this->app->instance(PaymentGatewayInterface::class, $mockGateway);
```

### Mockear SDK de Stripe

```php
use Stripe\StripeClient;
use Mockery;

$mockStripe = Mockery::mock(StripeClient::class);

$mockStripe->customers = Mockery::mock();
$mockStripe->customers->shouldReceive('create')
    ->andReturn((object)[
        'id' => 'cus_mock_123',
        'email' => 'test@example.com',
    ]);

// Inyectar en gateway
// Nota: Requiere refactorizar gateway para aceptar cliente inyectado
```

### Usar Factories

```php
// database/factories/PaymentCustomerFactory.php
use App\Models\PaymentCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentCustomerFactory extends Factory
{
    protected $model = PaymentCustomer::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'gateway' => 'stripe',
            'gateway_customer_id' => 'cus_' . $this->faker->uuid,
            'email' => $this->faker->email,
            'name' => $this->faker->name,
        ];
    }
}

// Usar en tests
$customer = PaymentCustomer::factory()->create([
    'gateway' => 'paypal'
]);
```

---

## Best Practices

### ✅ Hacer

- ✅ Usar `RefreshDatabase` en tests de integración
- ✅ Mockear servicios externos (Stripe, PayPal)
- ✅ Escribir tests antes de refactorizar
- ✅ Un assert por test (cuando sea posible)
- ✅ Nombres descriptivos: `test_user_can_create_subscription`
- ✅ Arrange-Act-Assert pattern
- ✅ Limpiar mocks: `Mockery::close()`

### ❌ Evitar

- ❌ Tests que dependen de orden de ejecución
- ❌ Hacer llamadas reales a APIs en tests
- ❌ Tests sin asserts
- ❌ Datos hardcodeados (usar factories)
- ❌ Tests muy largos (>50 líneas)
- ❌ Tests que modifican configuración global

### Cobertura Objetivo

| Componente | Cobertura Mínima |
|------------|------------------|
| **Services** | 90% |
| **Gateways** | 85% |
| **Models** | 70% |
| **Controllers** | 80% |
| **Global** | 80% |

### Ejecutar Tests en CI/CD

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.0
          extensions: mbstring, xml, curl
      
      - name: Install Dependencies
        run: composer install
      
      - name: Run Tests
        run: php artisan test --coverage --min=80
```

---

## Comandos Útiles

```bash
# Crear test
php artisan make:test MyTest

# Ejecutar tests
php artisan test

# Con cobertura
php artisan test --coverage

# Test específico
php artisan test --filter MyTest

# En paralelo
php artisan test --parallel

# Recrear BD de testing
php artisan migrate:fresh --env=testing

# Ver configuración de testing
php artisan config:show --env=testing
```

---

## Próximos Pasos

Ahora que sabes cómo testear:

1. **Revisa buenas prácticas**: [09-BUENAS-PRACTICAS.md](09-BUENAS-PRACTICAS.md)
2. **Soluciona problemas**: [10-TROUBLESHOOTING.md](10-TROUBLESHOOTING.md)

---

[⬅️ Anterior: Webhooks](07-WEBHOOKS.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Buenas Prácticas](09-BUENAS-PRACTICAS.md)
