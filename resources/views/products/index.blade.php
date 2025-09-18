@extends('layouts.migapp')

@section('title', 'Productos - ' . $inventory->name . ' - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <div class="flex items-center mb-2">
                    <a href="{{ route('inventories.index') }}" 
                       class="text-white/80 hover:text-white mr-3 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-box-open mr-3"></i>{{ $inventory->name }}
                    </h1>
                </div>
                <p class="text-white/80 ml-8">
                    <i class="fas fa-map-marker-alt mr-2"></i>{{ $inventory->branch->name }}
                    <span class="mx-2">•</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        {{ $inventory->type === 'sale_product' ? 'bg-green-500 text-white' : 'bg-orange-500 text-white' }}">
                        {{ $inventory->type === 'sale_product' ? 'Productos de Venta' : 'Materias Primas' }}
                    </span>
                </p>
            </div>
            
            @if(in_array(auth()->user()->role, ['owner', 'manager']))
                <x-migapp.button 
                    variant="primary" 
                    icon="fas fa-plus"
                    onclick="openModal('create-product')">
                    Nuevo Producto
                </x-migapp.button>
            @endif
        </div>
        
        <!-- Estadísticas -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 stats-container">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-boxes text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $analysis['total_products'] }}</div>
                        <div class="text-white/70 text-xs">Total Productos</div>
                    </div>
                </div>
                
                @if($inventory->type === 'sale_product')
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bread-slice text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ $analysis['fresh_products'] }}</div>
                            <div class="text-white/70 text-xs">Productos Frescos</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-amber-300">{{ $analysis['expiring_soon'] }}</div>
                            <div class="text-white/70 text-xs">Por Vencer</div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-seedling text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ $analysis['dry_products'] }}</div>
                            <div class="text-white/70 text-xs">Ingredientes Secos</div>
                        </div>
                    </div>
                @endif
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-red-300">{{ $analysis['low_stock'] }}</div>
                        <div class="text-white/70 text-xs">Stock Bajo</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">${{ number_format($analysis['total_value'], 0) }}</div>
                        <div class="text-white/70 text-xs">Valor Total</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('inventories.products.index', $inventory) }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-white mb-1">Buscar producto</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-md text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           placeholder="Nombre del producto...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-white mb-1">Estado</label>
                    <select name="status" 
                            class="px-3 py-2 bg-white/20 border border-white/30 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option value="">Todos</option>
                        @if($inventory->type === 'sale_product')
                            <option value="fresh" {{ request('status') === 'fresh' ? 'selected' : '' }}>Productos Frescos</option>
                            <option value="expiring" {{ request('status') === 'expiring' ? 'selected' : '' }}>Por Vencer</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Vencidos</option>
                        @endif
                        <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Stock Bajo</option>
                    </select>
                </div>
                
                <div>
                    <x-migapp.button variant="secondary" type="submit">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </x-migapp.button>
                </div>
                
                @if(request()->hasAny(['search', 'status']))
                    <div>
                        <a href="{{ route('inventories.products.index', $inventory) }}" 
                           class="inline-flex items-center px-4 py-2 bg-white/20 text-white text-sm font-medium rounded-md hover:bg-white/30 transition-colors">
                            <i class="fas fa-times mr-2"></i>Limpiar
                        </a>
                    </div>
                @endif
            </form>
        </div>
        
        <!-- Lista de productos -->
        <div class="glass-card rounded-lg overflow-hidden">
            @if($products->isEmpty())
                <div class="text-center py-12 px-6">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay productos</h3>
                    <p class="text-gray-500 mb-6">
                        @if(request()->hasAny(['search', 'status']))
                            No se encontraron productos con los filtros aplicados.
                        @else
                            Comienza agregando productos a este inventario.
                        @endif
                    </p>
                    
                    @can('create', App\Models\Product::class)
                        <x-migapp.button 
                            variant="primary" 
                            icon="fas fa-plus"
                            onclick="openModal('create-product')">
                            Agregar Producto
                        </x-migapp.button>
                    @endcan
                </div>
            @else
                <!-- Encabezados de tabla -->
                <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                    <div class="grid grid-cols-12 gap-4 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="col-span-3">Producto</div>
                        <div class="col-span-1 text-center">Precio</div>
                        <div class="col-span-2 text-center">Stock</div>
                        <div class="col-span-2 text-center">Estado</div>
                        @if($inventory->type === 'sale_product')
                            <div class="col-span-2 text-center">Vencimiento</div>
                        @else
                            <div class="col-span-2 text-center">Información</div>
                        @endif
                        <div class="col-span-2 text-center">Acciones</div>
                    </div>
                </div>
                
                <!-- Filas de productos -->
                <div class="divide-y divide-gray-200">
                    @foreach($products as $product)
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="grid grid-cols-12 gap-4 items-center">
                                <!-- Información del producto -->
                                <div class="col-span-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($product->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800 text-sm leading-5">{{ $product->name }}</h3>
                                            <p class="text-xs text-gray-500">
                                                {{ $product->unit ?? 'unidades' }}
                                                @if($inventory->type === 'sale_product' && $product->expiration_days <= 3)
                                                    <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs bg-green-100 text-green-800">
                                                        <i class="fas fa-bread-slice mr-1"></i>Fresco
                                                    </span>
                                                @elseif($inventory->type === 'raw_material' && $product->expiration_days > 30)
                                                    <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs bg-orange-100 text-orange-800">
                                                        <i class="fas fa-seedling mr-1"></i>Seco
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Precio -->
                                <div class="col-span-1 text-center">
                                    <span class="font-semibold text-gray-800">${{ number_format($product->price, 0) }}</span>
                                </div>
                                
                                <!-- Stock con barra de progreso -->
                                <div class="col-span-2">
                                    <div class="text-center mb-1">
                                        <span class="font-medium {{ $product->is_low_stock ? 'text-red-600' : 'text-gray-800' }}">
                                            {{ $product->stock }}
                                        </span>
                                        <span class="text-gray-400 text-xs">/ {{ $product->min_stock }} min</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        @php
                                            $percentage = $product->min_stock > 0 ? min(100, ($product->stock / $product->min_stock) * 100) : 100;
                                            $colorClass = $percentage <= 50 ? 'bg-red-500' : ($percentage <= 80 ? 'bg-amber-500' : 'bg-green-500');
                                        @endphp
                                        <div class="h-2 rounded-full {{ $colorClass }}" style="width: {{ max(5, $percentage) }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Estado -->
                                <div class="col-span-2 text-center">
                                    <div class="flex flex-wrap justify-center gap-1">
                                        @if(isset($product->is_expired) && $product->is_expired)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i>Vencido
                                            </span>
                                        @elseif(isset($product->is_expiring) && $product->is_expiring)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                <i class="fas fa-clock mr-1"></i>Por vencer
                                            </span>
                                        @endif
                                        
                                        @if($product->is_low_stock)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Stock bajo
                                            </span>
                                        @endif
                                        
                                        @if(!$product->is_low_stock && !isset($product->is_expired) && !isset($product->is_expiring))
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Normal
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Información adicional -->
                                <div class="col-span-2 text-center">
                                    @if($product->lote)
                                        <div class="text-xs text-gray-600">
                                            <i class="fas fa-calendar mr-1"></i>{{ $product->lote->format('d/m/Y') }}
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-500 mt-1">
                                        Valor: ${{ number_format($product->stock * $product->price, 0) }}
                                    </div>
                                </div>
                                
                                <!-- Botones de acción -->
                                <div class="col-span-2">
                                    <div class="flex justify-center space-x-2">
                                        @if(in_array(auth()->user()->role, ['owner', 'manager', 'employee']))
                                            <button onclick="editProduct({{ $product->id_product }})" 
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-amber-600 bg-amber-100 rounded-md hover:bg-amber-200 transition-colors" 
                                                    title="Editar producto y stock">
                                                <i class="fas fa-edit mr-1"></i>Editar
                                            </button>
                                        @endif
                                        
                                        @if(in_array(auth()->user()->role, ['owner', 'manager']))
                                            <button onclick="deleteProduct({{ $product->id_product }}, '{{ $product->name }}')" 
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-100 rounded-md hover:bg-red-200 transition-colors" 
                                                    title="Eliminar producto">
                                                <i class="fas fa-trash mr-1"></i>Eliminar
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <!-- Modal para Crear Producto -->
    <x-migapp.modal id="create-product" max-width="lg">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-plus-circle mr-2 text-blue-600"></i>
                Agregar Producto a {{ $inventory->name }}
            </h3>
        </x-slot>
        
        <form id="create-product-form" onsubmit="submitCreateProduct(event)">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="create-name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del Producto *
                        </label>
                        <input type="text" id="create-name" name="name" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               placeholder="Ej: Pan Blanco, Harina de Trigo" required>
                    </div>
                    
                    <div>
                        <label for="create-price" class="block text-sm font-medium text-gray-700 mb-1">
                            Precio *
                        </label>
                        <input type="number" id="create-price" name="price" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               required>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="create-stock" class="block text-sm font-medium text-gray-700 mb-1">
                            Stock Inicial *
                        </label>
                        <input type="number" id="create-stock" name="stock" min="0" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               required>
                    </div>
                    
                    <div>
                        <label for="create-min-stock" class="block text-sm font-medium text-gray-700 mb-1">
                            Stock Mínimo *
                        </label>
                        <input type="number" id="create-min-stock" name="min_stock" min="0" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               required>
                    </div>
                    
                    <div>
                        <label for="create-unit" class="block text-sm font-medium text-gray-700 mb-1">
                            Unidad
                        </label>
                        <select id="create-unit" name="unit" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option value="unidades">Unidades</option>
                            <option value="kg">Kilogramos</option>
                            <option value="g">Gramos</option>
                            <option value="l">Litros</option>
                            <option value="ml">Mililitros</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="create-lote" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha de Lote
                        </label>
                        <input type="date" id="create-lote" name="lote" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="create-expiration-days" class="block text-sm font-medium text-gray-700 mb-1">
                            Días de Vida Útil
                        </label>
                        <input type="number" id="create-expiration-days" name="expiration_days" min="1" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               placeholder="{{ $inventory->type === 'sale_product' ? '1-3 días para pan fresco' : '30+ días para ingredientes' }}">
                    </div>
                </div>
            </div>
            
            <div class="mt-6 text-sm text-gray-600">
                <p><strong>Consejos para {{ $inventory->type === 'sale_product' ? 'productos de venta' : 'materias primas' }}:</strong></p>
                <ul class="mt-2 space-y-1 text-xs">
                    @if($inventory->type === 'sale_product')
                        <li>• <strong>Pan fresco:</strong> 1-3 días de vida útil</li>
                        <li>• <strong>Pasteles:</strong> 3-7 días dependiendo del tipo</li>
                        <li>• <strong>Productos horneados:</strong> Verificar fecha de producción</li>
                    @else
                        <li>• <strong>Harinas:</strong> 6-12 meses en lugar seco</li>
                        <li>• <strong>Azúcar:</strong> No vence, pero verificar humedad</li>
                        <li>• <strong>Levadura:</strong> Verificar fecha de vencimiento</li>
                    @endif
                </ul>
            </div>
        </form>
        
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-migapp.button variant="secondary" onclick="closeModal('create-product')">
                    Cancelar
                </x-migapp.button>
                <x-migapp.button variant="primary" onclick="document.getElementById('create-product-form').requestSubmit()">
                    <i class="fas fa-save mr-2"></i>Crear Producto
                </x-migapp.button>
            </div>
        </x-slot>
    </x-migapp.modal>
    
    
    <!-- Modal para Editar Producto -->
    <x-migapp.modal id="edit-product" max-width="lg">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-edit mr-2 text-amber-600"></i>
                Editar Producto
            </h3>
        </x-slot>
        
        <form id="edit-product-form" onsubmit="submitEditProduct(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-product-id" name="product_id">
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del Producto *
                        </label>
                        <input type="text" id="edit-name" name="name" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               required>
                    </div>
                    
                    <div>
                        <label for="edit-price" class="block text-sm font-medium text-gray-700 mb-1">
                            Precio *
                        </label>
                        <input type="number" id="edit-price" name="price" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               required>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="edit-stock" class="block text-sm font-medium text-gray-700 mb-1">
                            Stock Actual *
                        </label>
                        <input type="number" id="edit-stock" name="stock" min="0" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               required>
                    </div>
                    
                    <div>
                        <label for="edit-min-stock" class="block text-sm font-medium text-gray-700 mb-1">
                            Stock Mínimo *
                        </label>
                        <input type="number" id="edit-min-stock" name="min_stock" min="0" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               required>
                    </div>
                    
                    <div>
                        <label for="edit-unit" class="block text-sm font-medium text-gray-700 mb-1">
                            Unidad
                        </label>
                        <select id="edit-unit" name="unit" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option value="unidades">Unidades</option>
                            <option value="kg">Kilogramos</option>
                            <option value="g">Gramos</option>
                            <option value="l">Litros</option>
                            <option value="ml">Mililitros</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit-lote" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha de Lote
                        </label>
                        <input type="date" id="edit-lote" name="lote" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="edit-expiration-days" class="block text-sm font-medium text-gray-700 mb-1">
                            Días de Vida Útil
                        </label>
                        <input type="number" id="edit-expiration-days" name="expiration_days" min="1" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                </div>
            </div>
        </form>
        
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-migapp.button variant="secondary" onclick="closeModal('edit-product')">
                    Cancelar
                </x-migapp.button>
                <x-migapp.button variant="primary" onclick="document.getElementById('edit-product-form').requestSubmit()">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </x-migapp.button>
            </div>
        </x-slot>
    </x-migapp.modal>
    
