@extends('layouts.migapp')

@section('title', 'Ventas - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-shopping-cart mr-3"></i>Gestión de Ventas
                </h1>
                <p class="text-white/80 mt-2">
                    Registra y controla todas las ventas de tu panadería
                </p>
            </div>
            
<x-migapp.button 
                variant="primary" 
                icon="fas fa-plus"
                onclick="openModal('cart-modal')">
                Nueva Venta
            </x-migapp.button>
        </div>
        
        <!-- Estadísticas -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 stats-container">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['total_sales'] }}</div>
                        <div class="text-white/70 text-xs">Total Ventas</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-300">${{ number_format($stats['total_amount'], 2) }}</div>
                        <div class="text-white/70 text-xs">Monto Total</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-300">${{ number_format($stats['average_sale'], 2) }}</div>
                        <div class="text-white/70 text-xs">Promedio</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-day text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-amber-300">{{ $stats['today_sales'] }}</div>
                        <div class="text-white/70 text-xs">Hoy</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-coins text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-emerald-300">${{ number_format($stats['today_amount'], 2) }}</div>
                        <div class="text-white/70 text-xs">Ingresos Hoy</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de ventas -->
        <div class="glass-card rounded-lg p-6">
            @if($sales->isEmpty())
<div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay ventas registradas</h3>
                    <p class="text-gray-500 mb-6">Registra tu primera venta para comenzar</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Venta
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cliente/Empleado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sucursal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-receipt text-green-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    #{{ str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $sale->products->count() }} productos
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sale->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $sale->user->role_name ?? ucfirst($sale->user->role) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sale->date->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">
                                            ${{ number_format($sale->total, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $sale->branch->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('sales.show', $sale) }}" 
                                               class="text-blue-600 hover:text-blue-900 inline-flex items-center px-2 py-1 text-xs bg-blue-100 rounded" 
                                               title="Ver factura" target="_blank">
                                                <i class="fas fa-eye mr-1"></i>Ver
                                            </a>
                                            
                                            <button onclick="printInvoice({{ $sale->id_sale }})" 
                                                    class="text-purple-600 hover:text-purple-900 inline-flex items-center px-2 py-1 text-xs bg-purple-100 rounded" 
                                                    title="Imprimir factura">
                                                <i class="fas fa-print mr-1"></i>Imprimir
                                            </button>
                                            
                                            <button onclick="emailInvoice({{ $sale->id_sale }})" 
                                                    class="text-green-600 hover:text-green-900 inline-flex items-center px-2 py-1 text-xs bg-green-100 rounded" 
                                                    title="Enviar por email">
                                                <i class="fas fa-envelope mr-1"></i>Email
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <!-- Filtros -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('sales.index') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-white mb-1">Fecha Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-white mb-1">Fecha Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                
                @if(auth()->user()->role === 'owner' && $availableBranches->count() > 1)
                <div class="min-w-[150px]">
                    <label class="block text-sm font-medium text-white mb-1">Sucursal</label>
                    <select name="branch_id" 
                            class="px-3 py-2 bg-white/20 border border-white/30 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option value="">Todas</option>
                        @foreach($availableBranches as $branch)
                            <option value="{{ $branch->id_branch }}" {{ request('branch_id') == $branch->id_branch ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div>
                    <x-migapp.button variant="secondary" type="submit">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </x-migapp.button>
                </div>
                
                @if(request()->hasAny(['date_from', 'date_to', 'branch_id']))
                    <div>
                        <a href="{{ route('sales.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-white/20 text-white text-sm font-medium rounded-md hover:bg-white/30 transition-colors">
                            <i class="fas fa-times mr-2"></i>Limpiar
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
    
    <!-- Modal del Carrito de Compras -->
    <x-migapp.modal id="cart-modal" max-width="2xl">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-shopping-cart mr-2 text-green-600"></i>
                Nueva Venta - Carrito de Compras
            </h3>
        </x-slot>
        
        <div class="grid grid-cols-3 gap-6">
            <!-- Panel de productos disponibles -->
            <div class="col-span-2">
                <h4 class="text-md font-semibold text-gray-800 mb-4">
                    <i class="fas fa-bread-slice mr-2"></i>Productos Disponibles
                </h4>
                
                <div class="mb-4">
                    <input type="text" id="product-search" placeholder="Buscar producto..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div id="products-grid" class="grid grid-cols-2 gap-3 max-h-96 overflow-y-auto">
                    @if(isset($products))
                        @foreach($products as $product)
                            <div class="product-card border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer" 
                                 data-product-id="{{ $product->id_product }}"
                                 data-product-name="{{ $product->name }}"
                                 data-product-price="{{ $product->price }}"
                                 data-product-stock="{{ $product->stock }}"
                                 onclick="addToCart({{ $product->id_product }}, '{{ $product->name }}', {{ $product->price }}, {{ $product->stock }})">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center text-white font-semibold text-xs">
                                        {{ strtoupper(substr($product->name, 0, 2)) }}
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="text-sm font-semibold text-gray-800 leading-tight">{{ $product->name }}</h5>
                                        <div class="text-xs text-gray-500">
                                            Stock: {{ $product->stock }} {{ $product->unit ?? 'unidades' }}
                                        </div>
                                        <div class="text-sm font-bold text-green-600">
                                            ${{ number_format($product->price, 0) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-span-2 text-center text-gray-500 py-8">
                            <i class="fas fa-box text-3xl mb-2"></i>
                            <p>No hay productos disponibles</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Carrito de compras -->
            <div class="col-span-1">
                <h4 class="text-md font-semibold text-gray-800 mb-4">
                    <i class="fas fa-shopping-bag mr-2"></i>Carrito
                </h4>
                
                <div id="cart-items" class="space-y-2 mb-4 max-h-64 overflow-y-auto">
                    <div id="empty-cart" class="text-center text-gray-500 py-8">
                        <i class="fas fa-shopping-cart text-3xl mb-2"></i>
                        <p class="text-sm">Carrito vacío</p>
                    </div>
                </div>
                
                <!-- Total -->
                <div class="border-t border-gray-200 pt-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-lg font-semibold text-gray-800">Total:</span>
                        <span id="cart-total" class="text-xl font-bold text-green-600">$0</span>
                    </div>
                    <div class="text-sm text-gray-500">
                        <span id="cart-items-count">0</span> producto(s)
                    </div>
                </div>
                
                <!-- Email del cliente (opcional) -->
                <div class="mb-4">
                    <label for="customer-email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email del cliente (opcional)
                    </label>
                    <input type="email" id="customer-email" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="correo@ejemplo.com">
                    <p class="text-xs text-gray-500 mt-1">Para enviar la factura automáticamente</p>
                </div>
                
                <!-- Sucursal -->
                <div class="mb-4">
                    <label for="sale-branch" class="block text-sm font-medium text-gray-700 mb-1">
                        Sucursal *
                    </label>
                    <select id="sale-branch" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        @if(isset($availableBranches))
                            @foreach($availableBranches as $branch)
                                <option value="{{ $branch->id_branch }}" {{ $loop->first ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <!-- Justificación -->
                <div class="mb-6">
                    <label for="sale-justify" class="block text-sm font-medium text-gray-700 mb-1">
                        Observaciones
                    </label>
                    <textarea id="sale-justify" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>
        </div>
        
        <x-slot name="footer">
            <div class="flex justify-between items-center w-full">
                <button onclick="clearCart()" 
                        class="px-4 py-2 text-sm font-medium text-red-600 bg-red-100 rounded-md hover:bg-red-200 transition-colors">
                    <i class="fas fa-trash mr-1"></i>Limpiar Carrito
                </button>
                
                <div class="flex space-x-3">
                    <x-migapp.button variant="secondary" onclick="closeModal('cart-modal')">
                        Cancelar
                    </x-migapp.button>
                    <x-migapp.button variant="primary" onclick="processSale()">
                        <i class="fas fa-cash-register mr-2"></i>Procesar Venta
                    </x-migapp.button>
                </div>
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
    
    .product-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .cart-item {
        transition: all 0.2s ease;
    }
    
    .cart-item:hover {
        background-color: #f9fafb;
    }
</style>
@endpush

@push('scripts')
<script>
// Variables globales del carrito
let cart = [];
let cartTotal = 0;

// Función para añadir producto al carrito
function addToCart(productId, productName, productPrice, productStock) {
    console.log('Agregando producto:', {productId, productName, productPrice, productStock});
    
    // Verificar stock disponible
    if (productStock <= 0) {
        alert(`No hay stock disponible para ${productName}`);
        return;
    }
    
    // Verificar si el producto ya está en el carrito
    const existingItemIndex = cart.findIndex(item => item.id_product === productId);
    
    if (existingItemIndex !== -1) {
        // Si ya existe, incrementar cantidad (si hay stock)
        const currentQty = cart[existingItemIndex].quantity;
        if (currentQty < productStock) {
            cart[existingItemIndex].quantity++;
            cart[existingItemIndex].subtotal = cart[existingItemIndex].quantity * productPrice;
            console.log('Producto actualizado en carrito:', cart[existingItemIndex]);
        } else {
            alert(`No hay más stock disponible para ${productName}. Stock disponible: ${productStock}`);
            return;
        }
    } else {
        // Si no existe, añadir nuevo item
        const newItem = {
            id_product: productId,
            name: productName,
            unit_price: productPrice,
            quantity: 1,
            stock: productStock,
            subtotal: productPrice
        };
        cart.push(newItem);
        console.log('Nuevo producto agregado:', newItem);
    }
    
    console.log('Carrito actualizado:', cart);
    updateCartDisplay();
}

// Función para actualizar la visualización del carrito
function updateCartDisplay() {
    const cartItemsContainer = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');
    const cartTotalElement = document.getElementById('cart-total');
    const cartItemsCount = document.getElementById('cart-items-count');
    
    if (cart.length === 0) {
        // Mostrar carrito vacío y limpiar contenedor
        cartItemsContainer.innerHTML = '<div id="empty-cart" class="text-center text-gray-500 py-8"><i class="fas fa-shopping-cart text-3xl mb-2"></i><p class="text-sm">Carrito vacío</p></div>';
        cartTotal = 0;
    } else {
        // Limpiar contenedor completamente
        cartItemsContainer.innerHTML = '';
        cartTotal = 0;
        
        // Añadir cada item del carrito
        cart.forEach((item, index) => {
            cartTotal += item.subtotal;
            
            const cartItemDiv = document.createElement('div');
            cartItemDiv.className = 'cart-item border border-gray-200 rounded-lg p-3 bg-white';
            cartItemDiv.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <h6 class="text-sm font-semibold text-gray-800">${item.name}</h6>
                    <button onclick="removeFromCart(${index})" 
                            class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button onclick="decreaseQuantity(${index})" 
                                class="w-6 h-6 bg-gray-200 rounded text-xs hover:bg-gray-300">-</button>
                        <span class="text-sm font-medium">${item.quantity}</span>
                        <button onclick="increaseQuantity(${index})" 
                                class="w-6 h-6 bg-gray-200 rounded text-xs hover:bg-gray-300">+</button>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">$${item.unit_price.toLocaleString()}</div>
                        <div class="text-sm font-bold text-green-600">$${item.subtotal.toLocaleString()}</div>
                    </div>
                </div>
            `;
            
            cartItemsContainer.appendChild(cartItemDiv);
        });
    }
    
    // Actualizar totales
    cartTotalElement.textContent = '$' + cartTotal.toLocaleString();
    cartItemsCount.textContent = cart.reduce((total, item) => total + item.quantity, 0);
}

// Función para remover producto del carrito
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

// Función para incrementar cantidad
function increaseQuantity(index) {
    if (cart[index].quantity < cart[index].stock) {
        cart[index].quantity++;
        cart[index].subtotal = cart[index].quantity * cart[index].unit_price;
        updateCartDisplay();
    } else {
        alert(`No hay más stock disponible para ${cart[index].name}`);
    }
}

// Función para decrementar cantidad
function decreaseQuantity(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
        cart[index].subtotal = cart[index].quantity * cart[index].unit_price;
        updateCartDisplay();
    } else {
        removeFromCart(index);
    }
}

// Función para limpiar carrito
function clearCart() {
    if (cart.length > 0) {
        if (confirm('¿Está seguro de limpiar el carrito?')) {
            cart = [];
            cartTotal = 0;
            updateCartDisplay();
            
            // Limpiar también los campos del formulario
            document.getElementById('customer-email').value = '';
            document.getElementById('sale-justify').value = '';
        }
    }
}

// Función para procesar la venta
function processSale() {
    if (cart.length === 0) {
        alert('El carrito está vacío. Agregue productos antes de procesar la venta.');
        return;
    }
    
    const branchId = document.getElementById('sale-branch').value;
    const customerEmail = document.getElementById('customer-email').value;
    const justify = document.getElementById('sale-justify').value;
    
    if (!branchId) {
        alert('Debe seleccionar una sucursal.');
        return;
    }
    
    // Preparar datos para enviar
    const saleData = {
        id_branch: branchId,
        customer_email: customerEmail,
        justify: justify,
        items: cart
    };
    
    // Deshabilitar botón y mostrar loading
    const submitButton = document.querySelector('button[onclick="processSale()"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
    
    // Enviar petición AJAX
    fetch('{{ route("sales.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(saleData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            alert(data.message);
            
            // Limpiar carrito y cerrar modal
            clearCart();
            closeModal('cart-modal');
            
            // Recargar página para mostrar nueva venta
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al procesar la venta');
    })
    .finally(() => {
        // Restaurar botón
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

// Función para buscar productos
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    const productCards = document.querySelectorAll('.product-card');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            productCards.forEach(card => {
                const productName = card.dataset.productName.toLowerCase();
                if (productName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});

// Función para enviar factura por email
function emailInvoice(saleId) {
    const email = prompt('Ingrese el email del cliente:');
    
    if (!email) {
        return; // Usuario canceló
    }
    
    // Validar formato de email básico
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Por favor, ingrese una dirección de email válida.');
        return;
    }
    
    // Mostrar mensaje de envío
    const originalAlert = window.alert;
    window.alert = function(message) {
        // No mostrar alert, usar notificación visual en su lugar
        showNotification(message, 'info');
    };
    
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
        window.alert = originalAlert; // Restaurar alert original
        
        if (data.success) {
            showNotification(`✅ Factura enviada exitosamente a ${email}`, 'success');
        } else {
            showNotification(`❌ Error: ${data.message}`, 'error');
        }
    })
    .catch(error => {
        window.alert = originalAlert; // Restaurar alert original
        console.error('Error:', error);
        showNotification('❌ Error de conexión al enviar el email', 'error');
    });
}

// Función para mostrar notificaciones visuales
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium min-w-[300px] max-w-[500px] transform translate-x-full opacity-0 transition-all duration-300`;
    
    // Aplicar colores según el tipo
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
    
    // Agregar al DOM
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
        notification.classList.add('translate-x-0', 'opacity-100');
    }, 100);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Función para imprimir factura
function printInvoice(saleId) {
    // Abrir la factura en una nueva ventana para imprimir
    const printWindow = window.open(`{{ url('/sales') }}/${saleId}?print=1`, '_blank', 'width=800,height=600');
    
    if (printWindow) {
        printWindow.onload = function() {
            // Esperar un poco para que cargue completamente, luego imprimir
            setTimeout(() => {
                printWindow.print();
            }, 500);
        };
    } else {
        alert('No se pudo abrir la ventana de impresión. Verifique que no esté bloqueando ventanas emergentes.');
    }
}

// Función para eliminar venta
function deleteSale(saleId, saleDate, saleTotal) {
    if (confirm(`¿Está seguro de eliminar la venta del ${saleDate} por $${saleTotal.toLocaleString()}?\n\nEsta acción restaurará el stock de los productos y no se puede deshacer.`)) {
        fetch(`{{ url('/sales') }}/${saleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
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
