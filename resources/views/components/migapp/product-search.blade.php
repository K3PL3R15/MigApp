@props([
    'placeholder' => 'Buscar productos...'
])

<div x-data="productSearch()" x-init="init()">
    <!-- Header del componente -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">
            <i class="fas fa-search text-amber-600 mr-2"></i>Buscar Productos para Transferir
        </h2>
        <p class="text-gray-600 text-sm">
            Busca productos disponibles en otras sucursales para solicitar transferencias
        </p>
    </div>
    
    <!-- Filtros superiores -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Selector de sucursal destino -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-crosshairs mr-1"></i>Sucursal de Destino *
            </label>
            <select x-model="destinationBranchId" 
                    @change="onDestinationChange()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option value="">Seleccionar sucursal destino...</option>
                <template x-for="branch in destinationBranches" :key="branch.id">
                    <option :value="branch.id" x-text="branch.display_name"></option>
                </template>
            </select>
        </div>
        
        <!-- Barra de búsqueda -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-search mr-1"></i>Buscar Productos
            </label>
            <div class="relative">
                <input type="text" 
                       x-model="searchTerm"
                       @input.debounce.500ms="searchProducts()"
                       @keydown.escape="clearSearch()"
                       :placeholder="destinationBranchId ? '{{ $placeholder }}' : 'Primero selecciona una sucursal destino'"
                       :disabled="!destinationBranchId"
                       class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                
                <button x-show="searchTerm.length > 0" 
                        @click="clearSearch()"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i class="fas fa-times text-gray-400 hover:text-gray-600"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="isSearching" class="text-center py-8">
        <div class="inline-flex items-center">
            <i class="fas fa-spinner fa-spin text-amber-600 text-xl mr-3"></i>
            <span class="text-gray-600">Buscando productos...</span>
        </div>
    </div>
    
    <!-- Estado vacío inicial -->
    <div x-show="!destinationBranchId && !isSearching" class="text-center py-12">
        <div class="text-gray-400 text-5xl mb-4">
            <i class="fas fa-crosshairs"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-700 mb-2">Selecciona una Sucursal Destino</h3>
        <p class="text-gray-500">Elige la sucursal a la cual quieres transferir productos</p>
    </div>
    
    <!-- Estado de búsqueda vacía -->
    <div x-show="destinationBranchId && searchTerm === '' && searchResults.length === 0 && !isSearching" class="text-center py-12">
        <div class="text-gray-400 text-5xl mb-4">
            <i class="fas fa-search"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-700 mb-2">Busca Productos</h3>
        <p class="text-gray-500">Escribe el nombre del producto que deseas transferir</p>
    </div>
    
    <!-- Sin resultados -->
    <div x-show="searchTerm.length > 0 && searchResults.length === 0 && !isSearching" class="text-center py-12">
        <div class="text-gray-400 text-5xl mb-4">
            <i class="fas fa-search-minus"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-700 mb-2">No se encontraron productos</h3>
        <p class="text-gray-500">Prueba con otros términos de búsqueda</p>
    </div>
    
    <!-- Resultados de búsqueda -->
    <div x-show="searchResults.length > 0" class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-800">
                <span x-text="searchResults.length"></span> producto<span x-text="searchResults.length === 1 ? '' : 's'"></span> encontrado<span x-text="searchResults.length === 1 ? '' : 's'"></span>
            </h3>
            <button @click="clearSearch()" 
                    class="text-sm text-amber-600 hover:text-amber-700 font-medium">
                <i class="fas fa-times mr-1"></i>Limpiar búsqueda
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="product in searchResults" :key="product.id_product">
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <!-- Product Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-800" x-text="product.name"></h4>
                            <div class="text-sm text-gray-600 mt-1">
                                <div class="flex items-center">
                                    <i class="fas fa-store text-xs mr-1"></i>
                                    <span x-text="product.branch_name"></span>
                                    <span x-show="product.branch_is_main" class="ml-1 text-xs text-amber-600">(Principal)</span>
                                </div>
                                <div class="flex items-center mt-1">
                                    <i :class="product.inventory_type === 'sale_product' ? 'fas fa-bread-slice text-green-600' : 'fas fa-seedling text-orange-600'" class="text-xs mr-1"></i>
                                    <span x-text="product.inventory_type_name"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stock indicator -->
                        <div class="text-right">
                            <div class="text-lg font-semibold" :class="product.is_low_stock ? 'text-amber-600' : 'text-green-600'" x-text="product.stock"></div>
                            <div class="text-xs text-gray-500">disponible</div>
                        </div>
                    </div>
                    
                    <!-- Product details -->
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 mb-3">
                        <div>
                            <span class="font-medium">Precio:</span>
                            <span x-text="product.formatted_price"></span>
                        </div>
                        <div x-show="product.lote">
                            <span class="font-medium">Lote:</span>
                            <span x-text="product.lote"></span>
                        </div>
                    </div>
                    
                    <!-- Warnings -->
                    <div x-show="product.is_low_stock" class="mb-3">
                        <div class="bg-amber-50 border-l-4 border-amber-400 p-2">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-amber-400 text-sm mr-2"></i>
                                <span class="text-xs text-amber-700">Stock bajo</span>
                            </div>
                        </div>
                    </div>
                    
                    <div x-show="product.is_expiring" class="mb-3">
                        <div class="bg-red-50 border-l-4 border-red-400 p-2">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-red-400 text-sm mr-2"></i>
                                <span class="text-xs text-red-700">Próximo a vencer</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quantity controls -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                        <div class="flex items-center space-x-2">
                            <button @click="decreaseQuantity(product.id_product)"
                                    :disabled="getQuantity(product.id_product) <= 1"
                                    class="w-8 h-8 bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed rounded-full flex items-center justify-center text-sm">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   :value="getQuantity(product.id_product)"
                                   @input="setQuantity(product.id_product, $event.target.value, product.stock)"
                                   :max="product.stock"
                                   min="1"
                                   class="w-16 h-8 text-center text-sm border border-gray-300 rounded">
                            <button @click="increaseQuantity(product.id_product, product.stock)"
                                    :disabled="getQuantity(product.id_product) >= product.stock"
                                    class="w-8 h-8 bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed rounded-full flex items-center justify-center text-sm">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <button @click="addToCart(product)"
                                class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                            <i class="fas fa-cart-plus mr-2"></i>Agregar
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productSearch() {
    return {
        searchTerm: '',
        destinationBranchId: '',
        destinationBranches: [],
        searchResults: [],
        isSearching: false,
        productQuantities: {},
        
        init() {
            this.loadDestinationBranches();
        },
        
        async loadDestinationBranches() {
            try {
                const response = await fetch('{{ route("transfers.destination-branches") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    this.destinationBranches = data.branches;
                    
                    // Auto-seleccionar si solo hay una opción (managers)
                    if (this.destinationBranches.length === 1) {
                        this.destinationBranchId = this.destinationBranches[0].id;
                    }
                }
            } catch (error) {
                console.error('Error loading destination branches:', error);
            }
        },
        
        onDestinationChange() {
            this.clearSearch();
        },
        
        async searchProducts() {
            if (!this.destinationBranchId || this.searchTerm.length < 2) {
                this.searchResults = [];
                return;
            }
            
            this.isSearching = true;
            
            try {
                const params = new URLSearchParams({
                    search: this.searchTerm,
                    destination_branch_id: this.destinationBranchId
                });
                
                const response = await fetch(`{{ route("transfers.search-products") }}?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    this.searchResults = data.products;
                    
                    // Inicializar cantidades
                    this.searchResults.forEach(product => {
                        if (!this.productQuantities[product.id_product]) {
                            this.productQuantities[product.id_product] = 1;
                        }
                    });
                } else {
                    this.searchResults = [];
                }
            } catch (error) {
                console.error('Error searching products:', error);
                this.searchResults = [];
            } finally {
                this.isSearching = false;
            }
        },
        
        clearSearch() {
            this.searchTerm = '';
            this.searchResults = [];
            this.productQuantities = {};
        },
        
        getQuantity(productId) {
            return this.productQuantities[productId] || 1;
        },
        
        setQuantity(productId, value, maxStock) {
            const quantity = Math.max(1, Math.min(parseInt(value) || 1, maxStock));
            this.productQuantities[productId] = quantity;
        },
        
        increaseQuantity(productId, maxStock) {
            const current = this.getQuantity(productId);
            if (current < maxStock) {
                this.productQuantities[productId] = current + 1;
            }
        },
        
        decreaseQuantity(productId) {
            const current = this.getQuantity(productId);
            if (current > 1) {
                this.productQuantities[productId] = current - 1;
            }
        },
        
        addToCart(product) {
            const quantity = this.getQuantity(product.id_product);
            const destinationBranch = this.destinationBranches.find(b => b.id == this.destinationBranchId);
            
            const cartItem = {
                product_id: product.id_product,
                product_name: product.name,
                origin_branch_id: product.branch_id,
                origin_branch_name: product.branch_name,
                destiny_branch_id: this.destinationBranchId,
                destiny_branch_name: destinationBranch ? destinationBranch.name : '',
                quantity: quantity,
                available_stock: product.stock,
                price: product.price
            };
            
            // Usar la función global del carrito
            if (window.addToTransferCart) {
                window.addToTransferCart(cartItem);
                
                // Resetear cantidad
                this.productQuantities[product.id_product] = 1;
                
                // Mostrar notificación
                if (window.showNotification) {
                    window.showNotification(`${product.name} agregado al carrito (${quantity} unidades)`, 'success');
                }
            } else {
                console.error('Transfer cart not available');
                if (window.showNotification) {
                    window.showNotification('Error: Carrito no disponible', 'error');
                }
            }
        }
    };
}
</script>
@endpush
