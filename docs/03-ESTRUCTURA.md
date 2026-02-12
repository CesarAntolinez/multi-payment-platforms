# 📁 Estructura del Proyecto

[⬅️ Anterior: Instalación](02-INSTALACION.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Servicios](04-SERVICIOS.md)

---

## 📋 Tabla de Contenidos

- [Árbol de Directorios](#árbol-de-directorios)
- [Descripción de Carpetas](#descripción-de-carpetas)
- [Archivos Principales](#archivos-principales)
- [Convenciones de Nomenclatura](#convenciones-de-nomenclatura)
- [Organización de Código](#organización-de-código)
- [Relación entre Componentes](#relación-entre-componentes)

---

## Árbol de Directorios

```
multi-payment-platforms/
│
├── app/                              # Código de la aplicación
│   ├── Console/                      # Comandos Artisan personalizados
│   │   ├── Commands/
│   │   │   └── TestPaymentGateways.php
│   │   └── Kernel.php
│   │
│   ├── Contracts/                    # ⭐ Interfaces (Strategy Pattern)
│   │   └── PaymentGatewayInterface.php
│   │
│   ├── Exceptions/                   # Excepciones personalizadas
│   │   ├── Handler.php
│   │   └── PaymentGateway/
│   │       ├── CustomerException.php
│   │       ├── GatewayException.php
│   │       ├── PlanException.php
│   │       └── SubscriptionException.php
│   │
│   ├── Http/                         # Capa HTTP
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   └── WebhookController.php  # ⭐ Manejo de webhooks
│   │   ├── Livewire/                  # ⭐ Componentes Livewire
│   │   │   ├── CreateCustomer.php
│   │   │   ├── CreatePaymentLink.php
│   │   │   ├── CreatePlan.php
│   │   │   ├── CreateSubscription.php
│   │   │   └── ManageCards.php
│   │   └── Middleware/
│   │       ├── Authenticate.php
│   │       ├── EncryptCookies.php
│   │       ├── PreventRequestsDuringMaintenance.php
│   │       ├── RedirectIfAuthenticated.php
│   │       ├── TrimStrings.php
│   │       ├── TrustHosts.php
│   │       ├── TrustProxies.php
│   │       └── VerifyCsrfToken.php
│   │
│   ├── Models/                       # ⭐ Modelos Eloquent
│   │   ├── User.php                  # Usuario (con trait HasPaymentCustomer)
│   │   ├── PaymentCard.php           # Tarjeta de pago
│   │   ├── PaymentCustomer.php       # Cliente en pasarela
│   │   ├── PaymentLink.php           # Link de pago
│   │   ├── PaymentPlan.php           # Plan de suscripción
│   │   ├── PaymentSubscription.php   # Suscripción activa
│   │   └── PaymentWebhook.php        # Eventos de webhooks
│   │
│   ├── Providers/                    # Service Providers
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── BroadcastServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   ├── FortifyServiceProvider.php
│   │   ├── JetstreamServiceProvider.php
│   │   ├── PaymentServiceProvider.php  # ⭐ Registro de servicios de pago
│   │   └── RouteServiceProvider.php
│   │
│   ├── Services/                     # ⭐⭐⭐ CAPA DE SERVICIOS (núcleo del negocio)
│   │   ├── PaymentGateways/          # Implementaciones de pasarelas
│   │   │   ├── AbstractPaymentGateway.php  # Clase base
│   │   │   ├── PayPalGateway.php           # Implementación PayPal
│   │   │   └── StripeGateway.php           # Implementación Stripe
│   │   │
│   │   ├── CardService.php           # Servicio de tarjetas
│   │   ├── CustomerService.php       # Servicio de clientes
│   │   ├── PaymentGatewayManager.php # ⭐ Manager/Registry
│   │   ├── PaymentLinkService.php    # Servicio de links de pago
│   │   ├── PlanService.php           # Servicio de planes
│   │   └── SubscriptionService.php   # Servicio de suscripciones
│   │
│   ├── Traits/                       # Traits reutilizables
│   │   └── HasPaymentCustomer.php    # Mixin para User model
│   │
│   ├── Actions/                      # Jetstream/Fortify Actions
│   │   └── Fortify/
│   │   └── Jetstream/
│   │
│   └── View/                         # View Composers
│
├── bootstrap/                        # Bootstrapping de Laravel
│   ├── app.php
│   └── cache/
│
├── config/                           # ⭐ Archivos de configuración
│   ├── app.php                       # Configuración de aplicación
│   ├── auth.php                      # Autenticación
│   ├── database.php                  # Base de datos
│   ├── services.php                  # ⭐ Credenciales de pasarelas
│   ├── session.php
│   ├── mail.php
│   └── ...
│
├── database/                         # Base de datos
│   ├── factories/                    # Model factories para testing
│   │   └── UserFactory.php
│   ├── migrations/                   # ⭐ Migraciones
│   │   ├── 2014_10_12_000000_create_users_table.php
│   │   ├── XXXX_create_payment_customers_table.php
│   │   ├── XXXX_create_payment_cards_table.php
│   │   ├── XXXX_create_payment_plans_table.php
│   │   ├── XXXX_create_payment_subscriptions_table.php
│   │   ├── XXXX_create_payment_links_table.php
│   │   └── XXXX_create_payment_webhooks_table.php
│   └── seeders/                      # Seeders
│       └── DatabaseSeeder.php
│
├── docs/                             # ⭐ Documentación completa
│   ├── 01-ARQUITECTURA.md
│   ├── 02-INSTALACION.md
│   ├── 03-ESTRUCTURA.md             # ← Este documento
│   ├── 04-SERVICIOS.md
│   ├── 05-USO.md
│   ├── 06-EXTENSIONES.md
│   ├── 07-WEBHOOKS.md
│   ├── 08-TESTING.md
│   ├── 09-BUENAS-PRACTICAS.md
│   └── 10-TROUBLESHOOTING.md
│
├── public/                           # Punto de entrada web
│   ├── index.php                     # Entry point
│   ├── css/
│   ├── js/
│   └── ...
│
├── resources/                        # Recursos no compilados
│   ├── css/                          # Estilos Tailwind
│   │   └── app.css
│   ├── js/                           # JavaScript
│   │   ├── app.js
│   │   └── bootstrap.js
│   ├── views/                        # ⭐ Vistas Blade
│   │   ├── dashboard.blade.php
│   │   ├── livewire/                 # Vistas de componentes Livewire
│   │   │   ├── create-customer.blade.php
│   │   │   ├── create-payment-link.blade.php
│   │   │   ├── create-plan.blade.php
│   │   │   ├── create-subscription.blade.php
│   │   │   └── manage-cards.blade.php
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   └── guest.blade.php
│   │   ├── auth/
│   │   └── profile/
│   └── markdown/                     # Políticas en Markdown
│       ├── policy.md
│       └── terms.md
│
├── routes/                           # ⭐ Definición de rutas
│   ├── api.php                       # Rutas API
│   ├── channels.php                  # Broadcasting channels
│   ├── console.php                   # Comandos console
│   └── web.php                       # ⭐ Rutas web + webhooks
│
├── storage/                          # Almacenamiento
│   ├── app/
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/                         # ⭐ Logs de la aplicación
│       └── laravel.log
│
├── tests/                            # ⭐ Tests (PHPUnit)
│   ├── Feature/                      # Tests de integración
│   │   ├── CustomerServiceTest.php
│   │   ├── PlanServiceTest.php
│   │   └── SubscriptionServiceTest.php
│   ├── Unit/                         # Tests unitarios
│   │   └── PaymentGatewayManagerTest.php
│   ├── CreatesApplication.php
│   └── TestCase.php
│
├── vendor/                           # Dependencias de Composer (no versionar)
│
├── .env                              # ⭐ Variables de entorno (no versionar)
├── .env.example                      # Plantilla de variables
├── .gitignore                        # Archivos ignorados por Git
├── artisan                           # CLI de Laravel
├── composer.json                     # ⭐ Dependencias PHP
├── composer.lock
├── package.json                      # ⭐ Dependencias Node.js
├── package-lock.json
├── phpunit.xml                       # Configuración PHPUnit
├── README.md                         # ⭐ Documentación principal
├── tailwind.config.js                # Configuración Tailwind CSS
├── vite.config.js                    # Configuración Vite
└── webpack.mix.js                    # Configuración Laravel Mix
```

---

## Descripción de Carpetas

### 📂 app/

Directorio principal del código de aplicación.

#### app/Console/
Comandos Artisan personalizados.

**Archivo destacado:**
- `Commands/TestPaymentGateways.php`: Comando para probar pasarelas

**Uso:**
```bash
php artisan test:payment-gateways
```

#### app/Contracts/
**Interfaces del sistema** (parte crucial del patrón Strategy).

- `PaymentGatewayInterface.php`: Define el contrato que todas las pasarelas deben implementar

#### app/Exceptions/
Excepciones personalizadas para manejo de errores específicos.

**Estructura:**
```
Exceptions/
├── Handler.php              # Manejador global
└── PaymentGateway/          # Excepciones de pagos
    ├── CustomerException.php
    ├── GatewayException.php
    ├── PlanException.php
    └── SubscriptionException.php
```

#### app/Http/
Capa de presentación HTTP.

**Subdirectorios:**
- **Controllers/**: Controladores tradicionales
  - `WebhookController.php`: Maneja webhooks de Stripe y PayPal
- **Livewire/**: Componentes Livewire (UI reactiva)
- **Middleware/**: Middleware de la aplicación

#### app/Models/
**Modelos Eloquent** (representan tablas de base de datos).

| Model | Descripción | Relaciones |
|-------|-------------|------------|
| `User` | Usuario del sistema | `hasMany(PaymentCustomer)` |
| `PaymentCustomer` | Cliente en pasarela | `belongsTo(User)`, `hasMany(PaymentCard)` |
| `PaymentCard` | Tarjeta de pago | `belongsTo(PaymentCustomer)` |
| `PaymentPlan` | Plan de suscripción | `hasMany(PaymentSubscription)` |
| `PaymentSubscription` | Suscripción activa | `belongsTo(PaymentCustomer)`, `belongsTo(PaymentPlan)` |
| `PaymentLink` | Link de pago | `belongsTo(User)` |
| `PaymentWebhook` | Evento de webhook | - |

#### app/Providers/
**Service Providers** de Laravel.

**Destacado:**
- `PaymentServiceProvider.php`: Registra todos los servicios de pago como singletons

```php
// Fragmento de PaymentServiceProvider
$this->app->singleton(PaymentGatewayManager::class);
$this->app->singleton(CustomerService::class);
$this->app->singleton(CardService::class);
// ...
```

#### app/Services/ ⭐⭐⭐
**NÚCLEO DEL NEGOCIO** - Capa de servicios con lógica de dominio.

**Estructura:**
```
Services/
├── PaymentGateways/               # Implementaciones de pasarelas
│   ├── AbstractPaymentGateway.php
│   ├── StripeGateway.php
│   └── PayPalGateway.php
│
├── PaymentGatewayManager.php      # Registry/Manager
├── CustomerService.php            # CRUD de clientes
├── CardService.php                # Gestión de tarjetas
├── PlanService.php                # CRUD de planes
├── SubscriptionService.php        # Gestión de suscripciones
└── PaymentLinkService.php         # Generación de links
```

#### app/Traits/
Traits reutilizables.

- `HasPaymentCustomer.php`: Añade métodos de pago al modelo `User`

**Ejemplo de uso:**
```php
// En User.php
use HasPaymentCustomer;

// Ahora User tiene:
$user->paymentCustomers()->get();
$user->createPaymentCustomer('stripe', [...]);
```

---

## Archivos Principales

### ⚙️ Configuración

#### config/services.php
Configuración de servicios externos (Stripe, PayPal).

```php
// Fragmento
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],

'paypal' => [
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'secret' => env('PAYPAL_SECRET'),
    'mode' => env('PAYPAL_MODE', 'sandbox'),
],
```

#### .env
Variables de entorno (credenciales, configuración).

**Nunca versionar este archivo.**

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
PAYPAL_CLIENT_ID=...
```

### 🗄️ Base de Datos

#### database/migrations/
Migraciones que definen la estructura de la base de datos.

**Principales:**
- `create_payment_customers_table.php`
- `create_payment_cards_table.php`
- `create_payment_plans_table.php`
- `create_payment_subscriptions_table.php`
- `create_payment_links_table.php`
- `create_payment_webhooks_table.php`

### 🛣️ Rutas

#### routes/web.php
Define todas las rutas web de la aplicación.

**Fragmento destacado:**
```php
// Dashboard con componentes Livewire
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // ... más rutas
});

// Webhooks (sin middleware de autenticación)
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe']);
Route::post('/webhooks/paypal', [WebhookController::class, 'paypal']);
```

### 🧪 Testing

#### tests/
Tests automatizados del sistema.

**Estructura:**
```
tests/
├── Feature/                    # Tests de integración
│   ├── CustomerServiceTest.php
│   ├── PlanServiceTest.php
│   └── SubscriptionServiceTest.php
│
└── Unit/                       # Tests unitarios
    └── PaymentGatewayManagerTest.php
```

---

## Convenciones de Nomenclatura

### 📝 Clases

| Tipo | Convención | Ejemplo |
|------|------------|---------|
| **Controllers** | `NombreController` | `WebhookController` |
| **Models** | `PascalCase`, singular | `PaymentCustomer` |
| **Services** | `NombreService` | `CustomerService` |
| **Exceptions** | `NombreException` | `CustomerException` |
| **Traits** | `HasNombre` o `NombreTrait` | `HasPaymentCustomer` |
| **Interfaces** | `NombreInterface` | `PaymentGatewayInterface` |
| **Livewire** | `VerboNombre` | `CreateCustomer` |

### 🗂️ Archivos

| Tipo | Convención | Ejemplo |
|------|------------|---------|
| **Vistas Blade** | `kebab-case.blade.php` | `create-customer.blade.php` |
| **Migraciones** | `YYYY_MM_DD_HHMMSS_descripcion.php` | `2023_01_15_create_payment_customers_table.php` |
| **Config** | `lowercase.php` | `services.php` |

### 💾 Base de Datos

| Tipo | Convención | Ejemplo |
|------|------------|---------|
| **Tablas** | `snake_case`, plural | `payment_customers` |
| **Columnas** | `snake_case` | `gateway_customer_id` |
| **Foreign Keys** | `tabla_singular_id` | `user_id`, `payment_plan_id` |
| **Índices** | `tabla_columna_index` | `payment_customers_user_id_index` |

### 🎨 Métodos

| Tipo | Convención | Ejemplo |
|------|------------|---------|
| **CRUD Create** | `create...` | `createCustomer()` |
| **CRUD Read** | `get...`, `find...` | `getCustomer()` |
| **CRUD Update** | `update...` | `updateCustomer()` |
| **CRUD Delete** | `delete...`, `destroy...` | `deleteCustomer()` |
| **Boolean** | `is...`, `has...`, `can...` | `isActive()`, `hasCard()` |

---

## Organización de Código

### 🎯 Principio de Separación de Concerns

El proyecto sigue una **arquitectura en capas** clara:

```
┌──────────────────────────────────────┐
│  PRESENTACIÓN (HTTP/UI)              │  ← Livewire, Controllers
├──────────────────────────────────────┤
│  SERVICIOS (Lógica de Negocio)      │  ← Services/, PaymentGateways/
├──────────────────────────────────────┤
│  DOMINIO (Models + Contracts)        │  ← Models/, Contracts/
├──────────────────────────────────────┤
│  INFRAESTRUCTURA (BD, APIs)          │  ← Eloquent, Stripe SDK, PayPal SDK
└──────────────────────────────────────┘
```

### 📐 Responsabilidades

| Capa | Responsabilidad | No debe |
|------|-----------------|---------|
| **Livewire/Controllers** | Recibir input, validar, llamar servicios | Contener lógica de negocio |
| **Services** | Lógica de negocio, orquestación | Acceder directamente a HTTP |
| **Models** | Persistencia, relaciones | Contener lógica de negocio compleja |
| **Gateways** | Comunicación con APIs externas | Saber sobre la BD local |

### 🔄 Flujo de Datos Típico

```
Usuario → Livewire Component → Service → Gateway → API Externa
                     ↓              ↓
                  Validar       Guardar en BD
```

**Ejemplo concreto:**
```
1. Usuario llena formulario "Crear Suscripción"
2. CreateSubscription (Livewire) valida datos
3. SubscriptionService.createSubscription() procesa lógica
4. PaymentGatewayManager selecciona gateway
5. StripeGateway.createSubscription() llama a Stripe API
6. SubscriptionService guarda PaymentSubscription en BD
7. CreateSubscription retorna respuesta al usuario
```

---

## Relación entre Componentes

### 🔗 Diagrama de Dependencias

```
Livewire Components
       │
       ↓
   Services ←────────┐
       │             │
       ├─────────────┤
       │             │
       ↓             │
PaymentGatewayManager│
       │             │
       ↓             │
PaymentGateways ─────┘
   (Stripe, PayPal)
       │
       ↓
   External APIs
```

### 🧩 Ejemplo de Interacción Completa

**Crear Cliente + Tarjeta + Suscripción:**

```php
// 1. CreateCustomer (Livewire)
public function submit()
{
    $this->customerService->createCustomer(
        gateway: 'stripe',
        userId: auth()->id(),
        customerData: [...]
    );
}

// 2. CustomerService
public function createCustomer($gateway, $userId, $customerData)
{
    $gatewayInstance = $this->gatewayManager->gateway($gateway);
    $result = $gatewayInstance->createCustomer($customerData);
    
    // Guardar en BD
    return PaymentCustomer::create([...]);
}

// 3. PaymentGatewayManager
public function gateway($name)
{
    return $this->gateways[$name]; // StripeGateway
}

// 4. StripeGateway
public function createCustomer($customerData)
{
    return $this->stripe->customers->create([...]);
}
```

### 📊 Modelos y Relaciones

```
User (1) ──────< (N) PaymentCustomer
                         │
                         ├──────< (N) PaymentCard
                         │
                         └──────< (N) PaymentSubscription
                                         │
                                         └─────> (1) PaymentPlan
```

**Código:**
```php
// User.php
public function paymentCustomers()
{
    return $this->hasMany(PaymentCustomer::class);
}

// PaymentCustomer.php
public function user()
{
    return $this->belongsTo(User::class);
}

public function cards()
{
    return $this->hasMany(PaymentCard::class);
}

public function subscriptions()
{
    return $this->hasMany(PaymentSubscription::class);
}
```

---

## Archivos que NO Debes Modificar

❌ **No modificar (generados automáticamente):**
- `vendor/` - Dependencias de Composer
- `node_modules/` - Dependencias de Node.js
- `public/build/` - Assets compilados
- `storage/framework/cache/` - Caché
- `bootstrap/cache/` - Caché de configuración

❌ **No versionar (en .gitignore):**
- `.env` - Variables de entorno
- `vendor/`
- `node_modules/`
- `public/hot`
- `public/storage`
- `storage/*.key`

---

## Próximos Pasos

Ahora que entiendes la estructura:

1. **Explora los servicios**: [04-SERVICIOS.md](04-SERVICIOS.md)
2. **Aprende a usar el sistema**: [05-USO.md](05-USO.md)
3. **Agrega una nueva pasarela**: [06-EXTENSIONES.md](06-EXTENSIONES.md)

---

[⬅️ Anterior: Instalación](02-INSTALACION.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Servicios](04-SERVICIOS.md)
