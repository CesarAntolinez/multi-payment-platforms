<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Crear Plan de Suscripción</h2>

    @if($successMessage)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $errorMessage }}
        </div>
    @endif

    <form wire:submit.prevent="createPlan">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Pasarela de Pago
            </label>
            <select wire:model="gateway" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                <option value="stripe">Stripe</option>
                <!-- Agregar más pasarelas aquí -->
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Nombre del Plan *
            </label>
            <input
                type="text"
                wire:model="name"
                class="shadow border rounded w-full py-2 px-3 text-gray-700"
                placeholder="Ej: Plan Premium Mensual"
            >
            @error('name')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Monto *
                </label>
                <input
                    type="number"
                    step="0.01"
                    wire:model="amount"
                    class="shadow border rounded w-full py-2 px-3 text-gray-700"
                    placeholder="9.99"
                >
                @error('amount')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Moneda *
                </label>
                <select wire:model="currency" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                    <option value="USD">USD - Dólar</option>
                    <option value="EUR">EUR - Euro</option>
                    <option value="MXN">MXN - Peso Mexicano</option>
                    <option value="COP">COP - Peso Colombiano</option>
                </select>
                @error('currency')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Intervalo *
                </label>
                <select wire:model="interval" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                    <option value="day">Día</option>
                    <option value="week">Semana</option>
                    <option value="month">Mes</option>
                    <option value="year">Año</option>
                </select>
                @error('interval')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Cada (cantidad) *
                </label>
                <input
                    type="number"
                    wire:model="intervalCount"
                    class="shadow border rounded w-full py-2 px-3 text-gray-700"
                    min="1"
                >
                @error('intervalCount')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="bg-gray-100 p-3 rounded mb-4">
            <p class="text-sm text-gray-700">
                <strong>Resumen:</strong> El plan cobrará
                <span class="font-bold">${{ $amount ?: '0.00' }} {{ strtoupper($currency) }}</span>
                cada
                <span class="font-bold">{{ $intervalCount > 1 ? $intervalCount : '' }} {{ $interval }}</span>
            </p>
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
            Crear Plan
        </button>
    </form>
</div>
