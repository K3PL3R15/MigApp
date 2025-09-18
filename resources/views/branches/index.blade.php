@extends('layouts.migapp')

@section('title', 'Sucursales - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-store mr-3"></i>Gestión de Sucursales
                </h1>
                <p class="text-white/80 mt-2">
                    Administra todas tus sucursales y ubicaciones
                </p>
            </div>
            
            <div class="flex items-center space-x-3">
                @if(in_array(auth()->user()->role, ['owner', 'manager']))
                    <a href="{{ route('branches.explore') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition-colors duration-200 inline-flex items-center">
                        <i class="fas fa-search mr-2"></i>Explorar Sucursales
                    </a>
                @endif
                
                @if(auth()->user()->isOwner())
                    <x-migapp.button 
                        variant="primary" 
                        icon="fas fa-plus"
                        onclick="openModal('branch-create-modal')">
                        Nueva Sucursal
                    </x-migapp.button>
                @endif
            </div>
        </div>
        
        <!-- Estadísticas generales -->
        @if($branches->count() > 0)
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
                <div class="flex flex-wrap justify-between items-center gap-4 stats-container">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-store text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ $branches->count() }}</div>
                            <div class="text-white/70 text-xs">Total Sucursales</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-300">{{ $branches->sum('users_count') }}</div>
                            <div class="text-white/70 text-xs">Total Empleados</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-boxes text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-300">{{ $branches->sum('inventories_count') }}</div>
                            <div class="text-white/70 text-xs">Total Inventarios</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-amber-300">{{ $branches->sum('sales_count') }}</div>
                            <div class="text-white/70 text-xs">Total Ventas</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Lista de sucursales -->
        <div class="glass-card rounded-lg p-6">
            @if($branches->isEmpty())
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay sucursales registradas</h3>
                    <p class="text-gray-500 mb-6">Crea tu primera sucursal para comenzar a gestionar tu negocio</p>
                    
                    @if(auth()->user()->isOwner())
                        <x-migapp.button 
                            variant="primary" 
                            icon="fas fa-plus"
                            onclick="openModal('branch-create-modal')">
                            Crear Primera Sucursal
                        </x-migapp.button>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($branches as $branch)
                        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border {{ $branch->is_main ? 'border-amber-200' : 'border-gray-200' }}">
                            <!-- Header de la sucursal -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 {{ $branch->is_main ? 'bg-amber-100' : 'bg-indigo-100' }} rounded-lg flex items-center justify-center">
                                        <i class="fas {{ $branch->is_main ? 'fa-crown text-amber-600' : 'fa-store text-indigo-600' }}"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 flex items-center">
                                            {{ $branch->name }}
                                            @if($branch->is_main)
                                                <span class="ml-2 text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full">Principal</span>
                                            @endif
                                        </h3>
                                        <p class="text-sm text-gray-600">{{ Str::limit($branch->direction, 25) }}</p>
                                        @if($branch->phone)
                                            <p class="text-xs text-gray-500"><i class="fas fa-phone mr-1"></i>{{ $branch->phone }}</p>
                                        @endif
                                        <div class="mt-2 flex items-center">
                                            <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2 py-1 rounded font-mono">
                                                <i class="fas fa-key mr-1"></i>{{ $branch->unique_code }}
                                            </span>
                                            <button onclick="copyToClipboard('{{ $branch->unique_code }}')" 
                                                    class="ml-2 text-gray-400 hover:text-gray-600 text-xs" 
                                                    title="Copiar código">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Estadísticas de la sucursal -->
                            <div class="grid grid-cols-2 gap-4 py-4">
                                <div class="text-center">
                                    <div class="text-xl font-bold text-gray-800">{{ $branch->users->count() }}</div>
                                    <div class="text-xs text-gray-500">Empleados</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xl font-bold text-gray-800">{{ $branch->inventories->count() }}</div>
                                    <div class="text-xs text-gray-500">Inventarios</div>
                                </div>
                            </div>
                            
                            <!-- Información adicional -->
                            <div class="border-t pt-3 mb-4">
                                <div class="text-xs text-gray-500 space-y-1">
                                    <div><strong>Ventas totales:</strong> {{ $branch->sales_count }}</div>
                                    <div><strong>Creada:</strong> {{ $branch->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            
                            <!-- Estado y acciones -->
                            <div class="flex justify-between items-center pt-4 border-t">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-green-500 mr-1" style="font-size: 6px;"></i>
                                    Operativa
                                </span>
                                
                                <div class="flex space-x-2">
                                    @if(auth()->user()->canManageBranch($branch->id_branch))
                                        <a href="{{ route('branches.show', $branch) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm inline-flex items-center px-2 py-1 bg-blue-50 rounded">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                    @endif
                                    
                                    @if(auth()->user()->isOwner() && $branch->id_user === auth()->id())
                                        <button onclick="editBranch({{ $branch->id_branch }})" 
                                                class="text-amber-600 hover:text-amber-800 text-sm inline-flex items-center px-2 py-1 bg-amber-50 rounded">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </button>
                                    @endif
                                    
                                    @if(auth()->user()->isOwner() && $branch->id_user === auth()->id() && !$branch->is_main)
                                        <button onclick="deleteBranch({{ $branch->id_branch }}, '{{ $branch->name }}')" 
                                                class="text-red-600 hover:text-red-800 text-sm inline-flex items-center px-2 py-1 bg-red-50 rounded">
                                            <i class="fas fa-trash mr-1"></i>Eliminar
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Botón para agregar nueva sucursal -->
                    @if(auth()->user()->isOwner())
                        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-2 border-dashed border-gray-300 hover:border-gray-400 cursor-pointer min-h-[400px] flex items-center justify-center" onclick="openModal('branch-create-modal')">
                            <div class="text-center">
                                <!-- Header similar a las otras tarjetas -->
                                <div class="flex justify-center mb-4">
                                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-plus text-gray-400 text-xl"></i>
                                    </div>
                                </div>
                                
                                <div class="text-gray-400 text-2xl mb-3">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-600 mb-2">Agregar Sucursal</h3>
                                <p class="text-sm text-gray-500 mb-6">Expande tu negocio creando una nueva ubicación</p>
                                
                                <!-- Espacio para coincidir con las estadísticas -->
                                <div class="grid grid-cols-2 gap-4 py-4 mb-4 opacity-50">
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-gray-400">+</div>
                                        <div class="text-xs text-gray-400">Empleados</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-gray-400">+</div>
                                        <div class="text-xs text-gray-400">Inventarios</div>
                                    </div>
                                </div>
                                
                                <x-migapp.button 
                                    variant="primary" 
                                    size="sm"
                                    class="w-full">
                                    <i class="fas fa-plus mr-2"></i>Crear Sucursal
                                </x-migapp.button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    <!-- Modal para crear sucursal -->
    @if(auth()->user()->isOwner())
        <x-migapp.modal id="branch-create-modal" max-width="md">
            <x-slot name="header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-store mr-2 text-indigo-600"></i>
                    Nueva Sucursal
                </h3>
            </x-slot>
            
            <form id="branch-create-form">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" id="name" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="Ej: Sucursal Centro">
                    </div>
                    
                    <div>
                        <label for="direction" class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                        <textarea id="direction" name="direction" required rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                  placeholder="Ej: Calle 123 #45-67, Barrio Centro"></textarea>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" id="phone" name="phone"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="Ej: +57 300 123 4567">
                    </div>
                </div>
            </form>
            
            <x-slot name="footer">
                <div class="flex justify-end space-x-3">
                    <x-migapp.button variant="secondary" onclick="closeModal('branch-create-modal')">
                        Cancelar
                    </x-migapp.button>
                    <x-migapp.button variant="primary" onclick="submitBranchForm()">
                        <i class="fas fa-save mr-2"></i>Crear Sucursal
                    </x-migapp.button>
                </div>
            </x-slot>
        </x-migapp.modal>
    @endif
    
    <!-- Modal para editar sucursal -->
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
                        <input type="text" id="edit-name" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="edit-direction" class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                        <textarea id="edit-direction" name="direction" required rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
                    </div>
                    
                    <div>
                        <label for="edit-phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" id="edit-phone" name="phone"
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
@endsection

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .branch-card:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
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
let currentBranchId = null;

// Función para enviar formulario de crear sucursal
function submitBranchForm() {
    const form = document.getElementById('branch-create-form');
    const formData = new FormData(form);
    
    // Obtener datos del formulario
    const data = {
        name: formData.get('name'),
        direction: formData.get('direction'),
        phone: formData.get('phone')
    };
    
    // Validaciones básicas
    if (!data.name || !data.direction) {
        showNotification('Por favor complete todos los campos obligatorios', 'error');
        return;
    }
    
    // Deshabilitar botón y mostrar loading
    const submitButton = document.querySelector('button[onclick="submitBranchForm()"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';
    
    // Enviar petición AJAX
    fetch('{{ route("branches.store") }}', {
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
            closeModal('branch-create-modal');
            
            // Limpiar formulario
            form.reset();
            
            // Recargar página para mostrar nueva sucursal
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión al crear la sucursal', 'error');
    })
    .finally(() => {
        // Restaurar botón
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

// Función para abrir modal de edición
function editBranch(branchId) {
    currentBranchId = branchId;
    
    // Obtener datos de la sucursal
    fetch(`{{ url('/branches') }}/${branchId}/edit`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Encontrar la sucursal en la página actual
            const branchCard = document.querySelector(`button[onclick="editBranch(${branchId})"]`).closest('.bg-white');
            const branchName = branchCard.querySelector('h3').textContent.trim().replace('Principal', '').trim();
            const branchDirection = branchCard.querySelector('p.text-sm.text-gray-600').textContent;
            const phoneElement = branchCard.querySelector('.fa-phone');
            const branchPhone = phoneElement ? phoneElement.parentElement.textContent.replace('📞', '').trim() : '';
            
            // Llenar formulario de edición
            document.getElementById('edit-name').value = branchName;
            document.getElementById('edit-direction').value = branchDirection;
            document.getElementById('edit-phone').value = branchPhone;
            
            // Abrir modal
            openModal('branch-edit-modal');
        } else {
            showNotification('Error al cargar los datos de la sucursal', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Función para actualizar sucursal
function updateBranchForm() {
    if (!currentBranchId) {
        showNotification('Error: ID de sucursal no válido', 'error');
        return;
    }
    
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
    fetch(`{{ url('/branches') }}/${currentBranchId}`, {
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
        currentBranchId = null;
    });
}

// Función para eliminar sucursal
function deleteBranch(branchId, branchName) {
    if (!confirm(`¿Está seguro de eliminar la sucursal "${branchName}"?\n\nEsta acción eliminará todos los datos asociados y no se puede deshacer.`)) {
        return;
    }
    
    fetch(`{{ url('/branches') }}/${branchId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
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
        showNotification('Error de conexión al eliminar la sucursal', 'error');
    });
}

// Función para copiar código al portapapeles
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        // API moderna del portapapeles
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Código copiado al portapapeles', 'success');
        }).catch(err => {
            console.error('Error al copiar:', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        // Fallback para navegadores más antiguos
        fallbackCopyTextToClipboard(text);
    }
}

// Función fallback para copiar texto
function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showNotification('Código copiado al portapapeles', 'success');
        } else {
            showNotification('Error al copiar el código', 'error');
        }
    } catch (err) {
        console.error('Error al copiar:', err);
        showNotification('Error al copiar el código', 'error');
    }

    document.body.removeChild(textArea);
}

// Función para mostrar notificaciones (reutilizada del módulo de ventas)
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
</script>
@endpush
