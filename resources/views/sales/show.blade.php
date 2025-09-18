@extends('layouts.migapp')

@section('title', 'Factura #' . str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT) . ' - MigApp')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Encabezado con acciones -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-receipt mr-3"></i>Factura #{{ str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT) }}
                </h1>
                <p class="text-white/80 mt-1">
                    {{ $sale->branch->name }} - {{ $sale->date->format('d/m/Y H:i') }}
                </p>
            </div>
            
            <div class="flex space-x-3">
                <x-migapp.button 
                    variant="secondary" 
                    icon="fas fa-arrow-left"
                    onclick="history.back()">
                    Volver
                </x-migapp.button>
                
                <x-migapp.button 
                    variant="primary" 
                    icon="fas fa-print"
                    onclick="window.print()">
                    Imprimir
                </x-migapp.button>
                
                <x-migapp.button 
                    variant="success" 
                    icon="fas fa-envelope"
                    onclick="emailInvoice({{ $sale->id_sale }})">
                    Enviar Email
                </x-migapp.button>
            </div>
        </div>

        <!-- Factura -->
        <div class="glass-card rounded-lg overflow-hidden" id="invoice-content">
            <!-- Cabecera de la empresa -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">
                            <i class="fas fa-bread-slice mr-2"></i>MigApp
                        </h1>
                        <p class="text-blue-100">Sistema de Gestión de Panaderías</p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-2xl font-bold mb-2">FACTURA</h2>
                        <p class="text-xl">#{{ str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <!-- Información de la factura -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Información de la sucursal -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-blue-200 pb-2">
                            <i class="fas fa-store text-blue-600 mr-2"></i>Información de la Sucursal
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-24">Sucursal:</span>
                                <span class="text-gray-900">{{ $sale->branch->name }}</span>
                            </div>
                            @if($sale->branch->address)
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-24">Dirección:</span>
                                <span class="text-gray-900">{{ $sale->branch->address }}</span>
                            </div>
                            @endif
                            @if($sale->branch->phone)
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-24">Teléfono:</span>
                                <span class="text-gray-900">{{ $sale->branch->phone }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Información de la venta -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-green-200 pb-2">
                            <i class="fas fa-info-circle text-green-600 mr-2"></i>Detalles de la Venta
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-24">Fecha:</span>
                                <span class="text-gray-900">{{ $sale->date->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-24">Hora:</span>
                                <span class="text-gray-900">{{ $sale->date->format('H:i') }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-24">Vendedor:</span>
                                <span class="text-gray-900">{{ $sale->user->name }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-24">Rol:</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    {{ $sale->user->role === 'owner' ? 'bg-purple-100 text-purple-800' : 
                                       ($sale->user->role === 'manager' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($sale->user->role) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de productos -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-amber-200 pb-2">
                        <i class="fas fa-shopping-bag text-amber-600 mr-2"></i>Productos Vendidos
                    </h3>
                    
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Producto
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cantidad
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Precio Unitario
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($sale->products as $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-4">
                                                    {{ strtoupper(substr($product->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                                    @if($product->description)
                                                        <div class="text-sm text-gray-500">{{ Str::limit($product->description, 50) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                {{ $product->pivot->quantity }}
                                                @if($product->unit)
                                                    {{ $product->unit }}
                                                @else
                                                    unidades
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                            ${{ number_format($product->pivot->unit_price, 0) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-lg font-bold text-green-600">
                                            ${{ number_format($product->pivot->subtotal, 0) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Resumen de totales -->
                <div class="flex justify-end mb-8">
                    <div class="w-full max-w-sm">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Productos:</span>
                                    <span class="font-medium">{{ $sale->products->count() }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Unidades totales:</span>
                                    <span class="font-medium">{{ $sale->products->sum('pivot.quantity') }}</span>
                                </div>
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between text-2xl font-bold text-green-600">
                                        <span>Total:</span>
                                        <span>${{ number_format($sale->total, 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                @if($sale->justify)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-indigo-200 pb-2">
                        <i class="fas fa-sticky-note text-indigo-600 mr-2"></i>Observaciones
                    </h3>
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <p class="text-gray-700">{{ $sale->justify }}</p>
                    </div>
                </div>
                @endif

                <!-- Footer de agradecimiento -->
                <div class="text-center bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">
                        ¡Gracias por tu compra!
                    </h3>
                    <p class="text-gray-600">
                        Esperamos verte pronto en {{ $sale->branch->name }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    @media print {
        body * {
            visibility: hidden;
        }
        #invoice-content, #invoice-content * {
            visibility: visible;
        }
        #invoice-content {
            position: absolute;
            left: 0;
            top: 0;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// Función para enviar factura por email
function emailInvoice(saleId) {
    const email = prompt('Ingrese el email del cliente:');
    if (email) {
        fetch(`{{ url('/sales') }}/${saleId}/email`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    }
}
</script>
@endpush
