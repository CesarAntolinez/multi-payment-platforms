# 🔧 API de Servicios

[⬅️ Anterior: Estructura](03-ESTRUCTURA.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Uso](05-USO.md)

---

## 📋 Tabla de Contenidos

- [Introducción a los Servicios](#introducción-a-los-servicios)
- [CustomerService](#customerservice)
- [CardService](#cardservice)
- [PlanService](#planservice)
- [SubscriptionService](#subscriptionservice)
- [PaymentLinkService](#paymentlinkservice)
- [PaymentGatewayManager](#paymentgatewaymanager)
- [Manejo de Errores](#manejo-de-errores)
- [Ejemplos Completos](#ejemplos-completos)

---

## Introducción a los Servicios

Los **servicios** son la capa de lógica de negocio de la aplicación. Cada servicio encapsula operaciones específicas relacionadas con pagos, abstrayendo la complejidad de interactuar con múltiples pasarelas.

### 🎯 Características

- ✅ **Unificados**: Una API consistente para todas las pasarelas
- ✅ **Transaccionales**: Usan DB transactions para integridad
- ✅ **Validados**: Verifican datos antes de enviar a pasarelas
- ✅ **Singleton**: Registrados como singletons en el contenedor
- ✅ **Inyectables**: Usa Dependency Injection

### 📍 Ubicación

Todos los servicios están en: [`app/Services/`](../app/Services/)

### 🔄 Acceso a Servicios

```php
// Opción 1: Usar el contenedor de Laravel
$customerService = app(CustomerService::class);

// Opción 2: Inyección de dependencias (recomendado)
class MiController extends Controller
{
    public function __construct(
        private CustomerService $customerService
    ) {}
    
    public function crearCliente()
    {
        $this->customerService->createCustomer(...);
    }
}

// Opción 3: Facade helper (si lo prefieres)
use App\Services\CustomerService;

$service = app(CustomerService::class);
```

---

## CustomerService

Gestión de clientes en las pasarelas de pago.

**Ubicación**: [`app/Services/CustomerService.php`](../app/Services/CustomerService.php)

### Métodos Disponibles

#### `createCustomer()`

Crea un cliente en una pasarela de pago.

**Firma:**
```php
public function createCustomer(
    User $user, 
    string $gateway, 
    array $additionalData = []
): PaymentCustomer
```

**Parámetros:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `$user` | `User` | Usuario para el cual crear el cliente |
| `$gateway` | `string` | Pasarela ('stripe', 'paypal') |
| `$additionalData` | `array` | Datos adicionales (metadata, etc.) |

**Retorna:** `PaymentCustomer` - Cliente creado

**Excepciones:**
- `Exception` - Si el usuario ya tiene cliente en esa pasarela
- `Exception` - Si falla la creación en la pasarela

**Ejemplo:**
```php
use App\Services\CustomerService;

$customerService = app(CustomerService::class);

try {
    $customer = $customerService->createCustomer(
        user: auth()->user(),
        gateway: 'stripe',
        additionalData: [
            'metadata' => [
                'plan' => 'premium',
                'source' => 'web'
            ]
        ]
    );
    
    echo "Cliente creado: " . $customer->gateway_customer_id;
    // Salida: Cliente creado: cus_XXXXXXXXXX
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### `updateCustomer()`

Actualiza información de un cliente existente.

**Firma:**
```php
public function updateCustomer(
    PaymentCustomer $customer, 
    array $updateData
): PaymentCustomer
```

**Parámetros:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `$customer` | `PaymentCustomer` | Cliente a actualizar |
| `$updateData` | `array` | Datos a actualizar (email, name, metadata) |

**Retorna:** `PaymentCustomer` - Cliente actualizado

**Ejemplo:**
```php
$customer = PaymentCustomer::find(1);

$updatedCustomer = $customerService->updateCustomer(
    customer: $customer,
    updateData: [
        'name' => 'Juan Pérez Actualizado',
        'email' => 'nuevo@email.com',
        'metadata' => [
            'company' => 'Acme Corp'
        ]
    ]
);
```

#### `getCustomer()`

Obtiene un cliente por usuario y pasarela.

**Firma:**
```php
public function getCustomer(User $user, string $gateway): ?PaymentCustomer
```

**Ejemplo:**
```php
$customer = $customerService->getCustomer(
    user: auth()->user(),
    gateway: 'stripe'
);

if ($customer) {
    echo "Cliente ID: " . $customer->gateway_customer_id;
} else {
    echo "Usuario no tiene cliente en Stripe";
}
```

---

## CardService

Gestión de tarjetas de pago (métodos de pago).

**Ubicación**: [`app/Services/CardService.php`](../app/Services/CardService.php)

### Métodos Disponibles

#### `createCard()`

Agrega una tarjeta a un cliente.

**Firma:**
```php
public function createCard(
    PaymentCustomer $customer, 
    string $tokenOrCardData
): PaymentCard
```

**Parámetros:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `$customer` | `PaymentCustomer` | Cliente al que agregar la tarjeta |
| `$tokenOrCardData` | `string` | Token de tarjeta o ID de método de pago |

**Retorna:** `PaymentCard` - Tarjeta creada

**Ejemplo (Stripe con token):**
```php
use App\Services\CardService;

$cardService = app(CardService::class);

// Token generado desde el frontend con Stripe.js
$stripeToken = 'tok_visa'; // o token real: tok_1XXXXX

$card = $cardService->createCard(
    customer: $customer,
    tokenOrCardData: $stripeToken
);

echo "Tarjeta agregada: •••• " . $card->last_four;
// Salida: Tarjeta agregada: •••• 4242
```

**Ejemplo (desde frontend con Stripe.js):**
```javascript
// Frontend: resources/js/components/card-form.js
const stripe = Stripe('pk_test_XXXX');
const cardElement = elements.create('card');

// Al hacer submit
const {token, error} = await stripe.createToken(cardElement);

if (!error) {
    // Enviar token.id al backend
    axios.post('/api/cards', {
        token: token.id,
        customer_id: customerId
    });
}
```

#### `listCards()`

Lista todas las tarjetas de un cliente.

**Firma:**
```php
public function listCards(PaymentCustomer $customer): Collection
```

**Ejemplo:**
```php
$cards = $cardService->listCards($customer);

foreach ($cards as $card) {
    echo "{$card->brand} •••• {$card->last_four} - Exp: {$card->exp_month}/{$card->exp_year}\n";
}

// Salida:
// Visa •••• 4242 - Exp: 12/2025
// Mastercard •••• 5555 - Exp: 08/2026
```

#### `setDefaultCard()`

Establece una tarjeta como predeterminada.

**Firma:**
```php
public function setDefaultCard(PaymentCard $card): PaymentCard
```

**Ejemplo:**
```php
$card = PaymentCard::find(5);

$cardService->setDefaultCard($card);

echo "Tarjeta predeterminada actualizada";
```

#### `deleteCard()`

Elimina una tarjeta.

**Firma:**
```php
public function deleteCard(PaymentCard $card): bool
```

**Ejemplo:**
```php
$card = PaymentCard::find(3);

if ($cardService->deleteCard($card)) {
    echo "Tarjeta eliminada exitosamente";
}
```

---

## PlanService

Gestión de planes de suscripción.

**Ubicación**: [`app/Services/PlanService.php`](../app/Services/PlanService.php)

### Métodos Disponibles

#### `createPlan()`

Crea un plan de suscripción.

**Firma:**
```php
public function createPlan(
    string $gateway,
    string $name,
    int $amount,
    string $currency,
    string $interval,
    array $additionalData = []
): PaymentPlan
```

**Parámetros:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `$gateway` | `string` | Pasarela ('stripe', 'paypal') |
| `$name` | `string` | Nombre del plan |
| `$amount` | `int` | Precio en centavos (ej: 2999 = $29.99) |
| `$currency` | `string` | Moneda (USD, EUR, COP, etc.) |
| `$interval` | `string` | Intervalo ('month', 'year') |
| `$additionalData` | `array` | Datos adicionales |

**Retorna:** `PaymentPlan` - Plan creado

**Ejemplo:**
```php
use App\Services\PlanService;

$planService = app(PlanService::class);

$plan = $planService->createPlan(
    gateway: 'stripe',
    name: 'Plan Premium',
    amount: 2999,      // $29.99
    currency: 'usd',
    interval: 'month',
    additionalData: [
        'metadata' => [
            'features' => 'unlimited',
            'support' => '24/7'
        ],
        'trial_period_days' => 14
    ]
);

echo "Plan creado: {$plan->name} - \${$plan->amount / 100}/{$plan->interval}";
// Salida: Plan creado: Plan Premium - $29.99/month
```

**Intervalos soportados:**
- `month` - Mensual
- `year` - Anual
- `week` - Semanal (solo Stripe)
- `day` - Diario (solo Stripe)

#### `updatePlan()`

Actualiza información de un plan.

**Firma:**
```php
public function updatePlan(
    PaymentPlan $plan, 
    array $updateData
): PaymentPlan
```

**Ejemplo:**
```php
$plan = PaymentPlan::find(1);

$updatedPlan = $planService->updatePlan(
    plan: $plan,
    updateData: [
        'name' => 'Plan Premium Plus',
        'metadata' => [
            'features' => 'unlimited_plus',
            'priority_support' => true
        ]
    ]
);
```

> ⚠️ **Nota**: No se puede cambiar el precio de un plan existente. Crea un nuevo plan si necesitas un precio diferente.

#### `listPlans()`

Lista todos los planes de una pasarela.

**Firma:**
```php
public function listPlans(string $gateway): Collection
```

**Ejemplo:**
```php
$stripePlans = $planService->listPlans('stripe');

foreach ($stripePlans as $plan) {
    echo "{$plan->name}: \${$plan->amount / 100}/{$plan->interval}\n";
}
```

---

## SubscriptionService

Gestión de suscripciones.

**Ubicación**: [`app/Services/SubscriptionService.php`](../app/Services/SubscriptionService.php)

### Métodos Disponibles

#### `createSubscription()`

Crea una suscripción para un cliente.

**Firma:**
```php
public function createSubscription(
    PaymentCustomer $customer,
    PaymentPlan $plan
): PaymentSubscription
```

**Parámetros:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `$customer` | `PaymentCustomer` | Cliente que se suscribe |
| `$plan` | `PaymentPlan` | Plan al que suscribirse |

**Retorna:** `PaymentSubscription` - Suscripción creada

**Excepciones:**
- `Exception` - Si cliente y plan son de pasarelas diferentes

**Ejemplo:**
```php
use App\Services\SubscriptionService;

$subscriptionService = app(SubscriptionService::class);

$customer = PaymentCustomer::where('user_id', auth()->id())
    ->where('gateway', 'stripe')
    ->first();
    
$plan = PaymentPlan::find(1);

$subscription = $subscriptionService->createSubscription(
    customer: $customer,
    plan: $plan
);

echo "Suscripción creada: " . $subscription->status;
echo "\nPróximo cobro: " . $subscription->current_period_end;
```

**Estados posibles:**
- `active` - Activa
- `trialing` - En período de prueba
- `past_due` - Pago vencido
- `canceled` - Cancelada
- `incomplete` - Incompleta (requiere acción)

#### `updateSubscription()`

Actualiza una suscripción (cambiar plan o cancelar).

**Firma:**
```php
public function updateSubscription(
    PaymentSubscription $subscription,
    PaymentPlan $newPlan = null,
    bool $cancel = false
): PaymentSubscription
```

**Ejemplo (cambiar plan):**
```php
$subscription = PaymentSubscription::find(1);
$newPlan = PaymentPlan::find(2); // Plan superior

$updatedSubscription = $subscriptionService->updateSubscription(
    subscription: $subscription,
    newPlan: $newPlan
);

echo "Plan actualizado a: " . $updatedSubscription->paymentPlan->name;
```

**Ejemplo (cancelar):**
```php
$subscription = PaymentSubscription::find(1);

$canceledSubscription = $subscriptionService->updateSubscription(
    subscription: $subscription,
    cancel: true
);

echo "Suscripción cancelada. Termina: " . $canceledSubscription->current_period_end;
```

#### `cancelSubscription()`

Cancela una suscripción (alias de updateSubscription con cancel=true).

**Firma:**
```php
public function cancelSubscription(PaymentSubscription $subscription): bool
```

**Ejemplo:**
```php
if ($subscriptionService->cancelSubscription($subscription)) {
    echo "Suscripción cancelada exitosamente";
}
```

---

## PaymentLinkService

Generación de links de pago únicos.

**Ubicación**: [`app/Services/PaymentLinkService.php`](../app/Services/PaymentLinkService.php)

### Métodos Disponibles

#### `createPaymentLink()`

Crea un link de pago.

**Firma:**
```php
public function createPaymentLink(
    User $user,
    string $gateway,
    int $amount,
    string $currency,
    array $additionalData = []
): PaymentLink
```

**Parámetros:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `$user` | `User` | Usuario creador del link |
| `$gateway` | `string` | Pasarela ('stripe', 'paypal') |
| `$amount` | `int` | Monto en centavos |
| `$currency` | `string` | Moneda |
| `$additionalData` | `array` | Descripción, metadata, etc. |

**Retorna:** `PaymentLink` - Link creado

**Ejemplo:**
```php
use App\Services\PaymentLinkService;

$linkService = app(PaymentLinkService::class);

$link = $linkService->createPaymentLink(
    user: auth()->user(),
    gateway: 'stripe',
    amount: 5000,  // $50.00
    currency: 'usd',
    additionalData: [
        'description' => 'Pago por consultoría',
        'metadata' => [
            'invoice_id' => 'INV-2024-001',
            'client' => 'Acme Corp'
        ]
    ]
);

echo "Link de pago: " . $link->url;
// Salida: Link de pago: https://buy.stripe.com/XXXXXXXXXX
```

**Compartir link:**
```php
// Enviar por email
Mail::to($cliente)->send(new PaymentLinkEmail($link));

// Mostrar QR
use SimpleSoftwareIO\QrCode\Facades\QrCode;
$qr = QrCode::size(300)->generate($link->url);

// Guardar para tracking
session(['payment_link_id' => $link->id]);
```

---

## PaymentGatewayManager

Manager central que administra todas las pasarelas.

**Ubicación**: [`app/Services/PaymentGatewayManager.php`](../app/Services/PaymentGatewayManager.php)

### Métodos Disponibles

#### `gateway()`

Obtiene una instancia de pasarela específica.

**Firma:**
```php
public function gateway(string $name): PaymentGatewayInterface
```

**Ejemplo:**
```php
use App\Services\PaymentGatewayManager;

$manager = app(PaymentGatewayManager::class);

// Obtener Stripe
$stripe = $manager->gateway('stripe');

// Obtener PayPal
$paypal = $manager->gateway('paypal');

// Usar directamente
$result = $stripe->createCustomer([
    'email' => 'test@example.com',
    'name' => 'Test User'
]);
```

#### `getAvailableGateways()`

Lista todas las pasarelas disponibles.

**Firma:**
```php
public function getAvailableGateways(): array
```

**Ejemplo:**
```php
$gateways = $manager->getAvailableGateways();
// Retorna: ['stripe', 'paypal']

foreach ($gateways as $gateway) {
    echo "Pasarela disponible: {$gateway}\n";
}
```

#### `hasGateway()`

Verifica si una pasarela está disponible.

**Firma:**
```php
public function hasGateway(string $name): bool
```

**Ejemplo:**
```php
if ($manager->hasGateway('stripe')) {
    echo "Stripe está disponible";
}

if (!$manager->hasGateway('mercadopago')) {
    echo "Mercado Pago no está disponible";
}
```

---

## Manejo de Errores

Todos los servicios lanzan excepciones cuando ocurren errores. Siempre envuelve las llamadas en try-catch.

### Tipos de Excepciones

```php
use App\Exceptions\PaymentGateway\CustomerException;
use App\Exceptions\PaymentGateway\GatewayException;
use App\Exceptions\PaymentGateway\PlanException;
use App\Exceptions\PaymentGateway\SubscriptionException;
```

### Ejemplo de Manejo Robusto

```php
use App\Services\CustomerService;
use Exception;

try {
    $customer = $customerService->createCustomer(
        user: auth()->user(),
        gateway: 'stripe'
    );
    
    return response()->json([
        'success' => true,
        'customer' => $customer
    ]);
    
} catch (Exception $e) {
    // Log del error
    \Log::error('Error creando cliente: ' . $e->getMessage(), [
        'user_id' => auth()->id(),
        'gateway' => 'stripe',
        'trace' => $e->getTraceAsString()
    ]);
    
    // Respuesta al usuario
    return response()->json([
        'success' => false,
        'error' => 'No se pudo crear el cliente. Por favor intenta nuevamente.'
    ], 500);
}
```

### Validación de Datos

```php
// Validar antes de llamar al servicio
$validated = $request->validate([
    'gateway' => 'required|in:stripe,paypal',
    'name' => 'required|string|max:255',
    'email' => 'required|email',
]);

try {
    $customer = $customerService->createCustomer(
        user: auth()->user(),
        gateway: $validated['gateway'],
        additionalData: [
            'metadata' => [
                'name' => $validated['name'],
            ]
        ]
    );
} catch (Exception $e) {
    // Manejar error...
}
```

---

## Ejemplos Completos

### Ejemplo 1: Flujo Completo de Suscripción

```php
use App\Services\{CustomerService, CardService, PlanService, SubscriptionService};

// 1. Crear cliente
$customerService = app(CustomerService::class);
$customer = $customerService->createCustomer(
    user: auth()->user(),
    gateway: 'stripe'
);

// 2. Agregar tarjeta
$cardService = app(CardService::class);
$card = $cardService->createCard(
    customer: $customer,
    tokenOrCardData: $request->stripe_token
);

// 3. Obtener o crear plan
$planService = app(PlanService::class);
$plan = PaymentPlan::where('gateway', 'stripe')
    ->where('name', 'Premium')
    ->first();

if (!$plan) {
    $plan = $planService->createPlan(
        gateway: 'stripe',
        name: 'Premium',
        amount: 2999,
        currency: 'usd',
        interval: 'month'
    );
}

// 4. Crear suscripción
$subscriptionService = app(SubscriptionService::class);
$subscription = $subscriptionService->createSubscription(
    customer: $customer,
    plan: $plan
);

return "¡Suscripción creada! Estado: {$subscription->status}";
```

### Ejemplo 2: Cambiar de Pasarela

```php
// Usuario tiene cliente en Stripe, quiere PayPal
$stripeCustomer = PaymentCustomer::where('user_id', auth()->id())
    ->where('gateway', 'stripe')
    ->first();

// Crear cliente en PayPal
$paypalCustomer = $customerService->createCustomer(
    user: auth()->user(),
    gateway: 'paypal'
);

// Crear plan equivalente en PayPal
$stripePlan = $stripeCustomer->subscriptions->first()->paymentPlan;

$paypalPlan = $planService->createPlan(
    gateway: 'paypal',
    name: $stripePlan->name,
    amount: $stripePlan->amount,
    currency: $stripePlan->currency,
    interval: $stripePlan->interval
);

// Cancelar suscripción Stripe
$subscriptionService->cancelSubscription(
    $stripeCustomer->subscriptions->first()
);

// Crear suscripción PayPal
$subscriptionService->createSubscription(
    customer: $paypalCustomer,
    plan: $paypalPlan
);
```

### Ejemplo 3: Link de Pago Rápido

```php
use App\Services\PaymentLinkService;

$linkService = app(PaymentLinkService::class);

// Crear link
$link = $linkService->createPaymentLink(
    user: auth()->user(),
    gateway: 'stripe',
    amount: $request->amount * 100, // Convertir a centavos
    currency: 'usd',
    additionalData: [
        'description' => $request->description,
        'metadata' => [
            'order_id' => $orderId,
            'customer_email' => $request->customer_email
        ]
    ]
);

// Enviar por email
Mail::to($request->customer_email)->send(
    new PaymentLinkEmail($link->url, $request->description)
);

return redirect()->back()->with('success', 'Link de pago enviado');
```

---

## Próximos Pasos

Ahora que conoces la API de servicios:

1. **Practica usando los servicios**: [05-USO.md](05-USO.md)
2. **Agrega una nueva pasarela**: [06-EXTENSIONES.md](06-EXTENSIONES.md)
3. **Configura webhooks**: [07-WEBHOOKS.md](07-WEBHOOKS.md)

---

[⬅️ Anterior: Estructura](03-ESTRUCTURA.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Uso](05-USO.md)
