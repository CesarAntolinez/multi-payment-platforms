<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Crear Suscripción</h2>

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

    <form wire:submit.prevent="createSubscription">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Pasarela de Pago
            </label>
            <select wire:model="gateway" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                <option value="stripe">Stripe</option>
                <option value="paypal">PayPal</option>
                <option value="mercadopago">Mercado Pago</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Seleccionar Plan
            </label>
            <select wire:model="selectedPlanId" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                <option value="">-- Seleccione un plan --</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}">
                        {{ $plan->name }} - ${{ number_format($plan->amount, 2) }} {{ strtoupper($plan->currency) }} / {{ $plan->interval }}
                    </option>
                @endforeach
            </select>
            @error('selectedPlanId')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>

        @if(count($plans) === 0)
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                No hay planes disponibles para esta pasarela. Crea un plan primero.
            </div>
        @endif

        <button
            type="submit"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full"
            @if(count($plans) === 0) disabled @endif
        >
            Crear Suscripción
        </button>
    </form>
</div>
