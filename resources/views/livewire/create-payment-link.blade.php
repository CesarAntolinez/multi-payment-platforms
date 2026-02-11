<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Crear Link de Pago</h2>

    @if($successMessage)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ $successMessage }}

            @if($generatedUrl)
                <div class="mt-3">
                    <p class="font-bold mb-2">URL del Link de Pago:</p>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            value="{{ $generatedUrl }}"
                            readonly
                            class="flex-1 px-3 py-2 border rounded bg-white"
                            id="paymentUrl"
                        >
                        <button
                            type="button"
                            onclick="copyToClipboard()"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                        >
                            Copiar
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($errorMessage)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $errorMessage }}
        </div>
    @endif

    <form wire:submit.prevent="createPaymentLink">
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
                Descripción *
            </label>
            <input
                type="text"
                wire:model="description"
                class="shadow border rounded w-full py-2 px-3 text-gray-700"
                placeholder="Ej: Pago por servicio de consultoría"
            >
            @error('description')
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
                    placeholder="100.00"
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

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
            Generar Link de Pago
        </button>
    </form>

    <script>
        function copyToClipboard() {
            const urlInput = document.getElementById('paymentUrl');
            urlInput.select();
            document.execCommand('copy');
            alert('URL copiada al portapapeles');
        }
    </script>
</div>
