@props([
    'position' => 'right',
    'persistent' => true
])

<!-- Floating Cart Button -->
<div class="fixed {{ $position === 'left' ? 'left-4' : 'right-4' }} bottom-4 z-50"
     x-data="{
        cartOpen: false,
        cartItems: [],
        cartCount: 0,
        cartAnimation: false,
        isProcessing: false,
        processProgress: 0,
        processedCount: 0,
        totalToProcess: 0,
        ...transferCart()
     }" 
     x-init="loadCart()">
    
    <!-- Cart Button -->
    <button @click="toggleCart()" 
            class="bg-amber-600 hover:bg-amber-700 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
            :class="{ 'animate-bounce': cartAnimation }">
        <div class="relative">
            <i class="fas fa-shopping-cart text-lg"></i>
            <!-- Cart Counter -->
            <span x-show="cartCount > 0" 
                  x-text="cartCount"
                  class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
                  x-transition:enter="transition ease-out duration-300"
                  x-transition:enter-start="opacity-0 scale-75"
                  x-transition:enter-end="opacity-100 scale-100"></span>
        </div>
    </button>
    
    <!-- Cart Panel -->
    <div x-show="cartOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-full"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-full"
         class="absolute bottom-16 {{ $position === 'left' ? 'left-0' : 'right-0' }} w-96 bg-white rounded-lg shadow-2xl border border-gray-200 max-h-96 flex flex-col"
         @click.away="cartOpen = false">
        
        <!-- Cart Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-orange-50 rounded-t-lg">
            <div class="flex items-center space-x-2">
                <i class="fas fa-exchange-alt text-amber-600"></i>
                <h3 class="font-semibold text-gray-800">Carrito de Transferencias</h3>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full font-medium" 
                      x-text="`${cartCount} ${cartCount === 1 ? 'producto' : 'productos'}`"></span>
                <button @click="cartOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto">
            <!-- Empty State -->
            <div x-show="cartItems.length === 0" class="p-6 text-center">
                <div class="text-gray-400 text-4xl mb-3">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h4 class="text-gray-600 font-medium mb-2">Carrito vacío</h4>
                <p class="text-sm text-gray-500">Explora las sucursales para agregar productos a transferir</p>
            </div>
            
            <!-- Cart Items List -->
            <div x-show="cartItems.length > 0" class="p-2">
                <template x-for="(item, index) in cartItems" :key="`${item.product_id}-${item.origin_branch_id}-${item.destiny_branch_id}`">
                    <div class="bg-gray-50 rounded-lg p-3 mb-2 border border-gray-200 hover:border-amber-300 transition-colors">
                        <!-- Product Info -->
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h5 class="font-medium text-gray-800 text-sm" x-text="item.product_name"></h5>
                                <div class="text-xs text-gray-600 mt-1">
                                    <div class="flex items-center">
                                        <i class="fas fa-arrow-right mx-2 text-amber-600"></i>
                                        <span x-text="item.origin_branch_name"></span>
                                        <i class="fas fa-arrow-right mx-2 text-blue-600"></i>
                                        <span x-text="item.destiny_branch_name"></span>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Stock disponible: <span x-text="item.available_stock"></span>
                                </div>
                            </div>
                            <button @click="removeFromCart(index)" 
                                    class="text-red-500 hover:text-red-700 ml-2">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                        
                        <!-- Quantity Controls -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <button @click="updateQuantity(index, item.quantity - 1)"
                                        :disabled="item.quantity <= 1"
                                        class="w-6 h-6 bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed rounded-full flex items-center justify-center text-xs">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="text-sm font-medium w-8 text-center" x-text="item.quantity"></span>
                                <button @click="updateQuantity(index, item.quantity + 1)"
                                        :disabled="item.quantity >= item.available_stock"
                                        class="w-6 h-6 bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed rounded-full flex items-center justify-center text-xs">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="text-xs text-gray-600">
                                Total: <span class="font-medium" x-text="item.quantity"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Cart Actions -->
        <div x-show="cartItems.length > 0" class="border-t border-gray-200 p-4 bg-gray-50 rounded-b-lg">
            <div class="flex justify-between items-center mb-3">
                <span class="text-sm text-gray-600">
                    Total de transferencias: <span class="font-semibold" x-text="cartCount"></span>
                </span>
                <button @click="clearCart()" 
                        class="text-xs text-red-600 hover:text-red-800 font-medium">
                    <i class="fas fa-trash-alt mr-1"></i>Limpiar carrito
                </button>
            </div>
            
            <div class="flex space-x-2">
                <x-migapp.button variant="outline-primary" 
                                size="sm" 
                                class="flex-1 text-xs"
                                @click="cartOpen = false">
                    <i class="fas fa-eye mr-1"></i>Seguir explorando
                </x-migapp.button>
                <button class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white text-xs px-3 py-2 rounded-md transition-colors flex-1 flex items-center justify-center" 
                        id="process-transfers-btn"
                        @click="processTransfers()">
                    <i class="fas fa-paper-plane mr-1" id="process-transfers-icon"></i>
                    <span id="process-transfers-text">Solicitar Transferencias</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Processing Modal -->
