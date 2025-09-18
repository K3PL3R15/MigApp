@extends('layouts.migapp')

@section('title', 'Explorar Sucursales - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-search mr-3"></i>Explorar Sucursales
                </h1>
                <p class="text-white/80 mt-2">
                    Explora inventarios de otras sucursales para solicitar transferencias
                </p>
                @if($destinyBranch)
                    <div class="mt-2 inline-flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Destino: {{ $destinyBranch->name }}
                    </div>
                @endif
            </div>
            
            <div class="flex items-center space-x-3">
                <!-- Selector de sucursal destino para owners -->
                @if(auth()->user()->role === 'owner')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors font-medium flex items-center space-x-2">
                            <i class="fas fa-crosshairs"></i>
                            <span id="selected-destiny">Seleccionar destino</span>
                            <i class="fas fa-chevron-down text-xs" :class="{'rotate-180': open}"></i>
                        </button>
                        
                        <div x-show="open" 
                             x-transition
                             @click.away="open = false"
                             class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg py-1 z-[9999]">
                            @foreach($availableBranches as $branch)
                                <button onclick="selectDestinyBranch({{ $branch->id_branch }}, '{{ $branch->name }}')" 
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-store mr-2"></i>{{ $branch->name }}
                                    @if($branch->is_main)
                                        <span class="text-xs text-amber-600">(Principal)</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <a href="{{ route('branches.index') }}" 
                   class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Volver a Sucursales
                </a>
            </div>
        </div>
        
        <!-- Estadísticas generales -->
        @if($stats['total_branches'] > 0)
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
                <div class="flex flex-wrap justify-between items-center gap-4 stats-container">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-store text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ $stats['total_branches'] }}</div>
                            <div class="text-white/70 text-xs">Sucursales Disponibles</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-boxes text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ $stats['total_inventories'] }}</div>
                            <div class="text-white/70 text-xs">Total Inventarios</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-cube text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ $stats['products_with_stock'] }}</div>
                            <div class="text-white/70 text-xs">Productos Disponibles</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Lista de sucursales -->
        <div class="glass-card rounded-lg p-6">
            @if($availableBranches->isEmpty())
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-store-slash"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay sucursales disponibles</h3>
                    <p class="text-gray-500 mb-6">
                        @if(auth()->user()->role === 'manager')
                            No hay otras sucursales del propietario para explorar
                        @else
                            Crea más sucursales para poder realizar transferencias
                        @endif
                    </p>
                </div>
            @else
                <div class="space-y-6" x-data="branchExplorer()">
                    @foreach($availableBranches as $branch)
                        <div class="border border-gray-200 rounded-lg overflow-hidden branch-card" 
                             data-branch-id="{{ $branch->id_branch }}">
                            <!-- Branch Header -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 {{ $branch->is_main ? 'bg-amber-100' : 'bg-blue-100' }} rounded-lg flex items-center justify-center">
                                            <i class="fas {{ $branch->is_main ? 'fa-crown text-amber-600' : 'fa-store text-blue-600' }}"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800 flex items-center">
                                                {{ $branch->name }}
                                                @if($branch->is_main)
                                                    <span class="ml-2 text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full">Principal</span>
                                                @endif
                                            </h3>
                                            <p class="text-sm text-gray-600">{{ Str::limit($branch->direction, 50) }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-gray-800">{{ $branch->inventories->count() }}</div>
                                            <div class="text-xs text-gray-500">Inventarios</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-blue-600">
                                                {{ $branch->inventories->sum(function($inv) { 
                                                    return $inv->products->where('stock', '>', 0)->count(); 
                                                }) }}
                                            </div>
                                            <div class="text-xs text-gray-500">Productos</div>
                                        </div>
                                        <button @click="toggleBranch({{ $branch->id_branch }})"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors text-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            <span x-text="expandedBranches.includes({{ $branch->id_branch }}) ? 'Ocultar' : 'Explorar'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Branch Content (Inventarios) -->
                            <div x-show="expandedBranches.includes({{ $branch->id_branch }})"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 max-h-0"
                                 x-transition:enter-end="opacity-100 max-h-screen"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 max-h-screen"
                                 x-transition:leave-end="opacity-0 max-h-0"
                                 class="overflow-hidden">
                                
                                @if($branch->inventories->isEmpty())
                                    <div class="p-6 text-center text-gray-500">
                                        <i class="fas fa-box-open text-3xl mb-2"></i>
                                        <p>No hay inventarios en esta sucursal</p>
                                    </div>
                                @else
                                    @foreach($branch->inventories as $inventory)
                                        <div class="p-4 border-b border-gray-100 last:border-b-0">
                                            <!-- Inventory Header -->
                                            <div class="flex justify-between items-center mb-3">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-8 h-8 {{ $inventory->type === 'sale_product' ? 'bg-green-100' : 'bg-orange-100' }} rounded-lg flex items-center justify-center">
                                                        <i class="fas {{ $inventory->type === 'sale_product' ? 'fa-bread-slice text-green-600' : 'fa-seedling text-orange-600' }} text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium text-gray-800">{{ $inventory->name }}</h4>
                                                        <p class="text-xs text-gray-600">
                                                            {{ $inventory->type === 'sale_product' ? 'Productos de Venta' : 'Materias Primas' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <span class="text-sm text-gray-500">
                                                    {{ $inventory->products->where('stock', '>', 0)->count() }} productos disponibles
                                                </span>
                                            </div>
                                            
                                            <!-- Products Grid -->
                                            @if($inventory->products->where('stock', '>', 0)->isEmpty())
                                                <div class="text-center py-4 text-gray-500 text-sm">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    Sin productos con stock disponible
                                                </div>
                                            @else
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                    @foreach($inventory->products->where('stock', '>', 0) as $product)
                                                        <div class="bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition-colors border border-gray-200">
                                                            <div class="flex justify-between items-start mb-2">
                                                                <div class="flex-1">
                                                                    <h5 class="font-medium text-gray-800 text-sm">{{ $product->name }}</h5>
                                                                    <div class="text-xs text-gray-600 mt-1 space-y-1">
                                                                        <div class="flex justify-between">
                                                                            <span>Stock:</span>
                                                                            <span class="font-medium {{ $product->stock <= $product->min_stock ? 'text-amber-600' : 'text-green-600' }}">
                                                                                {{ $product->stock }}
                                                                            </span>
                                                                        </div>
                                                                        <div class="flex justify-between">
                                                                            <span>Precio:</span>
                                                                            <span class="font-medium">${{ number_format($product->price, 2) }}</span>
                                                                        </div>
                                                                        @if($product->lote)
                                                                            <div class="flex justify-between">
                                                                                <span>Lote:</span>
                                                                                <span class="text-xs">{{ $product->lote->format('d/m/Y') }}</span>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Add to Cart Button -->
                                                            <div class="flex items-center justify-between mt-3">
                                                                <div class="flex items-center space-x-2">
                                                                    <button onclick="decreaseQuantity('{{ $branch->id_branch }}-{{ $product->id_product }}')"
                                                                            class="w-6 h-6 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center text-xs">
                                                                        <i class="fas fa-minus"></i>
                                                                    </button>
                                                                    <input type="number" 
                                                                           id="qty-{{ $branch->id_branch }}-{{ $product->id_product }}"
                                                                           min="1" 
                                                                           max="{{ $product->stock }}"
                                                                           value="1"
                                                                           class="w-12 h-6 text-center text-xs border border-gray-300 rounded">
                                                                    <button onclick="increaseQuantity('{{ $branch->id_branch }}-{{ $product->id_product }}', {{ $product->stock }})"
                                                                            class="w-6 h-6 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center text-xs">
                                                                        <i class="fas fa-plus"></i>
                                                                    </button>
                                                                </div>
                                                                
                                                                <button onclick="addProductToCart({
                                                                    product_id: {{ $product->id_product }},
                                                                    product_name: '{{ $product->name }}',
                                                                    origin_branch_id: {{ $branch->id_branch }},
                                                                    origin_branch_name: '{{ $branch->name }}',
                                                                    destiny_branch_id: getSelectedDestinyBranchId(),
                                                                    destiny_branch_name: getSelectedDestinyBranchName(),
                                                                    available_stock: {{ $product->stock }},
                                                                    price: {{ $product->price }},
                                                                    quantity: parseInt(document.getElementById('qty-{{ $branch->id_branch }}-{{ $product->id_product }}').value)
                                                                })"
                                                                        class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                                    <i class="fas fa-cart-plus mr-1"></i>Agregar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <!-- Transfer Cart Component -->
    <x-migapp.transfer-cart />
@endsection

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .branch-card {
        transition: all 0.3s ease;
    }
    
    .branch-card:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .stats-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    @media (max-width: 768px) {
        .stats-container {
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// Variables globales para gestión de destino
let selectedDestinyBranchId = {{ auth()->user()->role === 'manager' ? auth()->user()->id_branch : 'null' }};
let selectedDestinyBranchName = '{{ auth()->user()->role === 'manager' ? auth()->user()->branch->name : '' }}';

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    if (selectedDestinyBranchId && selectedDestinyBranchName) {
        document.getElementById('selected-destiny').textContent = selectedDestinyBranchName;
    }
});

// Componente Alpine.js para manejo de exploración
function branchExplorer() {
    return {
        expandedBranches: [],
        
        toggleBranch(branchId) {
            const index = this.expandedBranches.indexOf(branchId);
            if (index >= 0) {
                this.expandedBranches.splice(index, 1);
            } else {
                this.expandedBranches.push(branchId);
            }
        }
    };
}

// Funciones para manejo de cantidad
function increaseQuantity(productKey, maxStock) {
    const input = document.getElementById(`qty-${productKey}`);
    const currentValue = parseInt(input.value);
    if (currentValue < maxStock) {
        input.value = currentValue + 1;
    }
}

function decreaseQuantity(productKey) {
    const input = document.getElementById(`qty-${productKey}`);
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

// Funciones para manejo de sucursal destino
function selectDestinyBranch(branchId, branchName) {
    selectedDestinyBranchId = branchId;
    selectedDestinyBranchName = branchName;
    document.getElementById('selected-destiny').textContent = branchName;
}

function getSelectedDestinyBranchId() {
    return selectedDestinyBranchId;
}

function getSelectedDestinyBranchName() {
    return selectedDestinyBranchName;
}

// Función para agregar producto al carrito
function addProductToCart(product) {
    if (!selectedDestinyBranchId) {
        showNotification('Por favor selecciona una sucursal de destino primero', 'warning');
        return;
    }
    
    if (product.origin_branch_id === selectedDestinyBranchId) {
        showNotification('No puedes transferir productos a la misma sucursal de origen', 'error');
        return;
    }
    
    // Usar la función global del carrito
    if (window.addToTransferCart) {
        window.addToTransferCart(product);
    } else {
        console.error('Transfer cart not initialized');
        showNotification('Error: Carrito de transferencias no disponible', 'error');
    }
}

// Sistema de notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium min-w-[300px] max-w-[500px] transform translate-x-full opacity-0 transition-all duration-300`;
    
    switch (type) {
        case 'success':
            notification.className += ' bg-green-600';
            break;
        case 'error':
            notification.className += ' bg-red-600';
            break;
        case 'warning':
            notification.className += ' bg-amber-600';
            break;
        default:
            notification.className += ' bg-blue-600';
    }
    
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="mr-3">
                    ${type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️'}
                </div>
                <div>${message}</div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white/70 hover:text-white">
                ✕
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
        notification.classList.add('translate-x-0', 'opacity-100');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Exponer función showNotification globalmente para el carrito
window.showNotification = showNotification;
</script>
@endpush