@endsection

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
</style>
@endpush

@push('scripts')
<script>
// Variables globales para productos
let currentProductId = null;
const inventoryId = {{ $inventory->id_inventory }};

// Función para crear producto
function submitCreateProduct(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Mostrar loading
    const submitButton = document.querySelector('button[onclick*="create-product-form"]');
    const originalText = submitButton ? submitButton.innerHTML : '';
    if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';
        submitButton.disabled = true;
    }
    
    fetch('{{ route("inventories.products.store", $inventory) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('create-product');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo crear el producto'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear el producto');
    })
    .finally(() => {
        if (submitButton) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    });
}


// Función para editar producto con debug mejorado
function editProduct(productId) {
    currentProductId = productId;
    
    console.log('=== INICIANDO EDICIÓN DE PRODUCTO ===');
    console.log('Product ID:', productId);
    console.log('Inventory ID:', inventoryId);
    
    const url = `{{ url('inventories') }}/${inventoryId}/products/${productId}`;
    console.log('URL de petición:', url);
    
    // Cargar datos del producto
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(async response => {
        console.log('=== RESPUESTA DEL SERVIDOR ===');
        console.log('Status:', response.status);
        console.log('Status Text:', response.statusText);
        console.log('Headers:', [...response.headers.entries()]);
        
        const responseText = await response.text();
        console.log('Response Text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
            console.log('Datos JSON parseados:', data);
        } catch (parseError) {
            console.error('Error al parsear JSON:', parseError);
            console.log('Respuesta cruda:', responseText);
            throw new Error('La respuesta no es JSON válido');
        }
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return data;
    })
    .then(data => {
        console.log('=== PROCESANDO DATOS ===');
        
        if (data.success && data.product) {
            const product = data.product;
            console.log('Producto recibido:', product);
            
            // Validar que todos los elementos del formulario existan
            const elements = {
                'edit-product-id': productId,
                'edit-name': product.name || '',
                'edit-price': product.price || '',
                'edit-stock': product.stock || '',
                'edit-min-stock': product.min_stock || '',
                'edit-unit': product.unit || 'unidades',
                'edit-lote': product.lote || '',
                'edit-expiration-days': product.expiration_days || ''
            };
            
            console.log('Valores a llenar:', elements);
            
            let allElementsFound = true;
            // Llenar formulario con validación
            for (const [elementId, value] of Object.entries(elements)) {
                const element = document.getElementById(elementId);
                if (element) {
                    element.value = value;
                    console.log(`✓ ${elementId} llenado con:`, value);
                } else {
                    console.error(`✗ Elemento ${elementId} no encontrado`);
                    allElementsFound = false;
                }
            }
            
            if (allElementsFound) {
                console.log('✓ Todos los elementos llenados correctamente');
                openModal('edit-product');
            } else {
                alert('Error: No se pudieron llenar todos los campos del formulario');
            }
        } else {
            console.error('Respuesta inválida del servidor:', data);
            const errorMsg = data.message || 'Respuesta inválida del servidor';
            alert('Error al cargar los datos del producto: ' + errorMsg);
        }
    })
    .catch(error => {
        console.error('=== ERROR EN LA PETICIÓN ===');
        console.error('Error completo:', error);
        console.error('Stack trace:', error.stack);
        alert('Error de conexión al cargar los datos del producto: ' + error.message);
    });
}

