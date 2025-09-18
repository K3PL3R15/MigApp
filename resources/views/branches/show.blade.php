@extends('layouts.migapp')

@section('title', 'Sucursal: ' . $branch->name . ' - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sucursal -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <div class="flex items-center space-x-4 mb-2">
                    <a href="{{ route('branches.index') }}" 
                       class="text-white/70 hover:text-white transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Volver a Sucursales
                    </a>
                </div>
                <h1 class="text-3xl font-bold text-white flex items-center">
                    <div class="w-12 h-12 {{ $branch->is_main ? 'bg-amber-100' : 'bg-indigo-100' }} rounded-lg flex items-center justify-center mr-4">
                        <i class="fas {{ $branch->is_main ? 'fa-crown text-amber-600' : 'fa-store text-indigo-600' }}"></i>
                    </div>
                    {{ $branch->name }}
                    @if($branch->is_main)
                        <span class="ml-3 text-sm bg-amber-500 text-white px-3 py-1 rounded-full">Principal</span>
                    @endif
                </h1>
                <p class="text-white/80 mt-2">
                    <i class="fas fa-map-marker-alt mr-2"></i>{{ $branch->direction }}
                </p>
                @if($branch->phone)
                    <p class="text-white/70 mt-1">
                        <i class="fas fa-phone mr-2"></i>{{ $branch->phone }}
                    </p>
                @endif
            </div>
            
            @if(auth()->user()->isOwner() && $branch->id_user === auth()->id())
                <x-migapp.button 
                    variant="secondary" 
                    icon="fas fa-edit"
                    onclick="editBranch({{ $branch->id_branch }})">
                    Editar Sucursal
                </x-migapp.button>
            @endif
        </div>
        
        <!-- Estadísticas de la sucursal -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 stats-container">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['total_users'] }}</div>
                        <div class="text-white/70 text-xs">Empleados</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-boxes text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-300">{{ $stats['total_inventories'] }}</div>
                        <div class="text-white/70 text-xs">Inventarios</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-amber-300">{{ $branch->sales_count ?? 0 }}</div>
                        <div class="text-white/70 text-xs">Total Ventas</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-red-300">{{ $stats['low_stock_products'] }}</div>
                        <div class="text-white/70 text-xs">Stock Bajo</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Panel de Inventarios -->
            <div class="glass-card rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-boxes mr-2 text-purple-600"></i>
                        Inventarios ({{ $branch->inventories->count() }})
                    </h3>
                    @if(in_array(auth()->user()->role, ['owner', 'manager']))
                        <a href="{{ route('inventories.create') }}" 
                           class="text-sm bg-purple-100 text-purple-700 px-3 py-1 rounded-full hover:bg-purple-200 transition-colors">
                            <i class="fas fa-plus mr-1"></i>Nuevo Inventario
                        </a>
                    @endif
                </div>
                
                @if($branch->inventories->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-boxes text-4xl mb-3"></i>
                        <p>No hay inventarios registrados en esta sucursal</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($branch->inventories as $inventory)
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-gray-800">{{ $inventory->name }}</h4>
                                        <p class="text-sm text-gray-600 mb-2">{{ $inventory->description ?? 'Sin descripción' }}</p>
                                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                                            <span><i class="fas fa-cubes mr-1"></i>{{ $inventory->products->count() }} productos</span>
                                            <span><i class="fas fa-calendar mr-1"></i>{{ $inventory->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        @if(auth()->user()->canManageBranch($inventory->id_branch))
                                            <a href="{{ route('inventories.show', $inventory) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-sm bg-blue-50 px-2 py-1 rounded">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->isOwner() && $inventory->branch->id_user === auth()->id() || auth()->user()->role === 'manager' && $inventory->id_branch === auth()->user()->id_branch)
                                            <a href="{{ route('inventories.edit', $inventory) }}" 
                                               class="text-amber-600 hover:text-amber-800 text-sm bg-amber-50 px-2 py-1 rounded">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- Panel de Empleados -->
            <div class="glass-card rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-users mr-2 text-green-600"></i>
                        Empleados ({{ $branch->users->count() }})
                    </h3>
                    @if(auth()->user()->isOwner())
                        <a href="#" 
                           class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-full hover:bg-green-200 transition-colors">
                            <i class="fas fa-plus mr-1"></i>Nuevo Empleado
                        </a>
                    @endif
                </div>
                
                @if($branch->users->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-users text-4xl mb-3"></i>
                        <p>No hay empleados asignados a esta sucursal</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($branch->users as $user)
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">{{ $user->name }}</h4>
                                            <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $user->role === 'owner' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $user->role === 'manager' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $user->role === 'employee' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $user->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Información adicional -->
        <div class="glass-card rounded-lg p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                Información de la Sucursal
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Detalles Básicos</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div><strong>Nombre:</strong> {{ $branch->name }}</div>
                        <div><strong>Dirección:</strong> {{ $branch->direction }}</div>
                        @if($branch->phone)
                            <div><strong>Teléfono:</strong> {{ $branch->phone }}</div>
                        @endif
                        <div><strong>Tipo:</strong> {{ $branch->is_main ? 'Sucursal Principal' : 'Sucursal Secundaria' }}</div>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Fechas</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div><strong>Creada:</strong> {{ $branch->created_at->format('d/m/Y H:i') }}</div>
                        <div><strong>Actualizada:</strong> {{ $branch->updated_at->format('d/m/Y H:i') }}</div>
                        <div><strong>Tiempo activa:</strong> {{ $branch->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Resumen</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div><strong>Total productos:</strong> {{ $stats['total_products'] }}</div>
                        <div><strong>Productos con stock bajo:</strong> {{ $stats['low_stock_products'] }}</div>
                        <div><strong>Estado:</strong> 
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Operativa
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para editar sucursal (reutilizado) -->
    @if(auth()->user()->isOwner() && $branch->id_user === auth()->id())
        <x-migapp.modal id="branch-edit-modal" max-width="md">
            <x-slot name="header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-edit mr-2 text-amber-600"></i>
                    Editar Sucursal
                </h3>
            </x-slot>
            
            <form id="branch-edit-form">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" id="edit-name" name="name" required value="{{ $branch->name }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="edit-direction" class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                        <textarea id="edit-direction" name="direction" required rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">{{ $branch->direction }}</textarea>
                    </div>
                    
                    <div>
                        <label for="edit-phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" id="edit-phone" name="phone" value="{{ $branch->phone }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                </div>
            </form>
            
            <x-slot name="footer">
                <div class="flex justify-end space-x-3">
                    <x-migapp.button variant="secondary" onclick="closeModal('branch-edit-modal')">
                        Cancelar
                    </x-migapp.button>
                    <x-migapp.button variant="primary" onclick="updateBranchForm()">
                        <i class="fas fa-save mr-2"></i>Actualizar Sucursal
                    </x-migapp.button>
                </div>
            </x-slot>
        </x-migapp.modal>
    @endif
@endsection

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
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
// Función para abrir modal de edición
function editBranch(branchId) {
    openModal('branch-edit-modal');
}

// Función para actualizar sucursal
function updateBranchForm() {
    const form = document.getElementById('branch-edit-form');
    const formData = new FormData(form);
    
    // Obtener datos del formulario
    const data = {
        name: formData.get('name'),
        direction: formData.get('direction'),
        phone: formData.get('phone'),
        _method: 'PUT'
    };
    
    // Validaciones básicas
    if (!data.name || !data.direction) {
        showNotification('Por favor complete todos los campos obligatorios', 'error');
        return;
    }
    
    // Deshabilitar botón y mostrar loading
    const submitButton = document.querySelector('button[onclick="updateBranchForm()"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
    
    // Enviar petición AJAX
    fetch(`{{ route('branches.update', $branch) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('branch-edit-modal');
            
            // Recargar página para mostrar cambios
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión al actualizar la sucursal', 'error');
    })
    .finally(() => {
        // Restaurar botón
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

// Función para mostrar notificaciones (reutilizada)
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
</script>
@endpush
