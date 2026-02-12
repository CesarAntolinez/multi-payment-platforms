# 🏗️ Arquitectura del Sistema

[🏠 Inicio](../README.md) | [➡️ Siguiente: Instalación](02-INSTALACION.md)

---

## 📋 Tabla de Contenidos

- [¿Qué es el Patrón Strategy?](#qué-es-el-patrón-strategy)
- [Aplicación en el Proyecto](#aplicación-en-el-proyecto)
- [Ventajas y Beneficios](#ventajas-y-beneficios)
- [Diagrama de Clases](#diagrama-de-clases)
- [Flujo de Ejecución](#flujo-de-ejecución)
- [Componentes Principales](#componentes-principales)
- [Principios SOLID Aplicados](#principios-solid-aplicados)

---

## ¿Qué es el Patrón Strategy?

El **Patrón Strategy** es un patrón de diseño de comportamiento que permite definir una familia de algoritmos, encapsular cada uno de ellos y hacerlos intercambiables. Este patrón permite que el algoritmo varíe independientemente de los clientes que lo utilizan.

### Componentes del Patrón

```
┌─────────────────────┐
│     Context         │  ← Cliente que usa la estrategia
│  (PaymentService)   │
└──────────┬──────────┘
           │ usa
           ▼
┌─────────────────────┐
│  Strategy Interface │  ← Define el contrato
│ (PaymentGateway     │
│   Interface)        │
└──────────┬──────────┘
           │
     ┌─────┴─────┐
     │           │
     ▼           ▼
┌─────────┐ ┌──────────┐
│Strategy │ │ Strategy │  ← Implementaciones concretas
│  A      │ │    B     │
│(Stripe) │ │ (PayPal) │
└─────────┘ └──────────┘
```

---

## Aplicación en el Proyecto

En este proyecto, implementamos el patrón Strategy para:

1. **Desacoplar** la lógica de negocio de las implementaciones específicas de pasarelas
2. **Permitir** cambiar entre pasarelas de pago en tiempo de ejecución
3. **Facilitar** la adición de nuevas pasarelas sin modificar código existente
4. **Unificar** la API de todas las pasarelas bajo una interfaz común

### Estructura Real del Proyecto

```
┌────────────────────────────────────────────────────┐
│           CAPA DE PRESENTACIÓN                     │
│   ┌──────────────┐  ┌──────────────┐              │
│   │  Livewire    │  │ Controllers  │              │
│   │  Components  │  │              │              │
│   └──────┬───────┘  └──────┬───────┘              │
└──────────┼──────────────────┼────────────────────── ┘
           │                  │
┌──────────▼──────────────────▼────────────────────── ┐
│           CAPA DE SERVICIOS (Domain)               │
│   ┌──────────────────┐  ┌─────────────────────┐   │
│   │ CustomerService  │  │ SubscriptionService │   │
│   ├──────────────────┤  ├─────────────────────┤   │
│   │ CardService      │  │ PaymentLinkService  │   │
│   ├──────────────────┤  ├─────────────────────┤   │
│   │ PlanService      │  │        ...          │   │
│   └────────┬─────────┘  └──────────┬──────────┘   │
└────────────┼────────────────────────┼───────────────┘
             │                        │
             │   ┌────────────────────▼─────┐
             └──►│ PaymentGatewayManager    │◄── Registry Pattern
                 │ (Context/Selector)       │
                 └───────────┬──────────────┘
                             │
                    ┌────────┴─────────┐
                    │                  │
         ┌──────────▼──────┐  ┌───────▼────────────┐
         │ StripeGateway   │  │  PayPalGateway     │
         └─────────────────┘  └────────────────────┘
                    │                  │
                    └────────┬─────────┘
                             │
                 ┌───────────▼────────────────┐
                 │ PaymentGatewayInterface    │ ◄── Strategy Interface
                 │                            │
                 │ + createCustomer()         │
                 │ + updateCustomer()         │
                 │ + createCard()             │
                 │ + createPlan()             │
                 │ + createSubscription()     │
                 │ + createPaymentLink()      │
                 └────────────────────────────┘
```

---

## Ventajas y Beneficios

### ✅ Ventajas del Diseño

| Ventaja | Descripción | Ejemplo en el Proyecto |
|---------|-------------|------------------------|
| **Flexibilidad** | Cambiar entre pasarelas sin modificar código | Cambiar de Stripe a PayPal con un parámetro |
| **Extensibilidad** | Agregar nuevas pasarelas fácilmente | Agregar Mercado Pago implementando la interfaz |
| **Mantenibilidad** | Código organizado y fácil de mantener | Cada pasarela en su propio archivo |
| **Testabilidad** | Fácil de testear con mocks | Mock de `PaymentGatewayInterface` |
| **Reusabilidad** | Servicios reutilizables en toda la app | `CustomerService` usado en múltiples componentes |
| **Escalabilidad** | Soporta crecimiento del sistema | Agregar nuevas pasarelas sin afectar existentes |

### 🎯 Principios Cumplidos

- **Open/Closed Principle**: Abierto a extensión (nuevas pasarelas), cerrado a modificación (código existente)
- **Dependency Inversion**: Servicios dependen de abstracciones, no de implementaciones concretas
- **Single Responsibility**: Cada clase tiene una única responsabilidad
- **Interface Segregation**: Interfaz clara con métodos específicos

---

## Diagrama de Clases

### PaymentGatewayInterface

```php
<<interface>>
PaymentGatewayInterface
───────────────────────────────────
+ createCustomer(array): array
+ updateCustomer(string, array): array
+ createCard(string, string): array
+ createPlan(array): array
+ updatePlan(string, array): array
+ createSubscription(array): array
+ updateSubscription(string, array): array
+ createPaymentLink(array): array
+ getGatewayName(): string
```

**Ubicación**: [`app/Contracts/PaymentGatewayInterface.php`](../app/Contracts/PaymentGatewayInterface.php)

### Implementaciones Concretas

```
AbstractPaymentGateway (Base)
├── StripeGateway
└── PayPalGateway
```

**StripeGateway**
```php
StripeGateway implements PaymentGatewayInterface
──────────────────────────────────────────────
- stripe: StripeClient
──────────────────────────────────────────────
+ __construct()
+ createCustomer(array): array
+ updateCustomer(string, array): array
+ createCard(string, string): array
+ createPlan(array): array
+ createSubscription(array): array
+ createPaymentLink(array): array
+ getGatewayName(): string
──────────────────────────────────────────────
# logOperation(string, array): void
# validateData(array, array): void
```

**PayPalGateway**
```php
PayPalGateway implements PaymentGatewayInterface
──────────────────────────────────────────────
- apiContext: ApiContext
──────────────────────────────────────────────
+ __construct()
+ createCustomer(array): array
+ updateCustomer(string, array): array
+ createPlan(array): array
+ createSubscription(array): array
+ createPaymentLink(array): array
+ getGatewayName(): string
```

**Ubicación**: 
- [`app/Services/PaymentGateways/StripeGateway.php`](../app/Services/PaymentGateways/StripeGateway.php)
- [`app/Services/PaymentGateways/PayPalGateway.php`](../app/Services/PaymentGateways/PayPalGateway.php)

### PaymentGatewayManager (Registry/Factory)

```php
PaymentGatewayManager
───────────────────────────────────
- gateways: array
───────────────────────────────────
+ __construct()
+ registerGateway(string, PaymentGatewayInterface): void
+ gateway(string): PaymentGatewayInterface
+ getAvailableGateways(): array
+ hasGateway(string): bool
```

**Ubicación**: [`app/Services/PaymentGatewayManager.php`](../app/Services/PaymentGatewayManager.php)

### Service Layer

```php
CustomerService
───────────────────────────────────
- gatewayManager: PaymentGatewayManager
───────────────────────────────────
+ createCustomer(string, int, array): PaymentCustomer
+ updateCustomer(int, int, array): PaymentCustomer
+ getCustomer(int, int): ?PaymentCustomer
```

```php
SubscriptionService
───────────────────────────────────
- gatewayManager: PaymentGatewayManager
───────────────────────────────────
+ createSubscription(string, int, int, array): PaymentSubscription
+ updateSubscription(int, int, array): PaymentSubscription
+ cancelSubscription(int, int): bool
```

**Ubicación**: [`app/Services/`](../app/Services/)

---

## Flujo de Ejecución

### Ejemplo: Crear una Suscripción

```
1. Usuario                              2. Livewire Component
   │                                       │
   │  Llena formulario                    │ CreateSubscription
   │  "Suscribirse"                       │
   └──────────────────────────────────────►│
                                           │ validate()
                                           ▼
                                    3. SubscriptionService
                                           │
                                           │ createSubscription(
                                           │   gateway: 'stripe',
                                           │   userId: 1,
                                           │   planId: 5,
                                           │   data: [...]
                                           │ )
                                           ▼
                                    4. PaymentGatewayManager
                                           │
                                           │ gateway('stripe')
                                           ▼
                                    5. StripeGateway
                                           │
                                           │ createSubscription([...])
                                           │
                                           ▼
                                    6. Stripe API
                                           │
                      ┌────────────────────┤
                      │                    │
                      ▼                    ▼
              7. PaymentSubscription    8. PaymentWebhook
                     Model                  (async)
                      │
                      ▼
              9. Respuesta al Usuario
                  "Suscripción creada"
```

### Secuencia Detallada

1. **Usuario**: Completa formulario en la UI
2. **Livewire Component**: Valida datos y llama al servicio
3. **SubscriptionService**: Orquesta la lógica de negocio
4. **PaymentGatewayManager**: Selecciona la pasarela correcta ('stripe', 'paypal', etc.)
5. **StripeGateway**: Ejecuta lógica específica de Stripe
6. **Stripe API**: Procesa la solicitud en el servidor de Stripe
7. **PaymentSubscription Model**: Guarda registro en BD local
8. **PaymentWebhook**: (Asíncrono) Recibe confirmación/eventos
9. **Respuesta**: Retorna al usuario confirmación

---

## Componentes Principales

### 1. Contratos (Interfaces)

**PaymentGatewayInterface**: Define el contrato que todas las pasarelas deben cumplir.

```php
// Fragmento simplificado
interface PaymentGatewayInterface
{
    public function createCustomer(array $customerData): array;
    public function createSubscription(array $subscriptionData): array;
    // ... más métodos
}
```

### 2. Implementaciones Concretas

Cada pasarela implementa la interfaz con su lógica específica:

**StripeGateway**: Usa el SDK de Stripe
```php
// Ver implementación completa en:
// app/Services/PaymentGateways/StripeGateway.php
```

**PayPalGateway**: Usa el SDK de PayPal
```php
// Ver implementación completa en:
// app/Services/PaymentGateways/PayPalGateway.php
```

### 3. Manager (Registry)

**PaymentGatewayManager**: Administra y proporciona acceso a las pasarelas registradas.

```php
// Registro en constructor
public function __construct()
{
    $this->registerGateway('stripe', new StripeGateway());
    $this->registerGateway('paypal', new PayPalGateway());
}

// Uso
$gateway = $this->gatewayManager->gateway('stripe');
```

### 4. Service Layer

Servicios que orquestan la lógica de negocio:

- **CustomerService**: Gestión de clientes
- **CardService**: Gestión de tarjetas
- **PlanService**: Gestión de planes
- **SubscriptionService**: Gestión de suscripciones
- **PaymentLinkService**: Generación de links de pago

### 5. Models (Eloquent)

Persistencia de datos:

- **User**: Usuario del sistema (con trait `HasPaymentCustomer`)
- **PaymentCustomer**: Cliente en pasarela de pago
- **PaymentCard**: Tarjeta de pago
- **PaymentPlan**: Plan de suscripción
- **PaymentSubscription**: Suscripción activa
- **PaymentLink**: Link de pago generado
- **PaymentWebhook**: Eventos recibidos de pasarelas

### 6. Livewire Components

Interfaz de usuario reactiva:

- **CreateCustomer**: Formulario crear cliente
- **ManageCards**: Gestión de tarjetas
- **CreatePlan**: Formulario crear plan
- **CreateSubscription**: Formulario suscripción
- **CreatePaymentLink**: Generador de links

---

## Principios SOLID Aplicados

### S - Single Responsibility Principle

✅ **Cumplido**: Cada clase tiene una única responsabilidad.

- `StripeGateway`: Solo maneja operaciones de Stripe
- `CustomerService`: Solo gestiona clientes
- `PaymentGatewayManager`: Solo registra y provee pasarelas

### O - Open/Closed Principle

✅ **Cumplido**: Abierto a extensión, cerrado a modificación.

- Para agregar nueva pasarela: crear nueva clase, NO modificar existentes
- Ejemplo: Agregar `MercadoPagoGateway` sin tocar `StripeGateway`

### L - Liskov Substitution Principle

✅ **Cumplido**: Las implementaciones son intercambiables.

- `StripeGateway` y `PayPalGateway` son sustituibles
- Los servicios usan `PaymentGatewayInterface`, no implementaciones concretas

### I - Interface Segregation Principle

✅ **Cumplido**: Interfaces específicas, no genéricas.

- `PaymentGatewayInterface` solo tiene métodos relacionados con pagos
- No mezcla responsabilidades de diferentes dominios

### D - Dependency Inversion Principle

✅ **Cumplido**: Dependencias sobre abstracciones.

- Servicios dependen de `PaymentGatewayInterface`
- No dependen directamente de `StripeGateway` o `PayPalGateway`

```php
// ✅ Correcto: Depende de abstracción
class SubscriptionService 
{
    public function __construct(
        private PaymentGatewayManager $gatewayManager
    ) {}
}

// ❌ Incorrecto: Dependería de implementación concreta
class SubscriptionService 
{
    public function __construct(
        private StripeGateway $stripe  // ¡Acoplamiento!
    ) {}
}
```

---

## Patrones Adicionales

### Registry Pattern

**PaymentGatewayManager** actúa como un registro centralizado de pasarelas disponibles.

### Template Method Pattern

**AbstractPaymentGateway** (si existiera) podría definir un esqueleto de algoritmo con pasos que las subclases implementan.

### Singleton Pattern

Los servicios se registran como **singletons** en el Service Provider:

```php
// app/Providers/PaymentServiceProvider.php
$this->app->singleton(PaymentGatewayManager::class);
$this->app->singleton(CustomerService::class);
```

### Dependency Injection

Constructor-based injection en todos los servicios:

```php
class CustomerService
{
    public function __construct(
        private PaymentGatewayManager $gatewayManager
    ) {}
}
```

---

## Diagrama de Secuencia UML

```
Usuario  Component  Service  Manager  Gateway  StripeAPI  Database
  │         │         │        │        │         │          │
  │  Submit │         │        │        │         │          │
  ├────────►│         │        │        │         │          │
  │         │ create  │        │        │         │          │
  │         ├────────►│        │        │         │          │
  │         │         │gateway │        │         │          │
  │         │         ├───────►│        │         │          │
  │         │         │        │ get    │         │          │
  │         │         │        ├───────►│         │          │
  │         │         │        │◄───────┤         │          │
  │         │         │create  │        │         │          │
  │         │         ├────────┴───────►│         │          │
  │         │         │                 │  POST   │          │
  │         │         │                 ├────────►│          │
  │         │         │                 │◄────────┤          │
  │         │         │◄────────────────┤         │          │
  │         │         │                 │         │          │
  │         │         │  save           │         │          │
  │         │         ├────────────────────────────────────►│
  │         │         │◄───────────────────────────────────┤
  │         │◄────────┤                 │         │          │
  │◄────────┤         │                 │         │          │
  │  Success│         │                 │         │          │
```

---

## Conclusión

La arquitectura de este proyecto está diseñada siguiendo las mejores prácticas de desarrollo de software:

- ✅ **Patrón Strategy** para flexibilidad
- ✅ **Principios SOLID** para mantenibilidad
- ✅ **Separación de Concerns** para claridad
- ✅ **Dependency Injection** para testabilidad
- ✅ **Service Layer** para reutilización

Esta arquitectura permite:
- Agregar nuevas pasarelas en minutos
- Cambiar entre pasarelas sin reescribir código
- Testear componentes de forma aislada
- Escalar el sistema fácilmente

---

[🏠 Inicio](../README.md) | [➡️ Siguiente: Instalación](02-INSTALACION.md)
