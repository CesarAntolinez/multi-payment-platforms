# 🔧 Solución de Problemas (Troubleshooting)

[⬅️ Anterior: Buenas Prácticas](09-BUENAS-PRACTICAS.md) | [🏠 Inicio](../README.md)

---

## 📋 Tabla de Contenidos

- [Problemas con Base de Datos](#problemas-con-base-de-datos)
- [Errores de Stripe API](#errores-de-stripe-api)
- [Problemas con Webhooks](#problemas-con-webhooks)
- [Errores de Dependencias](#errores-de-dependencias)
- [Tests que Fallan](#tests-que-fallan)
- [Problemas de Permisos](#problemas-de-permisos)
- [FAQ (Preguntas Frecuentes)](#faq-preguntas-frecuentes)
- [Cómo Pedir Ayuda](#cómo-pedir-ayuda)

---

## Problemas con Base de Datos

### ❌ Error: "SQLSTATE[HY000] [1045] Access denied"

**Síntoma**: No puede conectarse a la base de datos.

**Causas Comunes**:
- Credenciales incorrectas en `.env`
- Usuario no existe o no tiene permisos
- MySQL no está corriendo

**Solución**:

```bash
# 1. Verificar que MySQL está corriendo
sudo systemctl status mysql
# o
brew services list | grep mysql

# 2. Probar conexión manual
mysql -u root -p

# 3. Verificar .env
cat .env | grep DB_

# 4. Actualizar credenciales
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_payment_platforms
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# 5. Limpiar caché de configuración
php artisan config:clear

# 6. Probar conexión
php artisan migrate:status
```

**Si el problema persiste**:

```bash
# Crear usuario con permisos
mysql -u root -p
```

```sql
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'password123';
GRANT ALL PRIVILEGES ON multi_payment_platforms.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### ❌ Error: "Base table or view not found"

**Síntoma**: Error al acceder a tabla inexistente.

**Causa**: Migraciones no ejecutadas.

**Solución**:

```bash
# Ver estado de migraciones
php artisan migrate:status

# Ejecutar migraciones pendientes
php artisan migrate

# Si hay errores, recrear BD
php artisan migrate:fresh

# Con seeders
php artisan migrate:fresh --seed
```

---

### ❌ Error: "Syntax error or access violation: 1071 Specified key was too long"

**Síntoma**: Error al migrar en MySQL antiguo.

**Causa**: MySQL < 5.7.7 no soporta índices largos.

**Solución**:

```php
// En app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Schema;

public function boot()
{
    Schema::defaultStringLength(191);
}
```

Luego:

```bash
php artisan migrate:fresh
```

---

### ❌ Error: "Too many connections"

**Síntoma**: MySQL rechaza nuevas conexiones.

**Solución**:

```bash
# Ver conexiones activas
mysql -u root -p
```

```sql
SHOW PROCESSLIST;

-- Aumentar límite
SET GLOBAL max_connections = 200;

-- Hacer permanente en /etc/mysql/my.cnf
[mysqld]
max_connections = 200
```

```bash
# Reiniciar MySQL
sudo systemctl restart mysql
```

---

## Errores de Stripe API

### ❌ Error: "No API key provided"

**Síntoma**: Stripe no encuentra la clave API.

**Solución**:

```bash
# 1. Verificar .env
cat .env | grep STRIPE

# 2. Debe tener:
STRIPE_KEY=pk_test_XXXXXXXXXXXXXXXX
STRIPE_SECRET=sk_test_XXXXXXXXXXXXXXXX

# 3. Limpiar caché
php artisan config:clear
php artisan config:cache

# 4. Verificar en config/services.php
php artisan tinker
```

```php
config('services.stripe.secret');
// Debe mostrar: "sk_test_XXXXX"
```

---

### ❌ Error: "Invalid API Key provided"

**Síntoma**: Clave API inválida o expirada.

**Solución**:

```bash
# 1. Ir a https://dashboard.stripe.com/test/apikeys
# 2. Copiar nuevas claves
# 3. Actualizar .env
STRIPE_KEY=pk_test_NUEVA_CLAVE
STRIPE_SECRET=sk_test_NUEVA_CLAVE

# 4. Limpiar caché
php artisan config:clear
```

**Verificar entorno correcto**:
- ✅ Desarrollo: usa `pk_test_` y `sk_test_`
- ✅ Producción: usa `pk_live_` y `sk_live_`
- ❌ NO mezclar claves test y live

---

### ❌ Error: "No such customer: cus_XXXXX"

**Síntoma**: Cliente no existe en Stripe.

**Causas**:
- Cliente eliminado en Stripe Dashboard
- Usando ambiente incorrecto (test vs live)
- BD local desincronizada con Stripe

**Solución**:

```bash
php artisan tinker
```

```php
use App\Models\PaymentCustomer;
use App\Services\CustomerService;

// Encontrar cliente en BD local
$customer = PaymentCustomer::find(1);

// Verificar que existe en Stripe
$service = app(CustomerService::class);

try {
    // Intentar obtener de Stripe
    $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
    $stripeCustomer = $stripe->customers->retrieve($customer->gateway_customer_id);
    
    echo "Cliente existe en Stripe\n";
} catch (\Exception $e) {
    echo "Cliente NO existe en Stripe: " . $e->getMessage() . "\n";
    
    // Opción 1: Recrear en Stripe
    // Opción 2: Eliminar de BD local
    $customer->delete();
}
```

---

### ❌ Error: "Rate limit exceeded"

**Síntoma**: Demasiadas peticiones a Stripe API.

**Solución**:

```php
// Agregar retry logic
use Stripe\Exception\RateLimitException;

try {
    $result = $stripe->customers->create([...]);
} catch (RateLimitException $e) {
    // Esperar y reintentar
    sleep(1);
    $result = $stripe->customers->create([...]);
}

// O implementar exponential backoff
$retries = 0;
$maxRetries = 3;

while ($retries < $maxRetries) {
    try {
        $result = $stripe->customers->create([...]);
        break;
    } catch (RateLimitException $e) {
        $retries++;
        sleep(pow(2, $retries)); // 2, 4, 8 segundos
    }
}
```

---

## Problemas con Webhooks

### ❌ Error: "Webhook signature verification failed"

**Síntoma**: Webhooks rechazados por firma inválida.

**Causas**:
- Webhook secret incorrecto
- Payload modificado en tránsito
- Usando endpoint antiguo

**Solución**:

```bash
# 1. Obtener nuevo webhook secret de Stripe Dashboard
# https://dashboard.stripe.com/test/webhooks

# 2. Actualizar .env
STRIPE_WEBHOOK_SECRET=whsec_NUEVO_SECRET

# 3. Limpiar caché
php artisan config:clear

# 4. Verificar
php artisan tinker
```

```php
config('services.stripe.webhook_secret');
// Debe mostrar: "whsec_XXXXX"
```

---

### ❌ Error: "Webhooks no llegan a la aplicación"

**Síntoma**: Eventos ocurren en Stripe pero no se reciben.

**Diagnóstico**:

```bash
# 1. Ver logs de Laravel
tail -f storage/logs/laravel.log | grep webhook

# 2. Ver logs de ngrok (si lo usas)
# http://127.0.0.1:4040

# 3. Verificar ruta está registrada
php artisan route:list | grep webhook
```

**Soluciones**:

**A. Verificar URL Pública**:
```bash
# Con ngrok
ngrok http 8000
# Copiar URL: https://XXXX.ngrok-free.app

# Configurar en Stripe:
# https://XXXX.ngrok-free.app/webhooks/stripe
```

**B. Verificar Firewall**:
```bash
# Permitir tráfico en puerto
sudo ufw allow 8000
```

**C. Probar Manualmente**:
```bash
curl -X POST http://localhost:8000/webhooks/stripe \
  -H "Content-Type: application/json" \
  -d '{"test": true}'

# Debe responder 200 o mostrar error específico
```

---

### ❌ Error: "Webhooks procesados múltiples veces"

**Síntoma**: El mismo evento se procesa varias veces.

**Causa**: Sin validación de idempotencia.

**Solución**:

```php
// En WebhookController.php
public function handleStripe(Request $request)
{
    $event = Webhook::constructEvent(...);
    
    // ✅ Verificar si ya fue procesado
    $existing = PaymentWebhook::where('event_id', $event->id)
        ->where('processed', true)
        ->first();
    
    if ($existing) {
        Log::info('Webhook ya procesado', ['event_id' => $event->id]);
        return response()->json(['status' => 'already processed'], 200);
    }
    
    // Continuar procesamiento...
}
```

---

## Errores de Dependencias

### ❌ Error: "Class 'X' not found"

**Síntoma**: Clase no encontrada.

**Causas**:
- Autoload no actualizado
- Dependencia faltante
- Namespace incorrecto

**Solución**:

```bash
# 1. Regenerar autoload
composer dump-autoload

# 2. Limpiar caché
php artisan clear-compiled
php artisan config:clear
php artisan cache:clear

# 3. Verificar que el archivo existe
find app -name "NombreClase.php"

# 4. Verificar namespace
head -n 10 app/Path/To/NombreClase.php
```

---

### ❌ Error: "Your requirements could not be resolved"

**Síntoma**: Composer no puede instalar dependencias.

**Solución**:

```bash
# 1. Verificar versión de PHP
php -v

# 2. Actualizar Composer
composer self-update

# 3. Limpiar caché de Composer
composer clear-cache

# 4. Intentar instalar nuevamente
composer install

# 5. Si falla, actualizar versiones
composer update

# 6. En último caso, eliminar vendor y reinstalar
rm -rf vendor composer.lock
composer install
```

---

### ❌ Error: npm install falla

**Síntoma**: No se pueden instalar dependencias de Node.

**Solución**:

```bash
# 1. Verificar versión de Node
node -v
npm -v

# 2. Limpiar caché
npm cache clean --force

# 3. Eliminar node_modules
rm -rf node_modules package-lock.json

# 4. Reinstalar
npm install

# 5. Si falla, usar versión específica de Node
nvm install 16
nvm use 16
npm install
```

---

## Tests que Fallan

### ❌ Error: "Database not found" en tests

**Síntoma**: Tests no encuentran la base de datos.

**Solución**:

```bash
# 1. Crear .env.testing
cp .env .env.testing

# 2. Configurar SQLite en memoria
# En .env.testing:
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# 3. O crear BD de testing en MySQL
mysql -u root -p
```

```sql
CREATE DATABASE multi_payment_platforms_testing;
```

```env
# En .env.testing
DB_CONNECTION=mysql
DB_DATABASE=multi_payment_platforms_testing
```

```bash
# 4. Ejecutar migraciones de testing
php artisan migrate --env=testing

# 5. Ejecutar tests
php artisan test
```

---

### ❌ Error: Tests pasan localmente pero fallan en CI

**Síntoma**: Tests funcionan en local, fallan en GitHub Actions.

**Causas**:
- Diferentes versiones de PHP
- Variables de entorno faltantes
- Base de datos no configurada

**Solución**:

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: testing
          MYSQL_ROOT_PASSWORD: password
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.0
          extensions: mbstring, xml, curl, mysql
      
      - name: Copy .env
        run: cp .env.example .env
      
      - name: Install Dependencies
        run: composer install
      
      - name: Generate key
        run: php artisan key:generate
      
      - name: Run Tests
        env:
          DB_CONNECTION: mysql
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
        run: php artisan test
```

---

## Problemas de Permisos

### ❌ Error: "Permission denied" en storage/

**Síntoma**: No puede escribir en storage/ o bootstrap/cache.

**Solución**:

```bash
# Linux/macOS
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache

# O más permisivo (solo desarrollo)
chmod -R 777 storage bootstrap/cache

# Windows (ejecutar CMD como Administrador)
icacls storage /grant Users:F /T
icacls bootstrap/cache /grant Users:F /T
```

---

### ❌ Error: "Failed to open stream: Permission denied"

**Síntoma**: Laravel no puede escribir logs.

**Solución**:

```bash
# Verificar permisos de storage/logs
ls -la storage/logs/

# Corregir permisos
sudo chmod -R 775 storage/logs
sudo chown -R $USER:www-data storage/logs

# Crear directorio si no existe
mkdir -p storage/logs
chmod 775 storage/logs
```

---

## FAQ (Preguntas Frecuentes)

### ❓ ¿Puedo usar Stripe y PayPal simultáneamente?

**Respuesta**: Sí, el sistema está diseñado para manejar múltiples pasarelas al mismo tiempo. Un usuario puede tener clientes en ambas pasarelas.

```php
// Cliente en Stripe
$stripeCustomer = $customerService->createCustomer($user, 'stripe');

// Cliente en PayPal
$paypalCustomer = $customerService->createCustomer($user, 'paypal');

// El usuario ahora tiene 2 clientes
$user->paymentCustomers; // Colección con ambos
```

---

### ❓ ¿Cómo cambio de entorno test a producción?

**Respuesta**: Actualiza las credenciales en `.env`:

```env
# Desarrollo/Test
STRIPE_KEY=pk_test_XXXXXXXX
STRIPE_SECRET=sk_test_XXXXXXXX

# Producción
STRIPE_KEY=pk_live_XXXXXXXX
STRIPE_SECRET=sk_live_XXXXXXXX
```

**⚠️ Importante**:
- ✅ Probar todo en test primero
- ✅ Configurar webhooks de producción
- ✅ Usar base de datos separada
- ✅ Habilitar logs de producción

---

### ❓ ¿Cómo migro suscripciones de Stripe a PayPal?

**Respuesta**: No hay migración automática. Debes:

1. Crear cliente en PayPal
2. Crear plan equivalente en PayPal
3. Cancelar suscripción Stripe
4. Crear nueva suscripción PayPal

```php
// 1. Obtener datos actuales
$stripeSubscription = $user->paymentCustomers()
    ->where('gateway', 'stripe')
    ->first()
    ->subscriptions()
    ->where('status', 'active')
    ->first();

// 2. Crear en PayPal
$paypalCustomer = $customerService->createCustomer($user, 'paypal');
$paypalPlan = /* crear plan equivalente */;
$paypalSubscription = $subscriptionService->createSubscription(
    $paypalCustomer,
    $paypalPlan
);

// 3. Cancelar Stripe
$subscriptionService->cancelSubscription($stripeSubscription);
```

---

### ❓ ¿Cómo elimino todos los datos de testing?

**Respuesta**:

```bash
# Recrear base de datos
php artisan migrate:fresh

# Eliminar webhooks guardados
php artisan tinker
```

```php
\App\Models\PaymentWebhook::truncate();
\App\Models\PaymentSubscription::truncate();
\App\Models\PaymentCard::truncate();
\App\Models\PaymentPlan::truncate();
\App\Models\PaymentCustomer::truncate();
\App\Models\PaymentLink::truncate();
```

---

### ❓ ¿Cómo pruebo webhooks sin ngrok?

**Respuesta**: Usa Stripe CLI:

```bash
# Instalar
brew install stripe/stripe-cli/stripe

# Login
stripe login

# Escuchar webhooks
stripe listen --forward-to localhost:8000/webhooks/stripe

# En otra terminal, disparar eventos
stripe trigger customer.subscription.created
stripe trigger invoice.payment_succeeded
```

---

## Cómo Pedir Ayuda

### 📋 Información a Incluir

Cuando pidas ayuda, incluye:

1. **Descripción del problema**
2. **Pasos para reproducir**
3. **Mensaje de error completo**
4. **Versiones** (PHP, Laravel, Composer)
5. **Logs relevantes**

### Ejemplo de Reporte

```markdown
## Problema
No puedo crear suscripciones, obtengo error 500.

## Pasos para Reproducir
1. Crear cliente en Stripe
2. Crear plan
3. Intentar crear suscripción
4. Error 500

## Error
```
SQLSTATE[23000]: Integrity constraint violation
```

## Entorno
- PHP: 8.0.25
- Laravel: 8.75.0
- Sistema: Ubuntu 20.04

## Logs
```
[2024-01-15 10:30:45] local.ERROR: ...
```

## Lo que he intentado
- Ejecutar migraciones
- Limpiar caché
- Verificar credenciales
```

### 🆘 Dónde Pedir Ayuda

1. **GitHub Issues**: https://github.com/CesarAntolinez/multi-payment-platforms/issues
2. **Stack Overflow**: Tag `laravel` + `stripe` o `paypal`
3. **Laravel Discord**: https://discord.gg/laravel
4. **Stripe Support**: https://support.stripe.com

---

## Herramientas de Diagnóstico

### Script de Diagnóstico

```bash
# Crear archivo diagnose.sh
cat > diagnose.sh << 'EOF'
#!/bin/bash

echo "=== Diagnóstico del Sistema ==="
echo ""

echo "PHP Version:"
php -v

echo ""
echo "Laravel Version:"
php artisan --version

echo ""
echo "Database Connection:"
php artisan migrate:status

echo ""
echo "Config Cache:"
ls -lh bootstrap/cache/config.php

echo ""
echo "Storage Permissions:"
ls -ld storage/logs

echo ""
echo "Environment:"
cat .env | grep -E "APP_ENV|DB_|STRIPE_|PAYPAL_" | sed 's/=.*$/=***/'

echo ""
echo "Recent Logs (last 20 lines):"
tail -20 storage/logs/laravel.log

echo ""
echo "=== Fin del Diagnóstico ==="
EOF

chmod +x diagnose.sh
./diagnose.sh
```

---

## Comandos de Limpieza

```bash
# Limpiar todo
php artisan optimize:clear

# Específicos
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Composer
composer dump-autoload

# NPM
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

---

## Recursos Adicionales

- 📖 [Documentación Laravel 8](https://laravel.com/docs/8.x)
- 📖 [Stripe API Docs](https://stripe.com/docs/api)
- 📖 [PayPal Developer Docs](https://developer.paypal.com/docs/)
- 🎥 [Laravel Tutorials](https://laracasts.com)
- 💬 [Laravel Community](https://laravel.io)

---

¡Espero que esta guía te ayude a resolver problemas! Si encuentras un problema no cubierto aquí, por favor abre un issue en GitHub.

---

[⬅️ Anterior: Buenas Prácticas](09-BUENAS-PRACTICAS.md) | [🏠 Inicio](../README.md)
