# ⭐ Buenas Prácticas Implementadas

[⬅️ Anterior: Testing](08-TESTING.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Troubleshooting](10-TROUBLESHOOTING.md)

---

## 📋 Tabla de Contenidos

- [Principios SOLID](#principios-solid)
- [Separation of Concerns](#separation-of-concerns)
- [Repository Pattern](#repository-pattern)
- [Service Layer Pattern](#service-layer-pattern)
- [Dependency Injection](#dependency-injection)
- [Validación y Manejo de Errores](#validación-y-manejo-de-errores)
- [Sistema de Caché](#sistema-de-caché)
- [Logging Estratégico](#logging-estratégico)
- [Seguridad](#seguridad)

---

## Principios SOLID

### S - Single Responsibility Principle (SRP)

**Principio**: Una clase debe tener una única razón para cambiar.

#### ✅ Implementación en el Proyecto

**Cada servicio tiene una responsabilidad específica:**

```php
// ✅ CORRECTO: Cada servicio hace una cosa
class CustomerService {
    // Solo gestiona clientes
    public function createCustomer() { }
    public function updateCustomer() { }
    public function getCustomer() { }
}

class SubscriptionService {
    // Solo gestiona suscripciones
    public function createSubscription() { }
    public function cancelSubscription() { }
}

// ❌ INCORRECTO: Responsabilidades mezcladas
class PaymentService {
    public function createCustomer() { }
    public function createSubscription() { }
    public function sendEmail() { } // Fuera de alcance
    public function generateReport() { } // Fuera de alcance
}
```

**Ejemplo Real del Proyecto:**

```php
// app/Services/CustomerService.php
// Solo maneja operaciones de clientes
class CustomerService
{
    public function createCustomer(User $user, string $gateway) { }
    public function updateCustomer(PaymentCustomer $customer) { }
    public function getCustomer(User $user, string $gateway) { }
}

// app/Services/CardService.php
// Solo maneja operaciones de tarjetas
class CardService
{
    public function createCard(PaymentCustomer $customer, string $token) { }
    public function deleteCard(PaymentCard $card) { }
    public function setDefaultCard(PaymentCard $card) { }
}
```

---

### O - Open/Closed Principle (OCP)

**Principio**: Abierto para extensión, cerrado para modificación.

#### ✅ Implementación en el Proyecto

**Agregar nueva pasarela SIN modificar código existente:**

```php
// 1. Crear nueva clase (EXTENSIÓN)
class MercadoPagoGateway implements PaymentGatewayInterface
{
    // Implementar métodos...
}

// 2. Registrar (NO modifica clases existentes)
// app/Services/PaymentGatewayManager.php
public function __construct()
{
    $this->registerGateway('stripe', new StripeGateway());
    $this->registerGateway('paypal', new PayPalGateway());
    
    // ⭐ Nueva pasarela agregada sin modificar las existentes
    $this->registerGateway('mercadopago', new MercadoPagoGateway());
}

// 3. Los servicios existentes funcionan sin cambios
$customerService->createCustomer($user, 'mercadopago');
// ✅ Funciona sin modificar CustomerService
```

**Comparación:**

```php
// ❌ INCORRECTO: Modificar cada vez que agregamos pasarela
class PaymentProcessor
{
    public function process($gateway)
    {
        if ($gateway === 'stripe') {
            // Código Stripe
        } elseif ($gateway === 'paypal') {
            // Código PayPal
        } elseif ($gateway === 'mercadopago') { // ← Modificamos clase existente
            // Código Mercado Pago
        }
    }
}

// ✅ CORRECTO: Extender sin modificar
interface PaymentGatewayInterface { }
class StripeGateway implements PaymentGatewayInterface { }
class PayPalGateway implements PaymentGatewayInterface { }
class MercadoPagoGateway implements PaymentGatewayInterface { } // ← Nueva clase
```

---

### L - Liskov Substitution Principle (LSP)

**Principio**: Las subclases deben ser sustituibles por sus clases base.

#### ✅ Implementación en el Proyecto

**Todas las pasarelas son intercambiables:**

```php
// Cualquier gateway puede usarse de la misma forma
public function createCustomer(PaymentGatewayInterface $gateway, array $data)
{
    // Este código funciona con CUALQUIER gateway
    $result = $gateway->createCustomer($data);
    
    if ($result['success']) {
        // Procesar...
    }
}

// Uso
$stripe = new StripeGateway();
$paypal = new PayPalGateway();
$mercadopago = new MercadoPagoGateway();

// ✅ Todos son sustituibles
createCustomer($stripe, $data);
createCustomer($paypal, $data);
createCustomer($mercadopago, $data);
```

**Contrato garantizado:**

```php
interface PaymentGatewayInterface
{
    // Todas las implementaciones DEBEN retornar este formato
    public function createCustomer(array $customerData): array;
    // Retorna: ['success' => bool, 'gateway_customer_id' => string, ...]
}

// ✅ StripeGateway respeta el contrato
class StripeGateway implements PaymentGatewayInterface
{
    public function createCustomer(array $customerData): array
    {
        return ['success' => true, 'gateway_customer_id' => 'cus_123'];
    }
}

// ✅ PayPalGateway respeta el contrato
class PayPalGateway implements PaymentGatewayInterface
{
    public function createCustomer(array $customerData): array
    {
        return ['success' => true, 'gateway_customer_id' => 'PAYID-123'];
    }
}
```

---

### I - Interface Segregation Principle (ISP)

**Principio**: No forzar clases a implementar métodos que no usan.

#### ✅ Implementación en el Proyecto

**Interfaz específica y cohesiva:**

```php
// ✅ CORRECTO: Interfaz específica para pasarelas de pago
interface PaymentGatewayInterface
{
    public function createCustomer(array $customerData): array;
    public function createSubscription(array $subscriptionData): array;
    public function createPaymentLink(array $linkData): array;
    // Solo métodos relevantes para pasarelas de pago
}

// ❌ INCORRECTO: Interfaz con métodos no relacionados
interface MegaInterface
{
    public function createCustomer(): array;
    public function sendEmail(): void; // ← No relacionado
    public function generateReport(): string; // ← No relacionado
    public function uploadFile(): bool; // ← No relacionado
}
```

**Interfaces específicas si es necesario:**

```php
// Si algunas pasarelas soportan características adicionales
interface RefundableGateway extends PaymentGatewayInterface
{
    public function refund(string $paymentId, int $amount): array;
}

// Solo las pasarelas que soportan refunds implementan esta interfaz
class StripeGateway implements RefundableGateway
{
    public function refund(string $paymentId, int $amount): array
    {
        // Implementación
    }
}
```

---

### D - Dependency Inversion Principle (DIP)

**Principio**: Depender de abstracciones, no de implementaciones concretas.

#### ✅ Implementación en el Proyecto

**Los servicios dependen de abstracciones:**

```php
// ✅ CORRECTO: Depende de PaymentGatewayManager (abstracción)
class CustomerService
{
    public function __construct(
        private PaymentGatewayManager $gatewayManager // ← Abstracción
    ) {}
    
    public function createCustomer(User $user, string $gateway)
    {
        $gatewayInstance = $this->gatewayManager->gateway($gateway);
        // gateway() retorna PaymentGatewayInterface (abstracción)
        $result = $gatewayInstance->createCustomer([...]);
    }
}

// ❌ INCORRECTO: Depende de implementación concreta
class CustomerService
{
    public function __construct(
        private StripeGateway $stripe, // ← Implementación concreta
        private PayPalGateway $paypal  // ← Implementación concreta
    ) {}
}
```

**Inyección de dependencias:**

```php
// Las dependencias se inyectan, no se crean
class SubscriptionService
{
    // ✅ Recibe dependencia en constructor
    public function __construct(
        private PaymentGatewayManager $gatewayManager
    ) {}
}

// Laravel resuelve automáticamente
$service = app(SubscriptionService::class);
// ✅ PaymentGatewayManager es inyectado automáticamente
```

---

## Separation of Concerns

**Principio**: Separar diferentes preocupaciones en módulos distintos.

### ✅ Arquitectura en Capas

```
┌─────────────────────────────────────┐
│  PRESENTACIÓN (Controllers/Views)   │ ← Maneja HTTP, validación básica
├─────────────────────────────────────┤
│  SERVICIOS (Business Logic)         │ ← Lógica de negocio
├─────────────────────────────────────┤
│  DOMINIO (Models/Contracts)         │ ← Entidades y contratos
├─────────────────────────────────────┤
│  INFRAESTRUCTURA (Gateways/DB)      │ ← Detalles de implementación
└─────────────────────────────────────┘
```

### Ejemplo de Separación

```php
// ✅ CAPA DE PRESENTACIÓN: Solo maneja HTTP
class SubscriptionController
{
    public function store(Request $request, SubscriptionService $service)
    {
        $validated = $request->validate([...]);
        
        try {
            $subscription = $service->createSubscription(...);
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

// ✅ CAPA DE SERVICIOS: Lógica de negocio
class SubscriptionService
{
    public function createSubscription(...)
    {
        // Validaciones de negocio
        // Orquestación de operaciones
        // Llamadas a gateways
        return DB::transaction(function () {
            // ...
        });
    }
}

// ✅ CAPA DE INFRAESTRUCTURA: Detalles técnicos
class StripeGateway
{
    public function createSubscription(...)
    {
        // Comunicación con API de Stripe
        return $this->stripe->subscriptions->create([...]);
    }
}
```

---

## Repository Pattern

Aunque no está completamente implementado, el proyecto usa Eloquent como abstracción de datos.

### ✅ Buena Práctica: Eloquent como Repository

```php
// Los servicios usan Eloquent, no SQL directo
class CustomerService
{
    public function getCustomer(User $user, string $gateway)
    {
        // ✅ Usando Eloquent (abstracción)
        return PaymentCustomer::where('user_id', $user->id)
            ->where('gateway', $gateway)
            ->first();
        
        // ❌ Evitar SQL directo
        // DB::select("SELECT * FROM payment_customers WHERE...");
    }
}
```

### Extensión Futura: Repository Dedicado

```php
// Para mayor abstracción, se podría implementar:
interface CustomerRepositoryInterface
{
    public function findByUserAndGateway(int $userId, string $gateway);
}

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findByUserAndGateway(int $userId, string $gateway)
    {
        return PaymentCustomer::where('user_id', $userId)
            ->where('gateway', $gateway)
            ->first();
    }
}
```

---

## Service Layer Pattern

**Todos los servicios encapsulan lógica de negocio:**

### ✅ Características

- **Transaccionales**: Usan DB transactions
- **Validados**: Verifican datos antes de procesar
- **Reutilizables**: Se usan desde múltiples puntos
- **Testeables**: Fáciles de testear con mocks

### Ejemplo Completo

```php
class SubscriptionService
{
    public function createSubscription(
        PaymentCustomer $customer,
        PaymentPlan $plan
    ): PaymentSubscription {
        // 1. Validación de negocio
        if ($customer->gateway !== $plan->gateway) {
            throw new Exception("Gateway mismatch");
        }
        
        // 2. Transacción
        return DB::transaction(function () use ($customer, $plan) {
            // 3. Llamada a gateway externo
            $result = $this->gatewayManager
                ->gateway($customer->gateway)
                ->createSubscription([...]);
            
            // 4. Validar respuesta
            if (!$result['success']) {
                throw new Exception($result['error']);
            }
            
            // 5. Persistir en BD local
            return PaymentSubscription::create([...]);
        });
    }
}
```

---

## Dependency Injection

### ✅ Constructor Injection

**Todos los servicios usan constructor injection:**

```php
class CustomerService
{
    public function __construct(
        private PaymentGatewayManager $gatewayManager
    ) {}
}

// Laravel lo resuelve automáticamente
$service = app(CustomerService::class);
```

### ✅ Registro como Singletons

```php
// app/Providers/PaymentServiceProvider.php
public function register()
{
    $this->app->singleton(PaymentGatewayManager::class);
    $this->app->singleton(CustomerService::class);
    $this->app->singleton(SubscriptionService::class);
    // Una única instancia compartida
}
```

### Ventajas

- ✅ Fácil testing con mocks
- ✅ Desacoplamiento
- ✅ Reutilización de instancias
- ✅ Configuración centralizada

---

## Validación y Manejo de Errores

### ✅ Validación en Múltiples Capas

```php
// 1. CAPA DE PRESENTACIÓN: Validación de formato
public function store(Request $request)
{
    $validated = $request->validate([
        'gateway' => 'required|in:stripe,paypal',
        'plan_id' => 'required|exists:payment_plans,id',
    ]);
}

// 2. CAPA DE SERVICIOS: Validación de negocio
public function createSubscription(...)
{
    if ($customer->gateway !== $plan->gateway) {
        throw new Exception("Gateways no coinciden");
    }
}

// 3. CAPA DE GATEWAY: Validación de datos
public function createSubscription(array $data)
{
    if (!isset($data['customer_id'])) {
        throw new GatewayException("customer_id es requerido");
    }
}
```

### ✅ Try-Catch Estratégico

```php
// En servicios
public function createCustomer(...)
{
    return DB::transaction(function () {
        try {
            $result = $this->gateway->createCustomer([...]);
            
            if (!$result['success']) {
                throw new CustomerException($result['error']);
            }
            
            return PaymentCustomer::create([...]);
            
        } catch (Exception $e) {
            Log::error('Error creating customer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    });
}
```

### ✅ Excepciones Personalizadas

```php
// app/Exceptions/PaymentGateway/CustomerException.php
class CustomerException extends Exception
{
    public function report()
    {
        Log::error($this->getMessage());
    }
    
    public function render($request)
    {
        return response()->json([
            'error' => 'Error al gestionar cliente'
        ], 500);
    }
}
```

---

## Sistema de Caché

### ✅ Caché de Configuración

```php
// Producción: Cachear config
php artisan config:cache

// Desarrollo: Limpiar caché
php artisan config:clear
```

### ✅ Caché de Datos (Ejemplo de Uso)

```php
// Cachear lista de planes
public function getPlans(string $gateway)
{
    return Cache::remember("plans.{$gateway}", 3600, function () use ($gateway) {
        return PaymentPlan::where('gateway', $gateway)->get();
    });
}

// Invalidar caché al actualizar
public function updatePlan(PaymentPlan $plan)
{
    $plan->update([...]);
    Cache::forget("plans.{$plan->gateway}");
}
```

---

## Logging Estratégico

### ✅ Logs Contextuales

```php
Log::info('Customer created', [
    'user_id' => $user->id,
    'gateway' => $gateway,
    'customer_id' => $result['gateway_customer_id']
]);

Log::warning('Subscription canceled', [
    'subscription_id' => $subscription->id,
    'reason' => 'user_request'
]);

Log::error('Payment failed', [
    'error' => $e->getMessage(),
    'gateway' => $gateway,
    'trace' => $e->getTraceAsString()
]);
```

### ✅ Canales Separados (Opcional)

```php
// config/logging.php
'channels' => [
    'payments' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payments.log'),
        'level' => 'debug',
    ],
];

// Uso
Log::channel('payments')->info('Payment processed');
```

---

## Seguridad

### ✅ Protección de Credenciales

```env
# .env (NO versionar)
STRIPE_SECRET=sk_live_XXXXXXX

# .env.example (SÍ versionar)
STRIPE_SECRET=sk_test_tu_clave_secreta
```

### ✅ Validación de Webhooks

```php
// SIEMPRE verificar firma
$event = Webhook::constructEvent(
    $payload,
    $signature,
    $webhookSecret
);
```

### ✅ CSRF Protection

```php
// Rutas protegidas automáticamente
Route::middleware(['web'])->group(function () {
    // CSRF habilitado
});

// Webhooks excluidos
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'webhooks/*',
];
```

### ✅ SQL Injection Prevention

```php
// ✅ Eloquent/Query Builder (protegido)
User::where('email', $email)->first();

// ❌ Raw SQL (vulnerable)
DB::select("SELECT * FROM users WHERE email = '$email'");
```

### ✅ Mass Assignment Protection

```php
class PaymentCustomer extends Model
{
    protected $fillable = [
        'user_id',
        'gateway',
        'gateway_customer_id',
        // Solo campos permitidos
    ];
    
    protected $guarded = ['id']; // Protegido
}
```

---

## Resumen de Patrones

| Patrón | Ubicación | Beneficio |
|--------|-----------|-----------|
| **Strategy** | `PaymentGatewayInterface` | Flexibilidad de pasarelas |
| **Registry** | `PaymentGatewayManager` | Gestión centralizada |
| **Service Layer** | `Services/` | Lógica de negocio encapsulada |
| **Dependency Injection** | Constructores | Testabilidad, desacoplamiento |
| **Singleton** | Service Provider | Una instancia compartida |
| **Factory** | Database factories | Datos de prueba |
| **Repository** | Eloquent Models | Abstracción de datos |

---

## Próximos Pasos

¡Felicidades! Has aprendido las mejores prácticas del proyecto.

Finalmente:
1. **Soluciona problemas**: [10-TROUBLESHOOTING.md](10-TROUBLESHOOTING.md)

---

[⬅️ Anterior: Testing](08-TESTING.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Troubleshooting](10-TROUBLESHOOTING.md)
