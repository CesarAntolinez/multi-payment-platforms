# 💳 Multi-Payment Platforms - Laravel 8

> Sistema de integración de múltiples pasarelas de pago con Laravel 8, implementando el patrón Strategy para máxima flexibilidad y escalabilidad.

[![Laravel](https://img.shields.io/badge/Laravel-8.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 📋 Descripción General

Este proyecto proporciona una **capa de abstracción unificada** para gestionar múltiples pasarelas de pago desde una única aplicación Laravel. Permite a los desarrolladores integrar, gestionar y cambiar entre diferentes proveedores de pago sin modificar la lógica de negocio.

### ✨ Características Principales

- ✅ **Multi-pasarela**: Soporte para Stripe, PayPal (extensible a más)
- 🎯 **Patrón Strategy**: Arquitectura desacoplada y extensible
- 👥 **Gestión de Clientes**: Crear y actualizar clientes en cualquier pasarela
- 💳 **Administración de Tarjetas**: Agregar, listar y gestionar métodos de pago
- 📦 **Planes de Suscripción**: Crear y actualizar planes recurrentes
- 🔄 **Suscripciones**: Ciclo completo (crear, actualizar, cancelar)
- 🔗 **Links de Pago**: Generar enlaces de pago únicos
- 🔔 **Webhooks**: Sistema robusto de manejo de eventos
- 🎨 **UI Livewire**: Componentes reactivos para formularios
- 🧪 **Testing**: Suite completa de tests unitarios y de integración

## 🗂️ Tabla de Contenidos

### 📚 Documentación Detallada

| Documento | Descripción |
|-----------|-------------|
| [**01. Arquitectura**](docs/01-ARQUITECTURA.md) | Patrón Strategy, diagramas de clases, principios SOLID |
| [**02. Instalación**](docs/02-INSTALACION.md) | Requisitos, instalación paso a paso, configuración |
| [**03. Estructura**](docs/03-ESTRUCTURA.md) | Árbol de directorios, organización del código |
| [**04. Servicios**](docs/04-SERVICIOS.md) | API de servicios con ejemplos de uso |
| [**05. Uso**](docs/05-USO.md) | Guía práctica, flujo de usuario, comandos |
| [**06. Extensiones**](docs/06-EXTENSIONES.md) | Cómo agregar nuevas pasarelas de pago |
| [**07. Webhooks**](docs/07-WEBHOOKS.md) | Configuración y manejo de eventos |
| [**08. Testing**](docs/08-TESTING.md) | Ejecutar y crear tests |
| [**09. Buenas Prácticas**](docs/09-BUENAS-PRACTICAS.md) | Principios SOLID, patrones implementados |
| [**10. Troubleshooting**](docs/10-TROUBLESHOOTING.md) | Solución de problemas comunes |

## 🚀 Inicio Rápido (5 minutos)

```bash
# 1. Clonar el repositorio
git clone https://github.com/CesarAntolinez/multi-payment-platforms.git
cd multi-payment-platforms

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos (actualizar .env)
php artisan migrate

# 5. Configurar Stripe (obligatorio)
# Agregar en .env:
# STRIPE_KEY=tu_clave_publica
# STRIPE_SECRET=tu_clave_secreta

# 6. Iniciar servidor
php artisan serve
```

🎉 **¡Listo!** Visita http://localhost:8000

> 📖 Para instalación detallada, consulta [02-INSTALACION.md](docs/02-INSTALACION.md)

## 🏗️ Arquitectura (Resumen)

El proyecto implementa el **Patrón Strategy** para desacoplar la lógica de negocio de las implementaciones específicas de cada pasarela:

```
┌─────────────────────────────────────────────────────────┐
│                  Capa de Aplicación                     │
│          (Controllers, Livewire Components)             │
└────────────────────┬────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────┐
│                  Capa de Servicios                      │
│  CustomerService │ PlanService │ SubscriptionService    │
└────────────────────┬────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────┐
│             PaymentGatewayManager                       │
│              (Strategy Selector)                        │
└─────┬──────────────────────────────┬───────────────────┘
      │                              │
┌─────▼─────────┐            ┌──────▼────────────┐
│ StripeGateway │            │  PayPalGateway    │
│ (Concrete)    │            │  (Concrete)       │
└───────────────┘            └───────────────────┘
      │                              │
      └──────────────┬───────────────┘
                     │
           ┌─────────▼──────────┐
           │ PaymentGatewayInterface │
           │    (Strategy)      │
           └────────────────────┘
```

> 📖 Arquitectura completa en [01-ARQUITECTURA.md](docs/01-ARQUITECTURA.md)

## 🛠️ Tecnologías

| Categoría | Tecnología | Versión |
|-----------|-----------|---------|
| **Framework** | Laravel | 8.x |
| **PHP** | PHP | 8.0+ |
| **Frontend** | Livewire | 2.x |
| **UI Kit** | Jetstream | 2.9+ |
| **Testing** | PHPUnit | 9.3+ |
| **Pasarelas** | Stripe PHP SDK | 19.3+ |
| | PayPal SDK | 1.6+ |

## 📁 Estructura del Proyecto

```
multi-payment-platforms/
├── app/
│   ├── Contracts/           # Interfaces (PaymentGatewayInterface)
│   ├── Services/            # Lógica de negocio
│   │   ├── PaymentGateways/ # Implementaciones concretas
│   │   ├── CustomerService.php
│   │   ├── PlanService.php
│   │   └── ...
│   ├── Models/              # Eloquent models
│   ├── Http/
│   │   ├── Livewire/        # Componentes Livewire
│   │   └── Controllers/
│   └── Exceptions/          # Excepciones personalizadas
├── config/
│   └── services.php         # Configuración de pasarelas
├── database/
│   └── migrations/          # Schema de base de datos
├── routes/
│   └── web.php              # Rutas y webhooks
├── tests/                   # Tests unitarios y de integración
└── docs/                    # 📚 Documentación completa
```

> 📖 Estructura detallada en [03-ESTRUCTURA.md](docs/03-ESTRUCTURA.md)

## 💻 Ejemplos de Uso

### Crear un Cliente

```php
use App\Services\CustomerService;

$customerService = app(CustomerService::class);

$customer = $customerService->createCustomer(
    gateway: 'stripe',
    userId: auth()->id(),
    customerData: [
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com'
    ]
);
```

### Crear una Suscripción

```php
use App\Services\SubscriptionService;

$subscriptionService = app(SubscriptionService::class);

$subscription = $subscriptionService->createSubscription(
    gateway: 'stripe',
    userId: auth()->id(),
    planId: $plan->id,
    subscriptionData: [
        'trial_days' => 14
    ]
);
```

> 📖 Más ejemplos en [04-SERVICIOS.md](docs/04-SERVICIOS.md) y [05-USO.md](docs/05-USO.md)

## 🔐 Configuración de Pasarelas

### Stripe (Obligatorio)

```env
STRIPE_KEY=pk_test_xxxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
```

### PayPal (Opcional)

```env
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=xxxxxxxxxxxxx
PAYPAL_SECRET=xxxxxxxxxxxxx
```

> 📖 Configuración detallada en [02-INSTALACION.md](docs/02-INSTALACION.md)

## 🧪 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter CustomerServiceTest

# Con cobertura
php artisan test --coverage
```

> 📖 Guía completa de testing en [08-TESTING.md](docs/08-TESTING.md)

## 🔌 Agregar Nueva Pasarela

El sistema está diseñado para extensión fácil. Ejemplo para Mercado Pago:

```php
// 1. Crear clase que implemente PaymentGatewayInterface
class MercadoPagoGateway implements PaymentGatewayInterface {
    // Implementar métodos...
}

// 2. Registrar en PaymentGatewayManager
$this->registerGateway('mercadopago', new MercadoPagoGateway());
```

> 📖 Guía paso a paso en [06-EXTENSIONES.md](docs/06-EXTENSIONES.md)

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/NuevaCaracteristica`)
3. Commit tus cambios (`git commit -m 'Agregar nueva característica'`)
4. Push a la rama (`git push origin feature/NuevaCaracteristica`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está licenciado bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

**César Antolínez**

## 🆘 Soporte

¿Problemas? Consulta:
- [Troubleshooting Guide](docs/10-TROUBLESHOOTING.md)
- [Issues en GitHub](https://github.com/CesarAntolinez/multi-payment-platforms/issues)

## 📚 Recursos Adicionales

- [Documentación de Laravel 8](https://laravel.com/docs/8.x)
- [Documentación de Livewire 2](https://laravel-livewire.com/docs/2.x)
- [Stripe API Reference](https://stripe.com/docs/api)
- [PayPal Developer Docs](https://developer.paypal.com/docs/)

---

⭐ Si este proyecto te resultó útil, considera darle una estrella en GitHub.