// Función para enviar edición
function submitEditProduct(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Mostrar loading
    const submitButton = document.querySelector('button[onclick*="edit-product-form"]');
    const originalText = submitButton ? submitButton.innerHTML : '';
    if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
        submitButton.disabled = true;
    }
    
    fetch(`{{ url('inventories') }}/${inventoryId}/products/${currentProductId}`, {
        method: 'POST', // Usar POST con _method=PUT
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('edit-product');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo actualizar el producto'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el producto');
    })
    .finally(() => {
        if (submitButton) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    });
}


// Función para eliminar producto con modal de confirmación
function deleteProduct(productId, productName) {
    // Crear y mostrar modal de confirmación personalizado
    const confirmModal = document.createElement('div');
    confirmModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    confirmModal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md mx-4 shadow-xl">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar Eliminación</h3>
                    <p class="text-sm text-gray-600">Esta acción no se puede deshacer</p>
                </div>
            </div>
            <p class="text-gray-700 mb-6">
                ¿Estás seguro de que deseas eliminar el producto <strong>"${productName}"</strong>?
            </p>
            <div class="flex justify-end space-x-3">
                <button id="cancel-delete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>
                <button id="confirm-delete" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-1"></i>Eliminar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(confirmModal);
    
    // Manejar eventos del modal
    const cancelBtn = confirmModal.querySelector('#cancel-delete');
    const confirmBtn = confirmModal.querySelector('#confirm-delete');
    
    const closeModal = () => {
        document.body.removeChild(confirmModal);
    };
    
    cancelBtn.addEventListener('click', closeModal);
    
    confirmBtn.addEventListener('click', () => {
        // Deshabilitar botón y mostrar loading
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Eliminando...';
        
        console.log('Iniciando eliminación:', {
            productId,
            productName,
            inventoryId,
            url: `{{ url('inventories') }}/${inventoryId}/products/${productId}`
        });
        
        fetch(`{{ url('inventories') }}/${inventoryId}/products/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            console.log('Respuesta del servidor:', {
                status: response.status,
                statusText: response.statusText,
                url: response.url
            });
            
            const data = await response.json();
            console.log('Datos recibidos:', data);
            
            if (data.success) {
                closeModal();
                // Mostrar mensaje de éxito
                const successMessage = document.createElement('div');
                successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                successMessage.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Producto "${productName}" eliminado correctamente</span>
                    </div>
                `;
                document.body.appendChild(successMessage);
                
                setTimeout(() => {
                    if (document.body.contains(successMessage)) {
                        document.body.removeChild(successMessage);
                    }
                    window.location.reload();
                }, 2000);
            } else {
                // Mostrar error
                const errorMessage = data.message || 'No se pudo eliminar el producto';
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-trash mr-1"></i>Eliminar';
                
                alert('Error: ' + errorMessage);
                console.error('Error del servidor:', data);
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            closeModal();
            alert('Error de conexión al eliminar el producto');
        });
    });
    
    // Cerrar modal al hacer click fuera
    confirmModal.addEventListener('click', (e) => {
        if (e.target === confirmModal) {
            closeModal();
        }
    });
}

</script>
@endpush
