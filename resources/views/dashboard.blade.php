<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-gray-900">Dashboard de Pagos</h2>
        <p class="mt-2 text-gray-600">Gestiona clientes, planes, suscripciones y pagos desde un solo lugar</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Card: Crear Cliente -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Crear Cliente</dt>
                                        <dd class="text-lg font-semibold text-gray-900">Nuevo</dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('customers.create') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Ir al formulario →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Crear Plan -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Crear Plan</dt>
                                        <dd class="text-lg font-semibold text-gray-900">Suscripción</dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('plans.create') }}" class="text-green-600 hover:text-green-800 font-medium">
                                    Ir al formulario →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Crear Suscripción -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Crear Suscripción</dt>
                                        <dd class="text-lg font-semibold text-gray-900">Recurrente</dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('subscriptions.create') }}" class="text-purple-600 hover:text-purple-800 font-medium">
                                    Ir al formulario →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Administrar Tarjetas -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Mis Tarjetas</dt>
                                        <dd class="text-lg font-semibold text-gray-900">Gestionar</dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('cards.index') }}" class="text-yellow-600 hover:text-yellow-800 font-medium">
                                    Ver tarjetas →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Links de Pago -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Links de Pago</dt>
                                        <dd class="text-lg font-semibold text-gray-900">Generar</dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('payment-links.create') }}" class="text-red-600 hover:text-red-800 font-medium">
                                    Crear link →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Información -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Patrón Strategy</dt>
                                        <dd class="text-lg font-semibold text-gray-900">Implementado</dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600">Sistema flexible con múltiples pasarelas de pago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección informativa -->
                <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Información del Sistema</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Este sistema utiliza el <strong>Patrón Strategy</strong> para gestionar múltiples pasarelas de pago de forma flexible y escalable.</p>
                                <ul class="list-disc list-inside mt-2">
                                    <li>Soporte para Stripe (más pasarelas pueden agregarse fácilmente)</li>
                                    <li>Gestión completa de clientes, tarjetas y suscripciones</li>
                                    <li>Generación de links de pago</li>
                                    <li>Implementación con Laravel 8 y Livewire 2</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
