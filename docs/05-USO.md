# 🚀 Guía de Uso del Sistema

[⬅️ Anterior: Servicios](04-SERVICIOS.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Extensiones](06-EXTENSIONES.md)

---

## 📋 Tabla de Contenidos

- [Iniciar el Servidor](#iniciar-el-servidor)
- [Crear Usuarios de Prueba](#crear-usuarios-de-prueba)
- [Flujo Completo de Usuario](#flujo-completo-de-usuario)
- [Uso del Dashboard](#uso-del-dashboard)
- [Comandos Artisan Personalizados](#comandos-artisan-personalizados)
- [Uso de Tinker para Pruebas](#uso-de-tinker-para-pruebas)
- [Tarjetas de Prueba](#tarjetas-de-prueba)
- [Ejemplos Prácticos](#ejemplos-prácticos)

---

## Iniciar el Servidor

### Servidor de Desarrollo

```bash
# Iniciar servidor Laravel
php artisan serve

# Servidor estará disponible en:
# http://127.0.0.1:8000
```

### Con puerto personalizado

```bash
php artisan serve --port=8080

# Disponible en: http://127.0.0.1:8080
```

### Con host específico (acceso desde red local)

```bash
php artisan serve --host=0.0.0.0 --port=8000

# Accesible desde: http://tu-ip-local:8000
```

### Compilar Assets (en otra terminal)

```bash
# Desarrollo con watch (recompila automáticamente)
npm run dev

# O para producción
npm run build
```

---

## Crear Usuarios de Prueba

### Opción 1: Registro Manual

1. **Visitar**: http://127.0.0.1:8000/register
2. **Completar** formulario de registro
3. **Verificar** email (si tienes mail configurado)
4. **Acceder** al dashboard

### Opción 2: Usar Tinker

```bash
php artisan tinker
```

```php
// Crear usuario de prueba
use App\Models\User;

$user = User::create([
    'name' => 'Usuario de Prueba',
    'email' => 'test@example.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now()
]);

echo "Usuario creado con ID: " . $user->id;
```

### Opción 3: Usar Seeders

Si tienes seeders configurados:

```bash
php artisan db:seed --class=UserSeeder
```

### Opción 4: Factory en Tinker

```php
use App\Models\User;

// Crear 1 usuario
$user = User::factory()->create([
    'email' => 'admin@example.com'
]);

// Crear 5 usuarios aleatorios
User::factory(5)->create();
```

---

## Flujo Completo de Usuario

### Paso 1: Login

1. Visitar http://127.0.0.1:8000/login
2. Credenciales:
   - Email: `test@example.com`
   - Password: `password123`

### Paso 2: Crear Cliente en Pasarela

**Opción A: Desde Dashboard UI**

1. Dashboard → "Crear Cliente"
2. Seleccionar pasarela (Stripe o PayPal)
3. Click "Crear Cliente"
4. Ver confirmación: "Cliente creado exitosamente"

**Opción B: Con Tinker**

```php
use App\Services\CustomerService;

$customerService = app(CustomerService::class);

$customer = $customerService->createCustomer(
    user: User::find(1),
    gateway: 'stripe'
);
```

### Paso 3: Agregar Tarjeta (Solo Stripe)

1. Dashboard → "Gestionar Tarjetas"
2. Usar tarjeta de prueba: `4242 4242 4242 4242`
3. CVV: `123`
4. Expiración: Cualquier fecha futura (ej: `12/25`)
5. Click "Agregar Tarjeta"

### Paso 4: Crear Plan de Suscripción

**Opción A: Desde Dashboard UI**

1. Dashboard → "Crear Plan"
2. Completar formulario:
   - Nombre: "Plan Premium"
   - Precio: 29.99
   - Moneda: USD
   - Intervalo: Mensual
   - Pasarela: Stripe
3. Click "Crear Plan"

**Opción B: Con Tinker**

```php
use App\Services\PlanService;

$planService = app(PlanService::class);

$plan = $planService->createPlan(
    gateway: 'stripe',
    name: 'Plan Premium',
    amount: 2999,  // $29.99 en centavos
    currency: 'usd',
    interval: 'month'
);
```

### Paso 5: Crear Suscripción

1. Dashboard → "Crear Suscripción"
2. Seleccionar cliente
3. Seleccionar plan
4. Click "Suscribirse"
5. Ver confirmación con detalles de la suscripción

### Paso 6: Verificar Suscripción

**En Dashboard:**
- Ver estado: "Activa"
- Ver próximo cobro
- Ver detalles del plan

**En Stripe Dashboard:**
1. Ir a https://dashboard.stripe.com/test/subscriptions
2. Ver suscripción creada
3. Verificar cliente y detalles

---

## Uso del Dashboard

### Vista Principal

```
┌──────────────────────────────────────────┐
│  Multi-Payment Platforms                 │
├──────────────────────────────────────────┤
│                                          │
│  📊 Dashboard                            │
│                                          │
│  ┌────────────┐  ┌────────────┐        │
│  │ Clientes   │  │ Tarjetas   │        │
│  │     2      │  │     3      │        │
│  └────────────┘  └────────────┘        │
│                                          │
│  ┌────────────┐  ┌────────────┐        │
│  │ Planes     │  │Suscripciones│        │
│  │     5      │  │     1      │        │
│  └────────────┘  └────────────┘        │
│                                          │
│  📋 Acciones Rápidas                     │
│  • Crear Cliente                         │
│  • Gestionar Tarjetas                    │
│  • Crear Plan                            │
│  • Nueva Suscripción                     │
│  • Generar Link de Pago                  │
│                                          │
└──────────────────────────────────────────┘
```

### Componentes Disponibles

#### 1. Crear Cliente (`/dashboard`)

- **Ubicación**: Livewire Component `CreateCustomer`
- **Vista**: `resources/views/livewire/create-customer.blade.php`
- **Funcionalidad**: Crea clientes en Stripe o PayPal

#### 2. Gestionar Tarjetas (`/dashboard`)

- **Ubicación**: Livewire Component `ManageCards`
- **Vista**: `resources/views/livewire/manage-cards.blade.php`
- **Funcionalidad**: 
  - Listar tarjetas
  - Agregar nuevas tarjetas
  - Establecer tarjeta predeterminada
  - Eliminar tarjetas

#### 3. Crear Plan (`/dashboard`)

- **Ubicación**: Livewire Component `CreatePlan`
- **Vista**: `resources/views/livewire/create-plan.blade.php`
- **Funcionalidad**: Crear planes de suscripción

#### 4. Crear Suscripción (`/dashboard`)

- **Ubicación**: Livewire Component `CreateSubscription`
- **Vista**: `resources/views/livewire/create-subscription.blade.php`
- **Funcionalidad**: Suscribir clientes a planes

#### 5. Generar Link de Pago (`/dashboard`)

- **Ubicación**: Livewire Component `CreatePaymentLink`
- **Vista**: `resources/views/livewire/create-payment-link.blade.php`
- **Funcionalidad**: Generar links de pago únicos

---

## Comandos Artisan Personalizados

### Test Payment Gateways

Prueba la conectividad con las pasarelas.

```bash
php artisan test:payment-gateways
```

**Salida esperada:**
```
Testing Payment Gateways...
✓ Stripe gateway is available
✓ PayPal gateway is available

Testing Stripe connection...
✓ Stripe API is reachable
✓ Stripe credentials are valid

Testing PayPal connection...
✓ PayPal API is reachable
✓ PayPal credentials are valid

All tests passed!
```

### Listar Rutas

```bash
# Ver todas las rutas
php artisan route:list

# Filtrar rutas específicas
php artisan route:list --name=dashboard
php artisan route:list --path=webhook
```

### Limpiar Caché

```bash
# Limpiar todo el caché
php artisan optimize:clear

# O específicamente
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Logs

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Filtrar errores
tail -f storage/logs/laravel.log | grep ERROR

# Filtrar webhooks
tail -f storage/logs/laravel.log | grep webhook
```

---

## Uso de Tinker para Pruebas

Tinker es una REPL interactiva de Laravel. Útil para pruebas rápidas.

### Iniciar Tinker

```bash
php artisan tinker
```

### Ejemplos de Uso

#### Verificar Pasarelas Disponibles

```php
use App\Services\PaymentGatewayManager;

$manager = app(PaymentGatewayManager::class);
$manager->getAvailableGateways();

// Salida: ['stripe', 'paypal']
```

#### Crear Cliente Completo

```php
use App\Models\User;
use App\Services\CustomerService;

$user = User::find(1);
$customerService = app(CustomerService::class);

$customer = $customerService->createCustomer(
    user: $user,
    gateway: 'stripe',
    additionalData: [
        'metadata' => ['source' => 'tinker']
    ]
);

$customer->gateway_customer_id;
// Salida: "cus_XXXXXXXXXX"
```

#### Listar Clientes de un Usuario

```php
use App\Models\User;

$user = User::find(1);
$customers = $user->paymentCustomers;

foreach ($customers as $customer) {
    echo "{$customer->gateway}: {$customer->gateway_customer_id}\n";
}
```

#### Crear Suscripción Completa

```php
use App\Services\{CustomerService, PlanService, SubscriptionService};
use App\Models\User;

$user = User::find(1);

// 1. Cliente (si no existe)
$customerService = app(CustomerService::class);
$customer = $user->paymentCustomers()->where('gateway', 'stripe')->first()
    ?? $customerService->createCustomer($user, 'stripe');

// 2. Plan (si no existe)
$planService = app(PlanService::class);
$plan = \App\Models\PaymentPlan::where('name', 'Test Plan')->first()
    ?? $planService->createPlan('stripe', 'Test Plan', 999, 'usd', 'month');

// 3. Suscripción
$subscriptionService = app(SubscriptionService::class);
$subscription = $subscriptionService->createSubscription($customer, $plan);

echo "Suscripción creada: {$subscription->status}\n";
echo "ID: {$subscription->gateway_subscription_id}\n";
```

#### Verificar Webhooks Recibidos

```php
use App\Models\PaymentWebhook;

$webhooks = PaymentWebhook::latest()->take(5)->get();

foreach ($webhooks as $webhook) {
    echo "{$webhook->gateway} - {$webhook->event_type} - {$webhook->created_at}\n";
}
```

#### Simular Evento de Webhook

```php
use App\Models\PaymentWebhook;

$webhook = PaymentWebhook::create([
    'gateway' => 'stripe',
    'event_type' => 'customer.subscription.updated',
    'event_id' => 'evt_test_' . uniqid(),
    'payload' => json_encode(['test' => true]),
    'processed' => false
]);

echo "Webhook creado con ID: {$webhook->id}\n";
```

---

## Tarjetas de Prueba

### Stripe Test Cards

| Número | Tipo | Comportamiento |
|--------|------|----------------|
| `4242 4242 4242 4242` | Visa | ✅ Éxito |
| `4000 0025 0000 3155` | Visa | ✅ Requiere 3D Secure |
| `5555 5555 5555 4444` | Mastercard | ✅ Éxito |
| `3782 822463 10005` | Amex | ✅ Éxito |
| `4000 0000 0000 9995` | Visa | ❌ Fondos insuficientes |
| `4000 0000 0000 0002` | Visa | ❌ Tarjeta declinada |
| `4000 0000 0000 0341` | Visa | ❌ Tarjeta adjuntada falla |

**Datos adicionales para todas:**
- CVV: Cualquier 3 dígitos (ej: `123`)
- Fecha: Cualquier futura (ej: `12/25`)
- ZIP: Cualquier (ej: `12345`)

### PayPal Test Accounts

PayPal proporciona cuentas de prueba en el Sandbox:

1. Ir a https://developer.paypal.com/developer/accounts/
2. Usar credenciales de:
   - **Personal Account** (comprador)
   - **Business Account** (vendedor)

**Ejemplo:**
- Email: `sb-buyer@personal.example.com`
- Password: `(generado por PayPal)`

---

## Ejemplos Prácticos

### Ejemplo 1: Onboarding Completo de Usuario

```php
// En un Controller o Service
use App\Services\{CustomerService, CardService};
use App\Models\User;

public function onboardUser(User $user, string $stripeToken)
{
    try {
        // 1. Crear cliente en Stripe
        $customerService = app(CustomerService::class);
        $customer = $customerService->createCustomer(
            user: $user,
            gateway: 'stripe'
        );
        
        // 2. Agregar tarjeta
        $cardService = app(CardService::class);
        $card = $cardService->createCard(
            customer: $customer,
            tokenOrCardData: $stripeToken
        );
        
        // 3. Suscribir a plan básico
        $basicPlan = \App\Models\PaymentPlan::where('name', 'Basic')->first();
        
        if ($basicPlan) {
            $subscriptionService = app(SubscriptionService::class);
            $subscription = $subscriptionService->createSubscription(
                customer: $customer,
                plan: $basicPlan
            );
        }
        
        return [
            'success' => true,
            'message' => 'Usuario configurado exitosamente',
            'customer' => $customer,
            'subscription' => $subscription ?? null
        ];
        
    } catch (\Exception $e) {
        \Log::error('Error en onboarding: ' . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Error en la configuración: ' . $e->getMessage()
        ];
    }
}
```

### Ejemplo 2: Cambiar Plan de Suscripción

```php
use App\Services\SubscriptionService;
use App\Models\{PaymentSubscription, PaymentPlan};

public function upgradePlan($subscriptionId, $newPlanId)
{
    $subscription = PaymentSubscription::findOrFail($subscriptionId);
    $newPlan = PaymentPlan::findOrFail($newPlanId);
    
    // Verificar que sea de la misma pasarela
    if ($subscription->gateway !== $newPlan->gateway) {
        return response()->json([
            'error' => 'Plan debe ser de la misma pasarela'
        ], 400);
    }
    
    $subscriptionService = app(SubscriptionService::class);
    
    try {
        $updated = $subscriptionService->updateSubscription(
            subscription: $subscription,
            newPlan: $newPlan
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Plan actualizado exitosamente',
            'subscription' => $updated
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
```

### Ejemplo 3: Generar Factura con Link de Pago

```php
use App\Services\PaymentLinkService;
use Illuminate\Support\Facades\Mail;

public function generateInvoice($userId, $amount, $description)
{
    $user = User::findOrFail($userId);
    $linkService = app(PaymentLinkService::class);
    
    // Crear link de pago
    $link = $linkService->createPaymentLink(
        user: $user,
        gateway: 'stripe',
        amount: $amount * 100, // Convertir a centavos
        currency: 'usd',
        additionalData: [
            'description' => $description,
            'metadata' => [
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString()
            ]
        ]
    );
    
    // Enviar por email
    Mail::to($user->email)->send(
        new \App\Mail\InvoiceEmail($link, $description, $amount)
    );
    
    return $link;
}
```

### Ejemplo 4: Dashboard de Suscripciones

```php
// En un Controller
use App\Models\PaymentSubscription;

public function dashboard()
{
    $user = auth()->user();
    
    // Obtener todas las suscripciones activas
    $subscriptions = PaymentSubscription::whereHas('paymentCustomer', function($q) use ($user) {
        $q->where('user_id', $user->id);
    })
    ->where('status', 'active')
    ->with(['paymentPlan', 'paymentCustomer'])
    ->get();
    
    // Estadísticas
    $stats = [
        'total_subscriptions' => $subscriptions->count(),
        'monthly_cost' => $subscriptions->sum(function($sub) {
            return $sub->paymentPlan->amount / 100;
        }),
        'next_billing' => $subscriptions->min('current_period_end'),
    ];
    
    return view('dashboard', compact('subscriptions', 'stats'));
}
```

### Ejemplo 5: Cancelar Todas las Suscripciones

```php
use App\Services\SubscriptionService;
use App\Models\PaymentSubscription;

public function cancelAllSubscriptions($userId)
{
    $subscriptionService = app(SubscriptionService::class);
    
    $subscriptions = PaymentSubscription::whereHas('paymentCustomer', function($q) use ($userId) {
        $q->where('user_id', $userId);
    })
    ->whereIn('status', ['active', 'trialing'])
    ->get();
    
    $results = [];
    
    foreach ($subscriptions as $subscription) {
        try {
            $subscriptionService->cancelSubscription($subscription);
            $results[] = [
                'id' => $subscription->id,
                'status' => 'canceled',
                'success' => true
            ];
        } catch (\Exception $e) {
            $results[] = [
                'id' => $subscription->id,
                'error' => $e->getMessage(),
                'success' => false
            ];
        }
    }
    
    return $results;
}
```

---

## Tips y Trucos

### 💡 Usar Logs para Debugging

```php
// En cualquier parte de tu código
\Log::info('Customer created', [
    'customer_id' => $customer->id,
    'gateway' => $customer->gateway
]);

// Ver logs
tail -f storage/logs/laravel.log
```

### 💡 Modo Mantenimiento

```bash
# Activar
php artisan down --message="Actualizando sistema" --retry=60

# Desactivar
php artisan up
```

### 💡 Queue para Webhooks (Opcional)

```php
// En .env
QUEUE_CONNECTION=database

// Crear tabla de jobs
php artisan queue:table
php artisan migrate

// Procesar queue
php artisan queue:work

// Ver trabajos fallidos
php artisan queue:failed
```

---

## Próximos Pasos

Ahora que sabes usar el sistema:

1. **Agrega una nueva pasarela**: [06-EXTENSIONES.md](06-EXTENSIONES.md)
2. **Configura webhooks**: [07-WEBHOOKS.md](07-WEBHOOKS.md)
3. **Ejecuta tests**: [08-TESTING.md](08-TESTING.md)

---

[⬅️ Anterior: Servicios](04-SERVICIOS.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Extensiones](06-EXTENSIONES.md)
