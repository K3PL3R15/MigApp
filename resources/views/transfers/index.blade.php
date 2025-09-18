@extends('layouts.migapp')

@section('title', 'Transferencias - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-exchange-alt mr-3"></i>Gestión de Transferencias
                </h1>
                <p class="text-white/80 mt-2">
                    Solicita transferencias y gestiona solicitudes pendientes
                </p>
            </div>
            
            <div class="flex items-center space-x-3">
                <a href="{{ route('branches.index') }}" 
                   class="bg-white/20 hover:bg-white/30 text-white font-medium px-6 py-2 rounded-lg transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-store mr-2"></i>Sucursales
                </a>
            </div>
        </div>
        
        <!-- Tabs Navigation -->
        <div class="bg-white/10 backdrop-blur-sm rounded-t-lg border-b border-white/20">
            <nav class="flex space-x-8 px-6" x-data="{ activeTab: 'search' }">
                <button @click="activeTab = 'search'" 
                        :class="{ 'border-amber-400 text-white': activeTab === 'search', 'border-transparent text-white/60 hover:text-white/80': activeTab !== 'search' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-search mr-2"></i>Solicitar Transferencias
                </button>
                
                <button @click="activeTab = 'pending'" 
                        :class="{ 'border-amber-400 text-white': activeTab === 'pending', 'border-transparent text-white/60 hover:text-white/80': activeTab !== 'pending' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                    <i class="fas fa-clock mr-2"></i>Solicitudes Pendientes
                    <span id="pending-count" 
                          class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hidden">
                        0
                    </span>
                </button>
                
                <button @click="activeTab = 'history'" 
                        :class="{ 'border-amber-400 text-white': activeTab === 'history', 'border-transparent text-white/60 hover:text-white/80': activeTab !== 'history' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-history mr-2"></i>Historial
                </button>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div class="glass-card rounded-b-lg p-6">
            <!-- Tab 1: Solicitar Transferencias -->
            <div x-show="activeTab === 'search'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <x-migapp.product-search />
            </div>
            
            <!-- Tab 2: Solicitudes Pendientes -->
            <div x-show="activeTab === 'pending'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <x-migapp.pending-transfers />
            </div>
            
            <!-- Tab 3: Historial -->
            <div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <x-migapp.transfers-history />
            </div>
        </div>
    </div>
    
    <!-- Transfer Cart (siempre visible) -->
    <x-migapp.transfer-cart />
@endsection

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .tab-transition {
        transition: all 0.3s ease;
    }
    
    /* Estilos para el sistema de pestañas */
    .tab-content {
        min-height: 400px;
    }
</style>
@endpush

@push('scripts')
<script>
// Funcionalidad global para el módulo de transferencias
document.addEventListener('DOMContentLoaded', function() {
    // Cargar contador de solicitudes pendientes
    loadPendingCount();
    
    // Actualizar cada 30 segundos
    setInterval(loadPendingCount, 30000);
});

function loadPendingCount() {
    fetch('{{ route("transfers.index") }}?state=pending', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const count = data.transfers.length;
            const badge = document.getElementById('pending-count');
            
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    })
    .catch(error => {
        console.error('Error loading pending count:', error);
    });
}

// Función global para refrescar las listas
window.refreshTransferLists = function() {
    loadPendingCount();
    
    // Disparar eventos personalizados para actualizar componentes
    window.dispatchEvent(new CustomEvent('refresh-pending-transfers'));
    window.dispatchEvent(new CustomEvent('refresh-transfers-history'));
};

// Sistema de notificaciones global
window.showTransferNotification = function(message, type = 'info') {
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
};

// Exponer función globalmente
window.showNotification = window.showTransferNotification;
</script>
@endpush
