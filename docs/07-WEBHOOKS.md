# 🔔 Sistema de Webhooks

[⬅️ Anterior: Extensiones](06-EXTENSIONES.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Testing](08-TESTING.md)

---

## 📋 Tabla de Contenidos

- [¿Qué son los Webhooks?](#qué-son-los-webhooks)
- [Configuración de URLs](#configuración-de-urls)
- [Eventos Soportados](#eventos-soportados)
- [Seguridad y Verificación](#seguridad-y-verificación)
- [Testing Local con ngrok](#testing-local-con-ngrok)
- [Manejo de Eventos](#manejo-de-eventos)
- [Logging de Webhooks](#logging-de-webhooks)
- [Debugging](#debugging)
- [Ejemplos de Payloads](#ejemplos-de-payloads)

---

## ¿Qué son los Webhooks?

Los **webhooks** son notificaciones HTTP que las pasarelas de pago envían a tu aplicación cuando ocurren eventos importantes (pagos exitosos, suscripciones canceladas, etc.).

### 🎯 Por qué son Importantes

- ✅ **Tiempo real**: Notificaciones inmediatas de eventos
- ✅ **Confiabilidad**: No dependes de polling
- ✅ **Automatización**: Acciones automáticas basadas en eventos
- ✅ **Sincronización**: Mantén tu BD sincronizada con la pasarela

### 🔄 Flujo de Webhooks

```
Pasarela de Pago                 Tu Aplicación
      │                                │
      │  1. Evento ocurre              │
      │     (pago exitoso)             │
      │                                │
      │  2. POST /webhooks/stripe      │
      ├───────────────────────────────>│
      │                                │
      │                         3. Verificar firma
      │                         4. Guardar en BD
      │                         5. Procesar evento
      │                         6. Actualizar datos
      │                                │
      │  7. Response 200 OK            │
      │<───────────────────────────────┤
      │                                │
```

---

## Configuración de URLs

### Endpoints del Sistema

El sistema tiene endpoints para cada pasarela:

| Pasarela | Endpoint | Método |
|----------|----------|--------|
| **Stripe** | `/webhooks/stripe` | POST |
| **PayPal** | `/webhooks/paypal` | POST |
| **Mercado Pago** | `/webhooks/mercadopago` | POST |

**Ubicación del código**: [`app/Http/Controllers/WebhookController.php`](../app/Http/Controllers/WebhookController.php)

### Configurar en Stripe

#### Producción

1. **Ir a** https://dashboard.stripe.com/webhooks
2. **Click** "Add endpoint"
3. **URL**: `https://tu-dominio.com/webhooks/stripe`
4. **Eventos**: Seleccionar eventos (ver sección "Eventos Soportados")
5. **Copiar** el **Signing secret** (comienza con `whsec_`)
6. **Agregar** en `.env`:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_XXXXXXXXXXXXXXXX
   ```

#### Desarrollo/Testing

Ver sección [Testing Local con ngrok](#testing-local-con-ngrok)

### Configurar en PayPal

1. **Ir a** https://developer.paypal.com/dashboard/
2. **Seleccionar** tu aplicación
3. **Ir a** "Webhooks"
4. **Click** "Add Webhook"
5. **URL**: `https://tu-dominio.com/webhooks/paypal`
6. **Eventos**: Seleccionar eventos relevantes
7. **Guardar**

> ⚠️ **Nota**: PayPal no usa webhook secrets como Stripe. La verificación se hace con certificados SSL.

---

## Eventos Soportados

### Stripe Events

El sistema maneja los siguientes eventos de Stripe:

#### Suscripciones

| Evento | Descripción | Acción del Sistema |
|--------|-------------|-------------------|
| `customer.subscription.created` | Suscripción creada | Actualiza estado en BD |
| `customer.subscription.updated` | Suscripción actualizada | Actualiza datos (status, period) |
| `customer.subscription.deleted` | Suscripción cancelada | Marca como cancelada |
| `customer.subscription.trial_will_end` | Trial por terminar | Log (opcionalmente enviar email) |

#### Pagos/Facturas

| Evento | Descripción | Acción del Sistema |
|--------|-------------|-------------------|
| `invoice.payment_succeeded` | Pago exitoso | Log, opcional email confirmación |
| `invoice.payment_failed` | Pago fallido | Log warning, opcional notificar usuario |
| `invoice.created` | Factura creada | Log |
| `invoice.finalized` | Factura finalizada | Log |

#### Clientes

| Evento | Descripción | Acción del Sistema |
|--------|-------------|-------------------|
| `customer.created` | Cliente creado | Log |
| `customer.updated` | Cliente actualizado | Actualizar datos en BD |
| `customer.deleted` | Cliente eliminado | Marcar como eliminado |

#### Métodos de Pago

| Evento | Descripción | Acción del Sistema |
|--------|-------------|-------------------|
| `payment_method.attached` | Tarjeta agregada | Log |
| `payment_method.detached` | Tarjeta removida | Actualizar BD |
| `payment_method.updated` | Tarjeta actualizada | Actualizar datos |

**Implementación**: Ver método `processStripeEvent()` en [`WebhookController.php`](../app/Http/Controllers/WebhookController.php#L60)

### PayPal Events

| Evento | Descripción | Acción del Sistema |
|--------|-------------|-------------------|
| `BILLING.SUBSCRIPTION.ACTIVATED` | Suscripción activada | Actualiza estado |
| `BILLING.SUBSCRIPTION.UPDATED` | Suscripción actualizada | Actualiza datos |
| `BILLING.SUBSCRIPTION.CANCELLED` | Suscripción cancelada | Marca como cancelada |
| `BILLING.SUBSCRIPTION.SUSPENDED` | Suscripción suspendida | Actualiza estado |
| `PAYMENT.SALE.COMPLETED` | Pago completado | Log, opcional email |
| `PAYMENT.SALE.REFUNDED` | Reembolso procesado | Log, notificar |

**Implementación**: Ver método `processPayPalEvent()` en [`WebhookController.php`](../app/Http/Controllers/WebhookController.php#L183)

---

## Seguridad y Verificación

### Stripe: Verificación de Firma

Stripe firma todos los webhooks. **SIEMPRE** verifica la firma antes de procesar.

```php
// En WebhookController.php
public function handleStripe(Request $request)
{
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');
    $webhookSecret = config('services.stripe.webhook_secret');

    try {
        // ⭐ Verificar firma
        $event = \Stripe\Webhook::constructEvent(
            $payload, 
            $sigHeader, 
            $webhookSecret
        );
    } catch (\UnexpectedValueException $e) {
        // Payload inválido
        return response()->json(['error' => 'Invalid payload'], 400);
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Firma inválida
        return response()->json(['error' => 'Invalid signature'], 400);
    }

    // Procesar evento verificado...
}
```

**Importante:**
- ❌ Nunca proceses un webhook sin verificar la firma
- ❌ No uses datos del webhook sin verificación
- ✅ Siempre usa el webhook secret del entorno correcto (test/live)

### PayPal: Verificación

PayPal usa certificados SSL para autenticación:

```php
public function handlePayPal(Request $request)
{
    // PayPal envía datos verificados vía HTTPS
    $payload = $request->all();
    
    // Opcional: Verificar IP de origen
    $allowedIPs = [
        '173.0.82.126',
        '173.0.82.127',
        // ... más IPs de PayPal
    ];
    
    if (!in_array($request->ip(), $allowedIPs)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    // Procesar...
}
```

### Protección Adicional

#### 1. Rate Limiting

Limita las requests para prevenir ataques:

```php
// En routes/web.php
Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripe'])
    ->middleware('throttle:100,1'); // 100 requests por minuto
```

#### 2. Validar Payload

```php
protected function processStripeEvent($event)
{
    // Validar que el evento tenga los datos esperados
    if (!isset($event->data->object)) {
        Log::warning('Webhook inválido: sin data.object');
        return;
    }
    
    // Continuar procesamiento...
}
```

#### 3. Idempotencia

Evita procesar el mismo webhook múltiples veces:

```php
// Verificar si ya fue procesado
$existingWebhook = PaymentWebhook::where('event_id', $event->id)->first();

if ($existingWebhook && $existingWebhook->processed) {
    Log::info('Webhook ya procesado', ['event_id' => $event->id]);
    return response()->json(['status' => 'already processed'], 200);
}
```

---

## Testing Local con ngrok

Para probar webhooks en desarrollo local, usa **ngrok** para crear un túnel público.

### Paso 1: Instalar ngrok

```bash
# macOS
brew install ngrok

# Linux
snap install ngrok

# Windows - descargar de https://ngrok.com/download
```

### Paso 2: Crear Túnel

```bash
# Terminal 1: Iniciar Laravel
php artisan serve

# Terminal 2: Iniciar ngrok
ngrok http 8000
```

**Salida de ngrok:**
```
Session Status                online
Account                       TuCuenta (Plan: Free)
Version                       3.x.x
Region                        United States (us)
Latency                       50ms
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://XXXX-XX-XX-XX.ngrok-free.app -> http://localhost:8000
```

### Paso 3: Configurar Webhook en Stripe

1. Copiar URL de ngrok: `https://XXXX-XX-XX-XX.ngrok-free.app`
2. Ir a https://dashboard.stripe.com/test/webhooks
3. Click "Add endpoint"
4. URL: `https://XXXX-XX-XX-XX.ngrok-free.app/webhooks/stripe`
5. Seleccionar eventos
6. Copiar signing secret
7. Actualizar `.env`:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_XXXXXX
   ```

### Paso 4: Probar Webhook

```bash
# Crear una suscripción de prueba en Stripe Dashboard
# Los webhooks llegarán automáticamente a tu app local

# Ver logs en tiempo real
tail -f storage/logs/laravel.log | grep webhook
```

### Paso 5: Ver Tráfico de Webhooks

Ngrok proporciona una interfaz web:

```
http://127.0.0.1:4040
```

Aquí puedes:
- Ver todos los requests recibidos
- Inspeccionar payloads
- Reenviar requests
- Debugging detallado

---

## Manejo de Eventos

### Estructura del Webhook Handler

```php
protected function processStripeEvent($event)
{
    // Log del evento
    Log::info('Procesando evento Stripe', [
        'type' => $event->type,
        'id' => $event->id
    ]);

    // Switch por tipo de evento
    switch ($event->type) {
        case 'customer.subscription.updated':
            $this->handleSubscriptionUpdate($event->data->object);
            break;

        case 'invoice.payment_succeeded':
            $this->handlePaymentSuccess($event->data->object);
            break;

        default:
            Log::info('Evento no manejado', ['type' => $event->type]);
    }
}
```

### Ejemplo: Actualizar Suscripción

```php
protected function handleSubscriptionUpdate($stripeSubscription)
{
    // Buscar suscripción en BD local
    $subscription = PaymentSubscription::where(
        'gateway_subscription_id', 
        $stripeSubscription->id
    )->first();

    if (!$subscription) {
        Log::warning('Suscripción no encontrada', [
            'stripe_id' => $stripeSubscription->id
        ]);
        return;
    }

    // Actualizar datos
    $subscription->update([
        'status' => $stripeSubscription->status,
        'current_period_start' => date('Y-m-d H:i:s', $stripeSubscription->current_period_start),
        'current_period_end' => date('Y-m-d H:i:s', $stripeSubscription->current_period_end),
    ]);

    Log::info('Suscripción actualizada vía webhook', [
        'subscription_id' => $subscription->id,
        'new_status' => $stripeSubscription->status
    ]);

    // Opcional: Enviar notificación al usuario
    if ($stripeSubscription->status === 'active') {
        // Mail::to($subscription->user)->send(new SubscriptionActive());
    }
}
```

### Ejemplo: Pago Fallido

```php
protected function handleInvoicePaymentFailed($invoice)
{
    Log::warning('Pago fallido detectado', [
        'invoice_id' => $invoice->id,
        'amount' => $invoice->amount_due / 100,
        'customer_id' => $invoice->customer
    ]);

    // Buscar cliente
    $customer = PaymentCustomer::where(
        'gateway_customer_id', 
        $invoice->customer
    )->first();

    if ($customer) {
        // Notificar al usuario
        Mail::to($customer->user->email)->send(
            new PaymentFailedEmail($invoice)
        );

        // Opcional: Suspender suscripción después de X intentos
        $subscription = $customer->subscriptions()
            ->where('status', 'active')
            ->first();

        if ($subscription && $subscription->failed_payment_count >= 3) {
            $subscription->update(['status' => 'past_due']);
        }
    }
}
```

---

## Logging de Webhooks

### Base de Datos

Todos los webhooks se guardan en la tabla `payment_webhooks`:

```php
// Modelo: app/Models/PaymentWebhook.php
$webhook = PaymentWebhook::create([
    'gateway' => 'stripe',
    'event_type' => $event->type,
    'event_id' => $event->id,
    'payload' => json_decode($payload, true),
    'processed' => false,
]);

// Marcar como procesado
$webhook->markAsProcessed();

// Marcar como error
$webhook->markAsError($errorMessage);
```

### Logs de Aplicación

```php
// Logs en storage/logs/laravel.log

// Info
Log::info('Webhook recibido', [
    'gateway' => 'stripe',
    'event_type' => $event->type
]);

// Warning
Log::warning('Webhook sin acción', [
    'type' => $event->type
]);

// Error
Log::error('Error procesando webhook', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);
```

### Ver Webhooks en BD

```php
// En Tinker
php artisan tinker
```

```php
use App\Models\PaymentWebhook;

// Últimos 10 webhooks
$webhooks = PaymentWebhook::latest()->take(10)->get();

foreach ($webhooks as $webhook) {
    echo "{$webhook->gateway} - {$webhook->event_type} - " 
         . ($webhook->processed ? '✅' : '❌') . "\n";
}

// Webhooks no procesados
$pending = PaymentWebhook::where('processed', false)->get();

// Webhooks con errores
$errors = PaymentWebhook::whereNotNull('error_message')->get();
```

---

## Debugging

### Ver Logs en Tiempo Real

```bash
# Todos los logs
tail -f storage/logs/laravel.log

# Solo webhooks
tail -f storage/logs/laravel.log | grep -i webhook

# Solo errores
tail -f storage/logs/laravel.log | grep -i error
```

### Reenviar Webhooks

#### En Stripe Dashboard

1. Ir a https://dashboard.stripe.com/test/webhooks
2. Click en el endpoint
3. Ver eventos recientes
4. Click "..." → "Resend event"

#### En ngrok Interface

1. Abrir http://127.0.0.1:4040
2. Ver request específico
3. Click "Replay"

### Test Manual de Webhook

```bash
# Simular webhook de Stripe con curl
curl -X POST http://localhost:8000/webhooks/stripe \
  -H "Content-Type: application/json" \
  -H "Stripe-Signature: FIRMA_AQUI" \
  -d '{
    "id": "evt_test_webhook",
    "type": "customer.subscription.updated",
    "data": {
      "object": {
        "id": "sub_123",
        "status": "active"
      }
    }
  }'
```

### Stripe CLI para Testing

```bash
# Instalar Stripe CLI
brew install stripe/stripe-cli/stripe

# Login
stripe login

# Escuchar webhooks
stripe listen --forward-to localhost:8000/webhooks/stripe

# Enviar evento de prueba
stripe trigger customer.subscription.updated
```

---

## Ejemplos de Payloads

### Stripe: customer.subscription.updated

```json
{
  "id": "evt_1A2B3C4D5E6F",
  "object": "event",
  "type": "customer.subscription.updated",
  "data": {
    "object": {
      "id": "sub_1234567890",
      "object": "subscription",
      "status": "active",
      "customer": "cus_ABC123",
      "current_period_start": 1234567890,
      "current_period_end": 1237159890,
      "plan": {
        "id": "plan_premium",
        "amount": 2999,
        "currency": "usd",
        "interval": "month"
      }
    }
  }
}
```

### Stripe: invoice.payment_succeeded

```json
{
  "id": "evt_ABC123",
  "type": "invoice.payment_succeeded",
  "data": {
    "object": {
      "id": "in_1234567890",
      "object": "invoice",
      "customer": "cus_ABC123",
      "subscription": "sub_1234567890",
      "amount_paid": 2999,
      "currency": "usd",
      "status": "paid"
    }
  }
}
```

### PayPal: BILLING.SUBSCRIPTION.ACTIVATED

```json
{
  "id": "WH-ABC123",
  "event_type": "BILLING.SUBSCRIPTION.ACTIVATED",
  "resource": {
    "id": "I-BW452GLLEP1G",
    "plan_id": "P-5ML4271244454362WXNWU5NQ",
    "status": "ACTIVE",
    "status_update_time": "2024-01-15T10:00:00Z",
    "subscriber": {
      "email_address": "customer@example.com",
      "name": {
        "given_name": "John",
        "surname": "Doe"
      }
    }
  }
}
```

---

## Mejores Prácticas

### ✅ DO

- ✅ Verificar firma/autenticidad **siempre**
- ✅ Guardar todos los webhooks en BD para auditoría
- ✅ Procesar de forma idempotente
- ✅ Responder rápido (< 5 segundos)
- ✅ Usar queues para procesamiento pesado
- ✅ Log detallado de eventos y errores
- ✅ Testear con datos reales de sandbox

### ❌ DON'T

- ❌ Procesar sin verificar firma
- ❌ Ejecutar lógica pesada en el request
- ❌ Fallar silenciosamente
- ❌ Asumir que el webhook siempre llega
- ❌ Depender 100% de webhooks (verificar periódicamente)

---

## Próximos Pasos

Ahora que entiendes webhooks:

1. **Ejecuta tests**: [08-TESTING.md](08-TESTING.md)
2. **Revisa buenas prácticas**: [09-BUENAS-PRACTICAS.md](09-BUENAS-PRACTICAS.md)
3. **Soluciona problemas**: [10-TROUBLESHOOTING.md](10-TROUBLESHOOTING.md)

---

[⬅️ Anterior: Extensiones](06-EXTENSIONES.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Testing](08-TESTING.md)
