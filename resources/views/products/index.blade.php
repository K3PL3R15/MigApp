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
        <div class="glass-card rounded-lg p-6">
            @if($products->isEmpty())
                <div class="text-center py-12">
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($products as $product)
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-4 relative">
                            <!-- Indicadores de estado -->
                            <div class="absolute top-2 right-2 flex flex-col space-y-1">
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
                            </div>
                            
                            <!-- Información del producto -->
                            <div class="mb-3">
                                <h3 class="font-semibold text-gray-800 text-sm mb-1 pr-16">{{ $product->name }}</h3>
                                <p class="text-gray-600 text-xs">
                                    <i class="fas fa-tag mr-1"></i>{{ $product->formatted_price ?? '$' . number_format($product->price, 0) }}
                                    @if($product->lote)
                                        <span class="ml-2"><i class="fas fa-calendar mr-1"></i>{{ $product->lote->format('d/m/Y') }}</span>
                                    @endif
                                </p>
                            </div>
                            
                            <!-- Stock -->
                            <div class="mb-3">
                                <div class="flex justify-between items-center text-sm mb-1">
                                    <span class="text-gray-600">Stock</span>
                                    <span class="font-medium {{ $product->is_low_stock ? 'text-red-600' : 'text-gray-800' }}">
                                        {{ $product->stock }} {{ $product->unit ?? 'unidades' }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    @php
                                        $percentage = $product->min_stock > 0 ? min(100, ($product->stock / $product->min_stock) * 100) : 100;
                                        $colorClass = $percentage <= 50 ? 'bg-red-500' : ($percentage <= 80 ? 'bg-amber-500' : 'bg-green-500');
                                    @endphp
                                    <div class="h-2 rounded-full {{ $colorClass }}" style="width: {{ max(5, $percentage) }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>Min: {{ $product->min_stock }}</span>
                                    <span>Valor: ${{ number_format($product->stock * $product->price, 0) }}</span>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="flex justify-between items-center">
                                <div class="flex space-x-1">
                                    <button onclick="viewProduct({{ $product->id_product }})" 
                                            class="text-blue-600 hover:text-blue-800 text-sm" 
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    @can('update', $product)
                                        <button onclick="editProduct({{ $product->id_product }})" 
                                                class="text-amber-600 hover:text-amber-800 text-sm" 
                                                title="Editar producto">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button onclick="adjustStock({{ $product->id_product }})" 
                                                class="text-green-600 hover:text-green-800 text-sm" 
                                                title="Ajustar stock">
                                            <i class="fas fa-adjust"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('delete', $product)
                                        <button onclick="deleteProduct({{ $product->id_product }}, '{{ $product->name }}')" 
                                                class="text-red-600 hover:text-red-800 text-sm" 
                                                title="Eliminar producto">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                                
                                @if($inventory->type === 'sale_product' && $product->expiration_days <= 3)
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-bread-slice mr-1"></i>Fresco
                                    </div>
                                @elseif($inventory->type === 'raw_material' && $product->expiration_days > 30)
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-seedling mr-1"></i>Seco
                                    </div>
                                @endif
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
    
    <!-- Modal para Ver Producto -->
    <x-migapp.modal id="view-product" max-width="xl">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-eye mr-2 text-blue-600"></i>
                <span id="view-product-title">Detalles del Producto</span>
            </h3>
        </x-slot>
        
        <div id="view-product-content" class="space-y-4">
            <x-migapp.loading type="spinner" message="Cargando detalles..." />
        </div>
        
        <x-slot name="footer">
            <div class="flex justify-end">
                <x-migapp.button variant="secondary" onclick="closeModal('view-product')">
                    Cerrar
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
    
    <!-- Modal para Ajustar Stock -->
    <x-migapp.modal id="adjust-stock" max-width="md">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-adjust mr-2 text-green-600"></i>
                <span id="adjust-stock-title">Ajustar Stock</span>
            </h3>
        </x-slot>
        
        <form id="adjust-stock-form" onsubmit="submitAdjustStock(event)">
            @csrf
            <input type="hidden" id="adjust-product-id" name="product_id">
            
            <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-2">Información Actual</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="text-gray-600">Producto:</label>
                            <p id="adjust-product-name" class="font-medium text-gray-800">-</p>
                        </div>
                        <div>
                            <label class="text-gray-600">Stock Actual:</label>
                            <p id="adjust-current-stock" class="font-medium text-gray-800">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="adjustment" class="block text-sm font-medium text-gray-700 mb-1">
                            Ajuste *
                        </label>
                        <input type="number" id="adjustment" name="adjustment" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="+10 para agregar, -5 para quitar" required>
                        <p class="text-xs text-gray-500 mt-1">Números positivos agregan, negativos quitan</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nuevo Stock
                        </label>
                        <div id="new-stock-preview" class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">
                            -
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                        Razón del Ajuste *
                    </label>
                    <select id="reason" name="reason" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            required>
                        <option value="">Seleccionar razón...</option>
                        <option value="Producción diaria">Producción diaria</option>
                        <option value="Venta">Venta</option>
                        <option value="Merma">Merma/Desperdicio</option>
                        <option value="Devolución">Devolución</option>
                        <option value="Inventario físico">Conteo físico</option>
                        <option value="Corrección">Corrección de error</option>
                        <option value="Otro">Otro motivo</option>
                    </select>
                </div>
                
                <div id="other-reason" class="hidden">
                    <label for="other-reason-text" class="block text-sm font-medium text-gray-700 mb-1">
                        Especificar razón
                    </label>
                    <input type="text" id="other-reason-text" name="other_reason" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Describe el motivo del ajuste...">
                </div>
            </div>
        </form>
        
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-migapp.button variant="secondary" onclick="closeModal('adjust-stock')">
                    Cancelar
                </x-migapp.button>
                <x-migapp.button variant="primary" onclick="document.getElementById('adjust-stock-form').requestSubmit()">
                    <i class="fas fa-check mr-2"></i>Ajustar Stock
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

// Función para ver producto
function viewProduct(productId) {
    currentProductId = productId;
    
    // Abrir modal y mostrar loading
    openModal('view-product');
    document.getElementById('view-product-content').innerHTML = `
        <div class="flex items-center justify-center py-8">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-2"></i>
                <p class="text-gray-600">Cargando detalles del producto...</p>
            </div>
        </div>
    `;
    
    fetch(`/inventories/${inventoryId}/products/${productId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayProductDetails(data.product, data.bakery_info);
        } else {
            document.getElementById('view-product-content').innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                    <p>Error al cargar los detalles</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('view-product-content').innerHTML = `
            <div class="text-center py-8 text-red-600">
                <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                <p>Error al cargar los detalles</p>
            </div>
        `;
    });
}

// Función para mostrar detalles del producto
function displayProductDetails(product, bakeryInfo) {
    document.getElementById('view-product-title').textContent = product.name;
    
    const expirationDate = product.lote && product.expiration_days ? 
        new Date(new Date(product.lote).getTime() + (product.expiration_days * 24 * 60 * 60 * 1000)) : null;
    
    const content = `
        <div class="space-y-6">
            <!-- Información básica -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3">Información General</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Nombre:</label>
                        <p class="text-gray-800">${product.name}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Precio:</label>
                        <p class="text-gray-800">$${parseFloat(product.price).toLocaleString()}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Unidad:</label>
                        <p class="text-gray-800">${product.unit || 'unidades'}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Valor Total:</label>
                        <p class="text-gray-800 font-semibold">$${(product.stock * product.price).toLocaleString()}</p>
                    </div>
                </div>
            </div>
            
            <!-- Stock -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3">Control de Stock</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                        <div class="text-2xl font-bold ${product.stock <= product.min_stock ? 'text-red-600' : 'text-green-600'}">                            ${product.stock}
                        </div>
                        <div class="text-sm text-gray-600 mt-1">Stock Actual</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                        <div class="text-2xl font-bold text-amber-600">${product.min_stock}</div>
                        <div class="text-sm text-gray-600 mt-1">Stock Mínimo</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                        <div class="text-2xl font-bold text-blue-600">${product.stock > product.min_stock ? '✓' : '⚠'}</div>
                        <div class="text-sm text-gray-600 mt-1">Estado</div>
                    </div>
                </div>
            </div>
            
            <!-- Información de panadería -->
            ${product.lote || product.expiration_days ? `
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-3">Información de Producción</h4>
                    <div class="grid grid-cols-2 gap-4">
                        ${product.lote ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Fecha de Lote:</label>
                                <p class="text-gray-800">${new Date(product.lote).toLocaleDateString('es-ES')}</p>
                            </div>
                        ` : ''}
                        ${product.expiration_days ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Vida Útil:</label>
                                <p class="text-gray-800">${product.expiration_days} días</p>
                            </div>
                        ` : ''}
                        ${expirationDate ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Fecha de Vencimiento:</label>
                                <p class="text-gray-800 ${expirationDate < new Date() ? 'text-red-600 font-semibold' : ''}">
                                    ${expirationDate.toLocaleDateString('es-ES')}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Estado:</label>
                                <p class="text-gray-800">
                                    ${expirationDate < new Date() ? 
                                        '<span class="text-red-600 font-semibold">🔴 Vencido</span>' : 
                                        expirationDate < new Date(Date.now() + 7*24*60*60*1000) ? 
                                        '<span class="text-amber-600 font-semibold">🟡 Por vencer</span>' :
                                        '<span class="text-green-600 font-semibold">🟢 Fresco</span>'
                                    }
                                </p>
                            </div>
                        ` : ''}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('view-product-content').innerHTML = content;
}

// Función para editar producto
function editProduct(productId) {
    currentProductId = productId;
    
    // Cargar datos del producto
    fetch(`/inventories/${inventoryId}/products/${productId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const product = data.product;
            // Llenar formulario con datos actuales
            document.getElementById('edit-product-id').value = productId;
            document.getElementById('edit-name').value = product.name;
            document.getElementById('edit-price').value = product.price;
            document.getElementById('edit-stock').value = product.stock;
            document.getElementById('edit-min-stock').value = product.min_stock;
            document.getElementById('edit-unit').value = product.unit || 'unidades';
            document.getElementById('edit-lote').value = product.lote || '';
            document.getElementById('edit-expiration-days').value = product.expiration_days || '';
            
            openModal('edit-product');
        } else {
            alert('Error al cargar los datos del producto');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los datos del producto');
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
    
    fetch(`/inventories/${inventoryId}/products/${currentProductId}`, {
        method: 'PUT',
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

// Función para ajustar stock
function adjustStock(productId) {
    currentProductId = productId;
    
    // Cargar datos del producto
    fetch(`/inventories/${inventoryId}/products/${productId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const product = data.product;
            // Llenar información actual
            document.getElementById('adjust-product-id').value = productId;
            document.getElementById('adjust-product-name').textContent = product.name;
            document.getElementById('adjust-current-stock').textContent = `${product.stock} ${product.unit || 'unidades'}`;
            
            // Limpiar formulario
            document.getElementById('adjustment').value = '';
            document.getElementById('reason').value = '';
            document.getElementById('other-reason').classList.add('hidden');
            document.getElementById('new-stock-preview').textContent = '-';
            
            openModal('adjust-stock');
        } else {
            alert('Error al cargar los datos del producto');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los datos del producto');
    });
}

// Función para enviar ajuste de stock
function submitAdjustStock(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Combinar razón personalizada si es "Otro"
    const reason = formData.get('reason');
    const otherReason = formData.get('other_reason');
    if (reason === 'Otro' && otherReason) {
        formData.set('reason', otherReason);
    }
    
    // Mostrar loading
    const submitButton = document.querySelector('button[onclick*="adjust-stock-form"]');
    const originalText = submitButton ? submitButton.innerHTML : '';
    if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Ajustando...';
        submitButton.disabled = true;
    }
    
    fetch(`/inventories/${inventoryId}/products/${currentProductId}/adjust-stock`, {
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
            closeModal('adjust-stock');
            alert(`Stock ajustado correctamente.\nStock anterior: ${data.old_stock}\nStock nuevo: ${data.new_stock}`);
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo ajustar el stock'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al ajustar el stock');
    })
    .finally(() => {
        if (submitButton) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    });
}

