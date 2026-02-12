# 📦 Guía de Instalación

[⬅️ Anterior: Arquitectura](01-ARQUITECTURA.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Estructura](03-ESTRUCTURA.md)

---

## 📋 Tabla de Contenidos

- [Requisitos del Sistema](#requisitos-del-sistema)
- [Verificación de Extensiones PHP](#verificación-de-extensiones-php)
- [Instalación Paso a Paso](#instalación-paso-a-paso)
- [Configuración de Stripe](#configuración-de-stripe)
- [Configuración de PayPal](#configuración-de-paypal)
- [Configuración de Webhooks](#configuración-de-webhooks)
- [Verificación de la Instalación](#verificación-de-la-instalación)
- [Problemas Comunes](#problemas-comunes-y-soluciones)

---

## Requisitos del Sistema

### 📊 Tabla de Requisitos

| Componente | Versión Mínima | Versión Recomendada | Estado |
|------------|----------------|---------------------|--------|
| **PHP** | 8.0 | 8.1+ | ✅ Obligatorio |
| **Composer** | 2.0 | 2.5+ | ✅ Obligatorio |
| **Node.js** | 14.x | 16.x+ | ✅ Obligatorio |
| **NPM** | 6.x | 8.x+ | ✅ Obligatorio |
| **MySQL** | 5.7 | 8.0+ | ✅ Obligatorio |
| **PostgreSQL** | 10.x | 14.x+ | ⚠️ Alternativa |
| **Git** | 2.x | Latest | ✅ Obligatorio |

### 🔧 Extensiones PHP Requeridas

```bash
# Extensiones obligatorias
- php-json
- php-mbstring
- php-xml
- php-curl
- php-pdo
- php-mysql (o php-pgsql para PostgreSQL)
- php-zip
- php-bcmath
- php-tokenizer
- php-fileinfo
```

### 💻 Sistemas Operativos Soportados

- ✅ Linux (Ubuntu 20.04+, Debian 10+, CentOS 8+)
- ✅ macOS (10.15+)
- ✅ Windows 10/11 (con XAMPP, Laragon, o WSL2)

---

## Verificación de Extensiones PHP

### Paso 1: Verificar Versión de PHP

```bash
php -v
```

**Salida esperada:**
```
PHP 8.0.x (cli) (built: ...)
```

### Paso 2: Verificar Extensiones Instaladas

```bash
php -m | grep -E 'json|mbstring|xml|curl|pdo|mysql|zip|bcmath|tokenizer|fileinfo'
```

**Salida esperada:**
```
bcmath
curl
fileinfo
json
mbstring
pdo_mysql
tokenizer
xml
zip
```

### Paso 3: Instalar Extensiones Faltantes

#### Ubuntu/Debian

```bash
sudo apt-get update
sudo apt-get install -y php8.0-cli php8.0-common php8.0-mysql \
  php8.0-zip php8.0-gd php8.0-mbstring php8.0-curl php8.0-xml \
  php8.0-bcmath php8.0-tokenizer
```

#### macOS (con Homebrew)

```bash
brew install php@8.0
brew install composer
```

#### Windows

- Usar [XAMPP](https://www.apachefriends.org/) o [Laragon](https://laragon.org/)
- Las extensiones vienen preinstaladas

---

## Instalación Paso a Paso

### 📥 Paso 1: Clonar el Repositorio

```bash
# Usando HTTPS
git clone https://github.com/CesarAntolinez/multi-payment-platforms.git

# O usando SSH (si tienes configurado)
git clone git@github.com:CesarAntolinez/multi-payment-platforms.git

# Entrar al directorio
cd multi-payment-platforms
```

### 📦 Paso 2: Instalar Dependencias de PHP

```bash
composer install
```

**Tiempo estimado:** 2-3 minutos

**Salida esperada:**
```
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
Package operations: XX installs, 0 updates, 0 removals
  - Installing ...
Generating optimized autoload files
```

> ⚠️ **Nota**: Si encuentras errores, verifica que todas las extensiones PHP estén instaladas.

### 🎨 Paso 3: Instalar Dependencias de Node.js

```bash
npm install
```

**Tiempo estimado:** 1-2 minutos

### ⚙️ Paso 4: Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

**Salida esperada:**
```
Application key set successfully.
```

### 🗄️ Paso 5: Configurar Base de Datos

Editar `.env` y actualizar las credenciales de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_payment_platforms
DB_USERNAME=root
DB_PASSWORD=tu_password_aqui
```

#### Crear Base de Datos

**MySQL:**
```bash
mysql -u root -p
```

```sql
CREATE DATABASE multi_payment_platforms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**PostgreSQL (alternativa):**
```bash
psql -U postgres
```

```sql
CREATE DATABASE multi_payment_platforms;
\q
```

Si usas PostgreSQL, actualiza en `.env`:
```env
DB_CONNECTION=pgsql
DB_DATABASE=multi_payment_platforms
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

### 🔄 Paso 6: Ejecutar Migraciones

```bash
php artisan migrate
```

**Salida esperada:**
```
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (XX.XXms)
...
Migrating: XXXX_XX_XX_create_payment_customers_table
Migrated:  XXXX_XX_XX_create_payment_customers_table (XX.XXms)
```

### 📊 Paso 7: Poblar Base de Datos (Opcional)

```bash
# Ejecutar seeders para datos de prueba
php artisan db:seed
```

> 💡 **Tip**: Los seeders crean usuarios y datos de ejemplo para testing.

### 🎨 Paso 8: Compilar Assets

```bash
# Desarrollo (con watch)
npm run dev

# O compilar para producción
npm run build
```

### 🚀 Paso 9: Iniciar Servidor de Desarrollo

```bash
php artisan serve
```

**Salida esperada:**
```
Starting Laravel development server: http://127.0.0.1:8000
[Thu Jan 1 12:00:00 2024] PHP 8.0.x Development Server (http://127.0.0.1:8000) started
```

🎉 **¡Instalación completa!** Visita: http://127.0.0.1:8000

---

## Configuración de Stripe

### 🔑 Obtener Credenciales de Stripe

1. **Crear cuenta** en [Stripe](https://stripe.com)
2. **Ir a Dashboard** → Developers → API Keys
3. **Copiar**:
   - Publishable key (comienza con `pk_test_`)
   - Secret key (comienza con `sk_test_`)

### ⚙️ Configurar en .env

```env
STRIPE_KEY=pk_test_51XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
STRIPE_SECRET=sk_test_51XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
STRIPE_WEBHOOK_SECRET=whsec_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

> ⚠️ **Importante**: 
> - Usa claves de **test** durante desarrollo (`pk_test_` y `sk_test_`)
> - Nunca commits las claves en el repositorio
> - Las claves de **producción** (`pk_live_` y `sk_live_`) solo en servidor

### ✅ Verificar Configuración

```bash
php artisan tinker
```

```php
// En Tinker
use App\Services\PaymentGatewayManager;

$manager = app(PaymentGatewayManager::class);
$gateway = $manager->gateway('stripe');
$gateway->getGatewayName(); // Debe retornar 'stripe'
```

Si funciona correctamente, deberías ver:
```
=> "stripe"
```

### 🧪 Tarjetas de Prueba de Stripe

| Número | Tipo | Resultado |
|--------|------|-----------|
| `4242 4242 4242 4242` | Visa | ✅ Pago exitoso |
| `4000 0025 0000 3155` | Visa | ✅ Requiere autenticación 3D Secure |
| `4000 0000 0000 9995` | Visa | ❌ Fondos insuficientes |
| `4000 0000 0000 0002` | Visa | ❌ Tarjeta declinada |

**Datos complementarios de prueba:**
- **CVV**: Cualquier 3 dígitos (ej: 123)
- **Fecha expiración**: Cualquier fecha futura (ej: 12/25)
- **Código postal**: Cualquier (ej: 12345)

> 📖 Más tarjetas de prueba: https://stripe.com/docs/testing

---

## Configuración de PayPal

### 🔑 Obtener Credenciales de PayPal

1. **Crear cuenta** en [PayPal Developer](https://developer.paypal.com)
2. **Ir a Dashboard** → My Apps & Credentials
3. **Crear App** en Sandbox
4. **Copiar**:
   - Client ID
   - Secret

### ⚙️ Configurar en .env

```env
PAYPAL_CLIENT_ID=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
PAYPAL_SECRET=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
PAYPAL_MODE=sandbox
```

**Modos disponibles:**
- `sandbox`: Ambiente de pruebas (desarrollo)
- `live`: Producción (solo en servidor)

### ✅ Verificar Configuración

```bash
php artisan tinker
```

```php
use App\Services\PaymentGatewayManager;

$manager = app(PaymentGatewayManager::class);
$gateway = $manager->gateway('paypal');
$gateway->getGatewayName(); // Debe retornar 'paypal'
```

### 🧪 Cuentas de Prueba PayPal

PayPal proporciona cuentas de prueba en el Sandbox:

1. **Ir a** Sandbox → Accounts
2. **Encontrar**:
   - Personal Account (comprador)
   - Business Account (vendedor)
3. **Usar credenciales** para pruebas

---

## Configuración de Webhooks

Los webhooks permiten que las pasarelas notifiquen eventos (pagos exitosos, suscripciones canceladas, etc.)

### 🔔 Stripe Webhooks

#### Desarrollo Local con ngrok

1. **Instalar ngrok**:
```bash
# macOS
brew install ngrok

# Linux
snap install ngrok

# Windows - descargar de https://ngrok.com/download
```

2. **Iniciar túnel**:
```bash
ngrok http 8000
```

Esto mostrará una URL pública:
```
Forwarding  https://XXXX-XX-XXX-XXX-XXX.ngrok-free.app -> http://localhost:8000
```

3. **Configurar en Stripe Dashboard**:
   - Ir a Developers → Webhooks
   - Click "Add endpoint"
   - URL: `https://tu-url-ngrok.ngrok-free.app/webhooks/stripe`
   - Seleccionar eventos (o "Select all events")
   - Copiar **Signing secret** (comienza con `whsec_`)

4. **Actualizar .env**:
```env
STRIPE_WEBHOOK_SECRET=whsec_XXXXXXXXXXXXXXXXXXXXXXXXXX
```

#### Eventos Recomendados

- `customer.created`
- `customer.updated`
- `customer.deleted`
- `payment_intent.succeeded`
- `payment_intent.payment_failed`
- `invoice.payment_succeeded`
- `invoice.payment_failed`
- `customer.subscription.created`
- `customer.subscription.updated`
- `customer.subscription.deleted`

### 🔔 PayPal Webhooks

1. **Ir a** PayPal Developer → My Apps & Credentials
2. **Seleccionar tu App**
3. **Ir a** Webhooks
4. **Agregar Webhook**:
   - URL: `https://tu-url-ngrok.ngrok-free.app/webhooks/paypal`
   - Event types: Seleccionar eventos relevantes

#### Eventos Recomendados

- `BILLING.SUBSCRIPTION.CREATED`
- `BILLING.SUBSCRIPTION.ACTIVATED`
- `BILLING.SUBSCRIPTION.UPDATED`
- `BILLING.SUBSCRIPTION.CANCELLED`
- `PAYMENT.SALE.COMPLETED`
- `PAYMENT.SALE.REFUNDED`

### 🧪 Testing Webhooks

```bash
# Ver logs de webhooks
tail -f storage/logs/laravel.log | grep webhook
```

> 📖 Más información en [07-WEBHOOKS.md](07-WEBHOOKS.md)

---

## Verificación de la Instalación

### ✅ Checklist de Verificación

```bash
# 1. PHP funcionando
php -v

# 2. Composer instalado
composer --version

# 3. Node.js instalado
node -v

# 4. Base de datos conectada
php artisan migrate:status

# 5. Servidor corriendo
php artisan serve
# Visitar http://127.0.0.1:8000

# 6. Tests pasando
php artisan test
```

### 🔍 Test de Componentes

#### Test de Conexión a Stripe

```bash
php artisan tinker
```

```php
use App\Services\PaymentGatewayManager;

$manager = app(PaymentGatewayManager::class);
$stripe = $manager->gateway('stripe');

// Intentar crear un cliente de prueba
$result = $stripe->createCustomer([
    'name' => 'Test User',
    'email' => 'test@example.com'
]);

print_r($result);
```

Si todo funciona, verás información del cliente creado en Stripe.

#### Test de Conexión a PayPal

```php
$paypal = $manager->gateway('paypal');
$result = $paypal->createCustomer([
    'name' => 'Test User',
    'email' => 'test@example.com'
]);

print_r($result);
```

---

## Problemas Comunes y Soluciones

### ❌ Error: "Class not found"

**Problema**: Composer no ha cargado las clases correctamente.

**Solución**:
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### ❌ Error: "SQLSTATE[HY000] [1045] Access denied"

**Problema**: Credenciales de base de datos incorrectas.

**Solución**:
1. Verificar `.env` tiene usuario/contraseña correctos
2. Verificar que la base de datos existe:
```bash
mysql -u root -p
SHOW DATABASES;
```

### ❌ Error: "Permission denied" en storage/

**Problema**: Permisos incorrectos en directorios.

**Solución**:
```bash
# Linux/macOS
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache

# O más permisivo (solo desarrollo)
chmod -R 777 storage bootstrap/cache
```

### ❌ Error: "Stripe API key not found"

**Problema**: Variables de entorno no cargadas.

**Solución**:
```bash
# 1. Verificar que .env existe
ls -la .env

# 2. Verificar contenido
cat .env | grep STRIPE

# 3. Limpiar caché de configuración
php artisan config:clear
php artisan config:cache
```

### ❌ Error: npm install falla

**Problema**: Node.js o npm desactualizado.

**Solución**:
```bash
# Verificar versión
node -v
npm -v

# Actualizar npm
npm install -g npm@latest

# O usar nvm para actualizar Node.js
nvm install 16
nvm use 16
```

### ❌ Página en blanco después de php artisan serve

**Problema**: Error de permisos o configuración.

**Solución**:
```bash
# 1. Verificar logs
tail -f storage/logs/laravel.log

# 2. Verificar .env
php artisan config:clear

# 3. Regenerar clave
php artisan key:generate
```

### ❌ Tests fallan

**Problema**: Base de datos de testing no configurada.

**Solución**:

Crear `.env.testing`:
```env
APP_ENV=testing
DB_CONNECTION=mysql
DB_DATABASE=multi_payment_platforms_testing
```

Crear base de datos de testing:
```sql
CREATE DATABASE multi_payment_platforms_testing;
```

Ejecutar migraciones de testing:
```bash
php artisan migrate --env=testing
```

---

## Siguiente Paso

¡Instalación completada! 🎉

Continúa con:
- [03. Estructura del Proyecto](03-ESTRUCTURA.md) - Entender organización del código
- [05. Guía de Uso](05-USO.md) - Empezar a usar el sistema

---

[⬅️ Anterior: Arquitectura](01-ARQUITECTURA.md) | [🏠 Inicio](../README.md) | [➡️ Siguiente: Estructura](03-ESTRUCTURA.md)
