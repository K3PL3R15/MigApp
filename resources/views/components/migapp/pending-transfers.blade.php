@props([])

<div x-data="pendingTransfers()" x-init="init()">
    <!-- Header del componente -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">
                    <i class="fas fa-clock text-red-600 mr-2"></i>Solicitudes Pendientes
                </h2>
                <p class="text-gray-600 text-sm">
                    Revisa y gestiona las solicitudes de transferencia que requieren tu aprobación
                </p>
            </div>
            
            <button @click="loadPendingTransfers()" 
                    :disabled="isLoading"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                <i :class="isLoading ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'" class="mr-2"></i>
                <span x-text="isLoading ? 'Cargando...' : 'Actualizar'"></span>
            </button>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-filter mr-1"></i>Filtrar por Estado
            </label>
            <select x-model="filterState" 
                    @change="applyFilters()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option value="">Todos los estados</option>
                <option value="pending">Pendientes</option>
                <option value="approved">Aprobadas</option>
                <option value="rejected">Rechazadas</option>
                <option value="completed">Completadas</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-store mr-1"></i>Sucursal de Origen
            </label>
            <select x-model="filterOriginBranch" 
                    @change="applyFilters()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option value="">Todas las sucursales</option>
                <template x-for="branch in availableBranches" :key="'origin-' + branch.id">
                    <option :value="branch.id" x-text="branch.name"></option>
                </template>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-crosshairs mr-1"></i>Sucursal de Destino
            </label>
            <select x-model="filterDestinyBranch" 
                    @change="applyFilters()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option value="">Todas las sucursales</option>
                <template x-for="branch in availableBranches" :key="'destiny-' + branch.id">
                    <option :value="branch.id" x-text="branch.name"></option>
                </template>
            </select>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="isLoading" class="text-center py-12">
        <div class="inline-flex items-center">
            <i class="fas fa-spinner fa-spin text-amber-600 text-2xl mr-3"></i>
            <span class="text-gray-600">Cargando solicitudes...</span>
        </div>
    </div>
    
    <!-- Empty State -->
    <div x-show="!isLoading && filteredTransfers.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">
            <i class="fas fa-inbox"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay solicitudes</h3>
        <p class="text-gray-500">No se encontraron solicitudes de transferencia con los filtros aplicados</p>
    </div>
    
    <!-- Transfers List -->
    <div x-show="!isLoading && filteredTransfers.length > 0" class="space-y-4">
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-600">
                <span x-text="filteredTransfers.length"></span> solicitud<span x-text="filteredTransfers.length === 1 ? '' : 'es'"></span> 
                <span x-show="filterState || filterOriginBranch || filterDestinyBranch">filtrada<span x-text="filteredTransfers.length === 1 ? '' : 's'"></span></span>
            </p>
            
            <div class="flex space-x-2">
                <button x-show="hasFilters()" 
                        @click="clearFilters()" 
                        class="text-sm text-amber-600 hover:text-amber-700 font-medium">
                    <i class="fas fa-times mr-1"></i>Limpiar filtros
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 gap-4">
            <template x-for="transfer in filteredTransfers" :key="transfer.id_request">
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
                            <div class="text-2xl font-bold text-amber-600" x-text="transfer.quantity_products"></div>
                            <div class="text-xs text-gray-500">unidades</div>
                        </div>
                    </div>
                    
                    <!-- Transfer Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-arrow-up text-blue-600 mr-1"></i>Origen
                            </h4>
                            <div class="space-y-1">
                                <p class="font-medium text-gray-800" x-text="transfer.origin_branch.name"></p>
                                <p class="text-sm text-gray-600" x-text="transfer.origin_branch.direction"></p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-arrow-down text-green-600 mr-1"></i>Destino
                            </h4>
                            <div class="space-y-1">
                                <p class="font-medium text-gray-800" x-text="transfer.destiny_branch.name"></p>
                                <p class="text-sm text-gray-600" x-text="transfer.destiny_branch.direction"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Stock actual:</span>
                                <span class="block text-gray-800" x-text="transfer.product.stock"></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Precio:</span>
                                <span class="block text-gray-800" x-text="'$' + parseFloat(transfer.product.price).toFixed(2)"></span>
                            </div>
                            <div x-show="transfer.product.lote">
                                <span class="font-medium text-gray-700">Lote:</span>
                                <span class="block text-gray-800" x-text="formatDate(transfer.product.lote)"></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Estado stock:</span>
                                <span class="block" :class="transfer.product.stock <= transfer.product.min_stock ? 'text-amber-600 font-medium' : 'text-green-600'">
                                    <span x-text="transfer.product.stock <= transfer.product.min_stock ? 'Bajo' : 'Normal'"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <template x-if="transfer.state === 'pending' && canApprove(transfer)">
                            <div class="flex space-x-3">
                                <button @click="rejectTransfer(transfer.id_request)"
                                        :disabled="isProcessing"
                                        class="bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                                    <i class="fas fa-times mr-2"></i>Rechazar
                                </button>
                                
                                <button @click="approveTransfer(transfer.id_request)"
                                        :disabled="isProcessing"
                                        class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                                    <i class="fas fa-check mr-2"></i>Aprobar
                                </button>
                            </div>
                        </template>
                        
                        <template x-if="transfer.state === 'approved' && canComplete(transfer)">
                            <button @click="completeTransfer(transfer.id_request)"
                                    :disabled="isProcessing"
                                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                                <i class="fas fa-shipping-fast mr-2"></i>Completar
                            </button>
                        </template>
                        
                        <template x-if="transfer.state === 'pending' && canDelete(transfer)">
                            <button @click="deleteTransfer(transfer.id_request)"
                                    :disabled="isProcessing"
                                    class="bg-gray-600 hover:bg-gray-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                                <i class="fas fa-trash mr-2"></i>Eliminar
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pendingTransfers() {
    return {
        transfers: [],
        filteredTransfers: [],
        availableBranches: [],
        isLoading: false,
        isProcessing: false,
        filterState: 'pending',
        filterOriginBranch: '',
        filterDestinyBranch: '',
        userRole: '{{ auth()->user()->role }}',
        userBranchId: {{ auth()->user()->id_branch ?? 'null' }},
        
        init() {
            this.loadPendingTransfers();
            
            // Escuchar eventos de actualización
            window.addEventListener('refresh-pending-transfers', () => {
                this.loadPendingTransfers();
            });
        },
        
        async loadPendingTransfers() {
            this.isLoading = true;
            
            try {
                const response = await fetch('{{ route("transfers.index") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    this.transfers = data.transfers;
                    this.extractAvailableBranches();
                    this.applyFilters();
                }
            } catch (error) {
                console.error('Error loading pending transfers:', error);
                if (window.showNotification) {
                    window.showNotification('Error al cargar las solicitudes', 'error');
                }
            } finally {
                this.isLoading = false;
            }
        },
        
        extractAvailableBranches() {
            const branchesSet = new Set();
            
            this.transfers.forEach(transfer => {
                branchesSet.add(JSON.stringify({
                    id: transfer.origin_branch.id_branch,
                    name: transfer.origin_branch.name
                }));
                branchesSet.add(JSON.stringify({
                    id: transfer.destiny_branch.id_branch,
                    name: transfer.destiny_branch.name
                }));
            });
            
            this.availableBranches = Array.from(branchesSet)
                .map(str => JSON.parse(str))
                .sort((a, b) => a.name.localeCompare(b.name));
        },
        
        applyFilters() {
            this.filteredTransfers = this.transfers.filter(transfer => {
                // Filtro por estado
                if (this.filterState && transfer.state !== this.filterState) {
                    return false;
                }
                
                // Filtro por sucursal de origen
                if (this.filterOriginBranch && transfer.origin_branch.id_branch != this.filterOriginBranch) {
                    return false;
                }
                
                // Filtro por sucursal de destino
                if (this.filterDestinyBranch && transfer.destiny_branch.id_branch != this.filterDestinyBranch) {
                    return false;
                }
                
                return true;
            });
        },
        
        hasFilters() {
            return this.filterState || this.filterOriginBranch || this.filterDestinyBranch;
        },
        
        clearFilters() {
            this.filterState = '';
            this.filterOriginBranch = '';
            this.filterDestinyBranch = '';
            this.applyFilters();
        },
        
        canApprove(transfer) {
            // Solo puede aprobar el destinatario de la transferencia
            if (this.userRole === 'owner') {
                return transfer.destiny_branch.id_user === {{ auth()->user()->id }};
            } else if (this.userRole === 'manager') {
                return transfer.destiny_branch.id_branch === this.userBranchId;
            }
            return false;
        },
        
        canComplete(transfer) {
            // Solo puede completar el que aprobó (sucursal de origen)
            if (this.userRole === 'owner') {
                return transfer.origin_branch.id_user === {{ auth()->user()->id }};
            } else if (this.userRole === 'manager') {
                return transfer.origin_branch.id_branch === this.userBranchId;
            }
            return false;
        },
        
        canDelete(transfer) {
            // Solo puede eliminar el solicitante
            if (this.userRole === 'owner') {
                return transfer.destiny_branch.id_user === {{ auth()->user()->id }};
            } else if (this.userRole === 'manager') {
                return transfer.destiny_branch.id_branch === this.userBranchId;
            }
            return false;
        },
        
        async approveTransfer(transferId) {
            if (!confirm('¿Estás seguro de aprobar esta transferencia?')) return;
            
            this.isProcessing = true;
            
            try {
                const response = await fetch(`{{ url('/transfers') }}/${transferId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    if (window.showNotification) {
                        window.showNotification(data.message, 'success');
                    }
                    this.loadPendingTransfers();
                    if (window.refreshTransferLists) {
                        window.refreshTransferLists();
                    }
                } else {
                    if (window.showNotification) {
                        window.showNotification(data.message || 'Error al aprobar la transferencia', 'error');
                    }
                }
            } catch (error) {
                console.error('Error approving transfer:', error);
                if (window.showNotification) {
                    window.showNotification('Error de conexión al aprobar la transferencia', 'error');
                }
            } finally {
                this.isProcessing = false;
            }
        },
        
        async rejectTransfer(transferId) {
            if (!confirm('¿Estás seguro de rechazar esta transferencia?')) return;
            
            this.isProcessing = true;
            
            try {
                const response = await fetch(`{{ url('/transfers') }}/${transferId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    if (window.showNotification) {
                        window.showNotification(data.message, 'success');
                    }
                    this.loadPendingTransfers();
                    if (window.refreshTransferLists) {
                        window.refreshTransferLists();
                    }
                } else {
                    if (window.showNotification) {
                        window.showNotification(data.message || 'Error al rechazar la transferencia', 'error');
                    }
                }
            } catch (error) {
                console.error('Error rejecting transfer:', error);
                if (window.showNotification) {
                    window.showNotification('Error de conexión al rechazar la transferencia', 'error');
                }
            } finally {
                this.isProcessing = false;
            }
        },
        
        async completeTransfer(transferId) {
            if (!confirm('¿Estás seguro de completar esta transferencia? Se actualizará el stock de ambas sucursales.')) return;
            
            this.isProcessing = true;
            
            try {
                const response = await fetch(`{{ url('/transfers') }}/${transferId}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    if (window.showNotification) {
                        window.showNotification(data.message, 'success');
                    }
                    this.loadPendingTransfers();
                    if (window.refreshTransferLists) {
                        window.refreshTransferLists();
                    }
                } else {
                    if (window.showNotification) {
                        window.showNotification(data.message || 'Error al completar la transferencia', 'error');
                    }
                }
            } catch (error) {
                console.error('Error completing transfer:', error);
                if (window.showNotification) {
                    window.showNotification('Error de conexión al completar la transferencia', 'error');
                }
            } finally {
                this.isProcessing = false;
            }
        },
        
        async deleteTransfer(transferId) {
            if (!confirm('¿Estás seguro de eliminar esta solicitud? Esta acción no se puede deshacer.')) return;
            
            this.isProcessing = true;
            
            try {
                const response = await fetch(`{{ url('/transfers') }}/${transferId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    if (window.showNotification) {
                        window.showNotification(data.message, 'success');
                    }
                    this.loadPendingTransfers();
                    if (window.refreshTransferLists) {
                        window.refreshTransferLists();
                    }
                } else {
                    if (window.showNotification) {
                        window.showNotification(data.message || 'Error al eliminar la transferencia', 'error');
                    }
                }
            } catch (error) {
                console.error('Error deleting transfer:', error);
                if (window.showNotification) {
                    window.showNotification('Error de conexión al eliminar la transferencia', 'error');
                }
            } finally {
                this.isProcessing = false;
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
