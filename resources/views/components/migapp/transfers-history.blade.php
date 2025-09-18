@props([])

<div x-data="transfersHistory()" x-init="init()">
    <!-- Header del componente -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">
                    <i class="fas fa-history text-blue-600 mr-2"></i>Historial de Transferencias
                </h2>
                <p class="text-gray-600 text-sm">
                    Consulta todas las transferencias realizadas y su estado actual
                </p>
            </div>
            
            <button @click="loadHistory()" 
                    :disabled="isLoading"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                <i :class="isLoading ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'" class="mr-2"></i>
                <span x-text="isLoading ? 'Cargando...' : 'Actualizar'"></span>
            </button>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="isLoading" class="text-center py-12">
        <div class="inline-flex items-center">
            <i class="fas fa-spinner fa-spin text-amber-600 text-2xl mr-3"></i>
            <span class="text-gray-600">Cargando historial...</span>
        </div>
    </div>
    
    <!-- Empty State -->
    <div x-show="!isLoading && transfers.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">
            <i class="fas fa-history"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay historial</h3>
        <p class="text-gray-500">Aún no se han realizado transferencias</p>
    </div>
    
    <!-- History List -->
    <div x-show="!isLoading && transfers.length > 0" class="space-y-4">
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-600">
                <span x-text="transfers.length"></span> transferencia<span x-text="transfers.length === 1 ? '' : 's'"></span> en el historial
            </p>
        </div>
        
        <div class="grid grid-cols-1 gap-4">
            <template x-for="transfer in transfers" :key="transfer.id_request">
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                    <!-- Transfer Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-800" x-text="transfer.product.name"></h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusStyle(transfer.state)">
                                    <i :class="getStatusIcon(transfer.state)" class="mr-1"></i>
                                    <span x-text="getStatusText(transfer.state)"></span>
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">
                                Solicitud #<span x-text="transfer.id_request"></span> • 
                                <span x-text="formatDate(transfer.date_request)"></span>
                            </p>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-700" x-text="transfer.quantity_products"></div>
                            <div class="text-xs text-gray-500">unidades</div>
                        </div>
                    </div>
                    
                    <!-- Transfer Flow -->
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4 mb-4">
                        <!-- Origen -->
                        <div class="text-center flex-1">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-store text-blue-600"></i>
                            </div>
                            <h4 class="font-medium text-gray-800 text-sm" x-text="transfer.origin_branch.name"></h4>
                            <p class="text-xs text-gray-600">Origen</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="flex-1 flex items-center justify-center">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-0.5 bg-gray-300"></div>
                                <i class="fas fa-arrow-right text-gray-400"></i>
                                <div class="w-8 h-0.5 bg-gray-300"></div>
                            </div>
                        </div>
                        
                        <!-- Destino -->
                        <div class="text-center flex-1">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-crosshairs text-green-600"></i>
                            </div>
                            <h4 class="font-medium text-gray-800 text-sm" x-text="transfer.destiny_branch.name"></h4>
                            <p class="text-xs text-gray-600">Destino</p>
                        </div>
                    </div>
                    
                    <!-- Timeline -->
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Estado de la transferencia</h4>
                        <div class="space-y-2">
                            <!-- Solicitado -->
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-blue-500 rounded-full flex-shrink-0"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-800">Solicitado</p>
                                    <p class="text-xs text-gray-600" x-text="formatDate(transfer.date_request)"></p>
                                </div>
                            </div>
                            
                            <!-- Estado actual -->
                            <template x-if="transfer.state !== 'pending'">
                                <div class="flex items-center space-x-3">
                                    <div class="w-4 h-4 rounded-full flex-shrink-0"
                                         :class="transfer.state === 'rejected' ? 'bg-red-500' : 'bg-green-500'"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-800" x-text="getStatusText(transfer.state)"></p>
                                        <p class="text-xs text-gray-600" x-text="formatDate(transfer.updated_at)"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function transfersHistory() {
    return {
        transfers: [],
        isLoading: false,
        
        init() {
            this.loadHistory();
            
            // Escuchar eventos de actualización
            window.addEventListener('refresh-transfers-history', () => {
                this.loadHistory();
            });
        },
        
        async loadHistory() {
            this.isLoading = true;
            
            try {
                const response = await fetch('{{ route("transfers.index") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    // Ordenar por fecha más reciente primero
                    this.transfers = data.transfers.sort((a, b) => {
                        return new Date(b.date_request) - new Date(a.date_request);
                    });
                }
            } catch (error) {
                console.error('Error loading transfers history:', error);
                if (window.showNotification) {
                    window.showNotification('Error al cargar el historial', 'error');
                }
            } finally {
                this.isLoading = false;
            }
        },
        
        // Utility functions
        getStatusStyle(state) {
            const styles = {
                pending: 'bg-yellow-100 text-yellow-800',
                approved: 'bg-green-100 text-green-800',
                rejected: 'bg-red-100 text-red-800',
                completed: 'bg-blue-100 text-blue-800'
            };
            return styles[state] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusIcon(state) {
            const icons = {
                pending: 'fas fa-clock',
                approved: 'fas fa-check',
                rejected: 'fas fa-times',
                completed: 'fas fa-check-double'
            };
            return icons[state] || 'fas fa-question';
        },
        
        getStatusText(state) {
            const texts = {
                pending: 'Pendiente',
                approved: 'Aprobada',
                rejected: 'Rechazada',
                completed: 'Completada'
            };
            return texts[state] || state;
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    };
}
</script>
@endpush