// Función para eliminar producto
function deleteProduct(productId, productName) {
    if (confirm(`¿Estás seguro de que deseas eliminar el producto "${productName}"?`)) {
        fetch(`/inventories/${inventoryId}/products/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo eliminar el producto'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el producto');
        });
    }
}

// Event listeners para interacciones dinámicas
document.addEventListener('DOMContentLoaded', function() {
    // Preview de nuevo stock en tiempo real
    const adjustmentInput = document.getElementById('adjustment');
    const newStockPreview = document.getElementById('new-stock-preview');
    const currentStockElement = document.getElementById('adjust-current-stock');
    
    if (adjustmentInput && newStockPreview) {
        adjustmentInput.addEventListener('input', function() {
            const currentStockText = currentStockElement ? currentStockElement.textContent : '';
            const currentStock = parseInt(currentStockText.split(' ')[0]) || 0;
            const adjustment = parseInt(this.value) || 0;
            const newStock = currentStock + adjustment;
            
            if (this.value === '') {
                newStockPreview.textContent = '-';
                newStockPreview.className = 'w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700';
            } else {
                newStockPreview.textContent = newStock + ' ' + (currentStockText.split(' ')[1] || 'unidades');
                if (newStock < 0) {
                    newStockPreview.className = 'w-full px-3 py-2 bg-red-100 border border-red-300 rounded-md text-red-700 font-semibold';
                } else {
                    newStockPreview.className = 'w-full px-3 py-2 bg-green-100 border border-green-300 rounded-md text-green-700 font-semibold';
                }
            }
        });
    }
    
    // Mostrar/ocultar campo de razón personalizada
    const reasonSelect = document.getElementById('reason');
    const otherReasonDiv = document.getElementById('other-reason');
    const otherReasonInput = document.getElementById('other-reason-text');
    
    if (reasonSelect && otherReasonDiv && otherReasonInput) {
        reasonSelect.addEventListener('change', function() {
            if (this.value === 'Otro') {
                otherReasonDiv.classList.remove('hidden');
                otherReasonInput.required = true;
            } else {
                otherReasonDiv.classList.add('hidden');
                otherReasonInput.required = false;
                otherReasonInput.value = '';
            }
        });
    }
});
</script>
@endpush
