<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Administrar Tarjetas</h2>

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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Formulario para agregar tarjeta -->
        <div class="border rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-3">Agregar Nueva Tarjeta</h3>

            <form wire:submit.prevent="addCard">
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
                        Token de Tarjeta *
                    </label>
                    <input
                        type="text"
                        wire:model="cardToken"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="pm_xxxxxxxxxxxxx"
                    >
                    <p class="text-xs text-gray-500 mt-1">
                        Ingresa el token del método de pago generado por Stripe
                    </p>
                    @error('cardToken')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                    Agregar Tarjeta
                </button>
            </form>
        </div>

        <!-- Lista de tarjetas -->
        <div class="border rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-3">Tus Tarjetas</h3>

            @if(count($cards) > 0)
                <div class="space-y-3">
                    @foreach($cards as $card)
                        <div class="border rounded p-3 flex items-center justify-between {{ $card['is_default'] ? 'border-blue-500 bg-blue-50' : '' }}">
                            <div>
                                <p class="font-semibold">
                                    {{ ucfirst($card['brand']) }} **** {{ $card['last4'] }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Expira: {{ str_pad($card['exp_month'], 2, '0', STR_PAD_LEFT) }}/{{ $card['exp_year'] }}
                                </p>
                                @if($card['is_default'])
                                    <span class="text-xs bg-blue-500 text-white px-2 py-1 rounded">Predeterminada</span>
                                @endif
                            </div>
                            <div>
                                <svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-500 py-8">
                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    </svg>
                    <p>No tienes tarjetas registradas</p>
                </div>
            @endif
        </div>
    </div>
</div>
