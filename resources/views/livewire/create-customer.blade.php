<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Crear Cliente</h2>

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

    <form wire:submit.prevent="createCustomer">
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
            <p class="text-sm text-gray-600">
                Se creará un cliente con tu email: <strong>{{ auth()->user()->email }}</strong>
            </p>
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
            Crear Cliente
        </button>
    </form>
</div>