<x-migapp.modal id="transfer-processing-modal" max-width="lg" :closeable="false">
    <div class="text-center py-6">
        <div class="text-6xl text-amber-600 mb-4">
            <i class="fas fa-cog fa-spin"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Procesando transferencias...</h3>
        <p class="text-gray-600 mb-4">Por favor espera mientras enviamos tus solicitudes</p>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-amber-600 h-2 rounded-full transition-all duration-300" 
                 style="width: 0%" id="transfer-progress-bar"></div>
        </div>
        <p class="text-sm text-gray-500 mt-2" id="transfer-progress-text">0 de 0 transferencias procesadas</p>
    </div>
</x-migapp.modal>

@push('scripts')
<script>
function transferCart() {
    return {
        cartOpen: false,
        cartItems: [],
        cartCount: 0,
        cartAnimation: false,
        isProcessing: false,
        processProgress: 0,
        processedCount: 0,
        totalToProcess: 0,
        
        init() {
            this.loadCart();
        },
        
        toggleCart() {
            this.cartOpen = !this.cartOpen;
        },
        
        addToCart(product) {
            // Verificar si el producto ya existe en el carrito
            const existingIndex = this.cartItems.findIndex(item => 
                item.product_id === product.product_id &&
                item.origin_branch_id === product.origin_branch_id &&
                item.destiny_branch_id === product.destiny_branch_id
            );
            
            if (existingIndex >= 0) {
                // Si existe, incrementar cantidad
                if (this.cartItems[existingIndex].quantity < product.available_stock) {
                    this.cartItems[existingIndex].quantity += product.quantity || 1;
                }
            } else {
                // Si no existe, agregar nuevo
                this.cartItems.push({
                    product_id: product.product_id,
                    product_name: product.product_name,
                    origin_branch_id: product.origin_branch_id,
                    origin_branch_name: product.origin_branch_name,
                    destiny_branch_id: product.destiny_branch_id,
                    destiny_branch_name: product.destiny_branch_name,
                    quantity: product.quantity || 1,
                    available_stock: product.available_stock,
                    price: product.price || 0
                });
            }
            
            this.updateCartCount();
            this.saveCart();
            this.animateCart();
            
            // Mostrar notificación
            window.showNotification && window.showNotification(
                `${product.product_name} agregado al carrito`, 
                'success'
            );
        },
        
        removeFromCart(index) {
            this.cartItems.splice(index, 1);
            this.updateCartCount();
            this.saveCart();
        },
        
        updateQuantity(index, newQuantity) {
            if (newQuantity >= 1 && newQuantity <= this.cartItems[index].available_stock) {
                this.cartItems[index].quantity = newQuantity;
                this.updateCartCount();
                this.saveCart();
            }
        },
        
        clearCart() {
            if (confirm('¿Estás seguro de limpiar el carrito?')) {
                this.cartItems = [];
                this.updateCartCount();
                this.saveCart();
            }
        },
        
        updateCartCount() {
            this.cartCount = this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
        },
        
        animateCart() {
            this.cartAnimation = true;
            setTimeout(() => {
                this.cartAnimation = false;
            }, 600);
        },
        
        saveCart() {
            if ({{ $persistent ? 'true' : 'false' }}) {
                localStorage.setItem('transfer_cart', JSON.stringify(this.cartItems));
            }
        },
        
        loadCart() {
            if ({{ $persistent ? 'true' : 'false' }}) {
                const saved = localStorage.getItem('transfer_cart');
                if (saved) {
                    this.cartItems = JSON.parse(saved);
                    this.updateCartCount();
                }
            }
        },
        
        async processTransfers() {
            if (this.cartItems.length === 0) return;
            
            this.isProcessing = true;
            this.processProgress = 0;
            this.processedCount = 0;
            this.totalToProcess = this.cartItems.length;
            
            // Actualizar estado del botón
            const processBtn = document.getElementById('process-transfers-btn');
            const processIcon = document.getElementById('process-transfers-icon');
            const processText = document.getElementById('process-transfers-text');
            
            if (processBtn) processBtn.disabled = true;
            if (processIcon) {
                processIcon.className = 'fas fa-spinner fa-spin mr-1';
            }
            if (processText) processText.textContent = 'Procesando...';
            
            // Abrir modal de procesamiento e inicializar progreso
            window.openModal('transfer-processing-modal');
            
            // Inicializar elementos del progreso
            const progressBar = document.getElementById('transfer-progress-bar');
            const progressText = document.getElementById('transfer-progress-text');
            if (progressBar) progressBar.style.width = '0%';
            if (progressText) progressText.textContent = `0 de ${this.totalToProcess} transferencias procesadas`;
            
            const results = [];
            const errors = [];
            
            try {
                for (let i = 0; i < this.cartItems.length; i++) {
                    const item = this.cartItems[i];
                    
                    try {
                        const response = await fetch('{{ route("transfers.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                id_product: item.product_id,
                                id_origin_branch: item.origin_branch_id,
                                id_destiny_branch: item.destiny_branch_id,
                                quantity_products: item.quantity
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            results.push(data);
                        } else {
                            errors.push(`${item.product_name}: ${data.message}`);
                        }
                    } catch (error) {
                        errors.push(`${item.product_name}: Error de conexión`);
                    }
                    
                    this.processedCount++;
                    this.processProgress = Math.round((this.processedCount / this.totalToProcess) * 100);
                    
                    // Actualizar elementos del DOM directamente
                    const progressBar = document.getElementById('transfer-progress-bar');
                    const progressText = document.getElementById('transfer-progress-text');
                    if (progressBar) progressBar.style.width = `${this.processProgress}%`;
                    if (progressText) progressText.textContent = `${this.processedCount} de ${this.totalToProcess} transferencias procesadas`;
                    
                    // Pequeña pausa para mostrar progreso
                    await new Promise(resolve => setTimeout(resolve, 200));
                }
                
                // Mostrar resultados
                setTimeout(() => {
                    window.closeModal('transfer-processing-modal');
                    
                    if (results.length > 0) {
                        window.showNotification && window.showNotification(
                            `${results.length} transferencias solicitadas exitosamente`, 
                            'success'
                        );
                    }
                    
                    if (errors.length > 0) {
                        window.showNotification && window.showNotification(
                            `${errors.length} transferencias fallaron: ${errors.join(', ')}`, 
                            'error'
                        );
                    }
                    
                    // Limpiar carrito si todas fueron exitosas
                    if (errors.length === 0) {
                        this.clearCart();
                        this.cartOpen = false;
                    }
                }, 1000);
                
            } catch (error) {
                console.error('Error processing transfers:', error);
                window.closeModal('transfer-processing-modal');
                window.showNotification && window.showNotification(
                    'Error al procesar las transferencias', 
                    'error'
                );
            } finally {
                this.isProcessing = false;
                
                // Restaurar estado del botón
                const processBtn = document.getElementById('process-transfers-btn');
                const processIcon = document.getElementById('process-transfers-icon');
                const processText = document.getElementById('process-transfers-text');
                
                if (processBtn) processBtn.disabled = false;
                if (processIcon) {
                    processIcon.className = 'fas fa-paper-plane mr-1';
                }
                if (processText) processText.textContent = 'Solicitar Transferencias';
            }
        }
    };
}

// Función global para agregar al carrito desde otros componentes
window.addToTransferCart = function(product) {
    // Buscar el componente del carrito
    const cartComponent = document.querySelector('[x-data*="transferCart"]');
    if (cartComponent && cartComponent._x_dataStack) {
        const cartData = cartComponent._x_dataStack[0];
        if (cartData.addToCart) {
            cartData.addToCart(product);
        }
    }
};
</script>
@endpush
