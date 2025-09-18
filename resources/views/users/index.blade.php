@extends('layouts.migapp')

@section('title', 'Gestión de Personal - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-users mr-3"></i>Gestión de Personal
                </h1>
                <p class="text-white/80 mt-2">
                    Administra empleados y gerentes de {{ auth()->user()->isOwner() ? 'tus sucursales' : 'tu sucursal' }}
                </p>
            </div>
            
            @if(auth()->user()->isOwner())
                <x-migapp.button 
                    variant="primary" 
                    icon="fas fa-plus"
                    onclick="openModal('create-user-modal')">
                    Nuevo Usuario
                </x-migapp.button>
            @endif
        </div>
        
        <!-- Estadísticas -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 stats-container">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $users->count() }}</div>
                        <div class="text-white/70 text-xs">Total Personal</div>
                    </div>
                </div>
                
                @if(auth()->user()->isOwner())
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-tie text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ $users->where('role', 'manager')->count() }}</div>
                            <div class="text-white/70 text-xs">Gerentes</div>
                        </div>
                    </div>
                @endif
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $users->where('role', 'employee')->count() }}</div>
                        <div class="text-white/70 text-xs">Empleados</div>
                    </div>
                </div>
                
                @if(auth()->user()->isOwner())
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-store text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ $branches->count() }}</div>
                            <div class="text-white/70 text-xs">Sucursales</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Lista de usuarios -->
        <div class="glass-card rounded-lg overflow-hidden">
            @if($users->isEmpty())
                <div class="text-center py-12 px-6">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay personal registrado</h3>
                    <p class="text-gray-500 mb-6">
                        @if(auth()->user()->isOwner())
                            Comienza agregando gerentes y empleados a tus sucursales
                        @else
                            No hay empleados asignados a tu sucursal
                        @endif
                    </p>
                    
                    @if(auth()->user()->isOwner())
                        <x-migapp.button 
                            variant="primary" 
                            icon="fas fa-plus"
                            onclick="openModal('create-user-modal')">
                            Agregar Personal
                        </x-migapp.button>
                    @endif
                </div>
            @else
                <!-- Encabezados de tabla -->
                <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                    <div class="grid grid-cols-12 gap-4 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="col-span-3">Usuario</div>
                        <div class="col-span-2">Rol</div>
                        <div class="col-span-3">Sucursal</div>
                        <div class="col-span-2">Fecha Registro</div>
                        <div class="col-span-2 text-center">Acciones</div>
                    </div>
                </div>
                
                <!-- Filas de usuarios -->
                <div class="divide-y divide-gray-200">
                    @foreach($users as $user)
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="grid grid-cols-12 gap-4 items-center">
                                <!-- Información del usuario -->
                                <div class="col-span-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm
                                            {{ $user->role === 'owner' ? 'bg-gradient-to-br from-purple-500 to-purple-600' : '' }}
                                            {{ $user->role === 'manager' ? 'bg-gradient-to-br from-blue-500 to-blue-600' : '' }}
                                            {{ $user->role === 'employee' ? 'bg-gradient-to-br from-green-500 to-green-600' : '' }}">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800 text-sm leading-5">{{ $user->name }}</h3>
                                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Rol -->
                                <div class="col-span-2">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $user->role === 'owner' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $user->role === 'manager' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $user->role === 'employee' ? 'bg-green-100 text-green-800' : '' }}">
                                        {{ $user->role_name }}
                                    </span>
                                </div>
                                
                                <!-- Sucursal -->
                                <div class="col-span-3">
                                    @if($user->branch)
                                        <div class="flex items-center">
                                            <i class="fas fa-store text-gray-400 mr-2"></i>
                                            <span class="text-sm text-gray-800">{{ $user->branch->name }}</span>
                                            @if($user->branch->is_main)
                                                <span class="ml-2 text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full">Principal</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 italic">Sin sucursal</span>
                                    @endif
                                </div>
                                
                                <!-- Fecha de registro -->
                                <div class="col-span-2">
                                    <div class="text-sm text-gray-800">{{ $user->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</div>
                                </div>
                                
                                <!-- Botones de acción -->
                                <div class="col-span-2">
                                    <div class="flex justify-center space-x-2">
                                        @if($user->id !== auth()->id())
                                            @if(auth()->user()->isOwner() || (auth()->user()->isManager() && $user->role === 'employee' && $user->id_branch === auth()->user()->id_branch))
                                                <button onclick="editUser({{ $user->id }})" 
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-amber-600 bg-amber-100 rounded-md hover:bg-amber-200 transition-colors" 
                                                        title="Editar usuario">
                                                    <i class="fas fa-edit mr-1"></i>Editar
                                                </button>
                                            @endif
                                            
                                            @if(auth()->user()->isOwner() || (auth()->user()->isManager() && $user->role === 'employee' && $user->id_branch === auth()->user()->id_branch))
                                                <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" 
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-100 rounded-md hover:bg-red-200 transition-colors" 
                                                        title="Eliminar usuario">
                                                    <i class="fas fa-trash mr-1"></i>Eliminar
                                                </button>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-md">
                                                <i class="fas fa-user-circle mr-1"></i>Tú
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    @if(auth()->user()->isOwner())
        <!-- Modal para crear usuario -->
        <x-migapp.modal id="create-user-modal" max-width="md">
            <x-slot name="header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-user-plus mr-2 text-blue-600"></i>
                    Nuevo Usuario
                </h3>
            </x-slot>
            
            <form id="create-user-form" onsubmit="submitCreateUser(event)">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="create-name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre Completo *
                            </label>
                            <input type="text" id="create-name" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Nombre del usuario">
                        </div>
                        
                        <div>
                            <label for="create-email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email *
                            </label>
                            <input type="email" id="create-email" name="email" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="correo@ejemplo.com">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="create-role" class="block text-sm font-medium text-gray-700 mb-1">
                                Rol *
                            </label>
                            <select id="create-role" name="role" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Seleccionar rol...</option>
                                <option value="manager">Gerente</option>
                                <option value="employee">Empleado</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="create-branch" class="block text-sm font-medium text-gray-700 mb-1">
                                Sucursal *
                            </label>
                            <select id="create-branch" name="id_branch" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Seleccionar sucursal...</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id_branch }}">
                                        {{ $branch->name }}{{ $branch->is_main ? ' (Principal)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="create-password" class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña Temporal *
                        </label>
                        <input type="password" id="create-password" name="password" required minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Mínimo 8 caracteres">
                        <p class="text-xs text-gray-500 mt-1">El usuario podrá cambiarla en su primer inicio de sesión</p>
                    </div>
                </div>
            </form>
            
            <x-slot name="footer">
                <div class="flex justify-end space-x-3">
                    <x-migapp.button variant="secondary" onclick="closeModal('create-user-modal')">
                        Cancelar
                    </x-migapp.button>
                    <x-migapp.button variant="primary" onclick="document.getElementById('create-user-form').requestSubmit()">
                        <i class="fas fa-save mr-2"></i>Crear Usuario
                    </x-migapp.button>
                </div>
            </x-slot>
        </x-migapp.modal>
    @endif
    
    <!-- Modal para editar usuario -->
    <x-migapp.modal id="edit-user-modal" max-width="md">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-user-edit mr-2 text-amber-600"></i>
                Editar Usuario
            </h3>
        </x-slot>
        
        <form id="edit-user-form" onsubmit="submitEditUser(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-user-id" name="user_id">
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre Completo *
                        </label>
                        <input type="text" id="edit-name" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="edit-email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email *
                        </label>
                        <input type="email" id="edit-email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4" id="edit-role-branch-container">
                    <div id="edit-role-container">
                        <label for="edit-role" class="block text-sm font-medium text-gray-700 mb-1">
                            Rol *
                        </label>
                        <select id="edit-role" name="role" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <!-- Options will be populated by JavaScript -->
                        </select>
                    </div>
                    
                    <div id="edit-branch-container">
                        <label for="edit-branch" class="block text-sm font-medium text-gray-700 mb-1">
                            Sucursal *
                        </label>
                        <select id="edit-branch" name="id_branch" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <!-- Options will be populated by JavaScript -->
                        </select>
                    </div>
                </div>
            </div>
        </form>
        
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-migapp.button variant="secondary" onclick="closeModal('edit-user-modal')">
                    Cancelar
                </x-migapp.button>
                <x-migapp.button variant="primary" onclick="document.getElementById('edit-user-form').requestSubmit()">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </x-migapp.button>
            </div>
        </x-slot>
    </x-migapp.modal>
    
    <!-- Modal de Confirmación para Eliminar -->
    <x-migapp.delete-confirmation 
        id="delete-user-modal"
        title="¿Eliminar Usuario?"
        message="Esta acción eliminará permanentemente al usuario del sistema." />
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
// Variables globales para usuarios
let currentUserId = null;

// Función para crear usuario
function submitCreateUser(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Mostrar loading
    const submitButton = document.querySelector('button[onclick*="create-user-form"]');
    const originalText = submitButton ? submitButton.innerHTML : '';
    if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';
        submitButton.disabled = true;
    }
    
    fetch('{{ route("users.store") }}', {
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
            closeModal('create-user-modal');
            showNotification('Usuario creado correctamente', 'success');
            window.location.reload();
        } else {
            showNotification('Error: ' + (data.message || 'No se pudo crear el usuario'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al crear el usuario', 'error');
    })
    .finally(() => {
        if (submitButton) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    });
}

// Función para editar usuario
function editUser(userId) {
    currentUserId = userId;
    
    fetch(`{{ url('users') }}/${userId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.user) {
            // Llenar formulario con datos actuales
            document.getElementById('edit-user-id').value = userId;
            document.getElementById('edit-name').value = data.user.name || '';
            document.getElementById('edit-email').value = data.user.email || '';
            
            // Llenar roles disponibles
            const roleSelect = document.getElementById('edit-role');
            roleSelect.innerHTML = '';
            for (const [value, label] of Object.entries(data.roles)) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                option.selected = value === data.user.role;
                roleSelect.appendChild(option);
            }
            
            // Llenar sucursales disponibles (solo para owners)
            const branchSelect = document.getElementById('edit-branch');
            const branchContainer = document.getElementById('edit-branch-container');
            
            if (data.branches && data.branches.length > 0) {
                branchSelect.innerHTML = '';
                data.branches.forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch.id_branch;
                    option.textContent = branch.name + (branch.is_main ? ' (Principal)' : '');
                    option.selected = branch.id_branch == data.user.id_branch;
                    branchSelect.appendChild(option);
                });
                branchContainer.style.display = 'block';
            } else {
                branchContainer.style.display = 'none';
            }
            
            openModal('edit-user-modal');
        } else {
            showNotification('Error al cargar los datos del usuario', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Función para enviar edición
function submitEditUser(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Mostrar loading
    const submitButton = document.querySelector('button[onclick*="edit-user-form"]');
    const originalText = submitButton ? submitButton.innerHTML : '';
    if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
        submitButton.disabled = true;
    }
    
    fetch(`{{ url('users') }}/${currentUserId}`, {
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
            closeModal('edit-user-modal');
            showNotification('Usuario actualizado correctamente', 'success');
            window.location.reload();
        } else {
            showNotification('Error: ' + (data.message || 'No se pudo actualizar el usuario'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al actualizar el usuario', 'error');
    })
    .finally(() => {
        if (submitButton) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
        currentUserId = null;
    });
}

// Función para eliminar usuario
function deleteUser(userId, userName) {
    currentUserId = userId;
    
    // Configurar modal de confirmación
    const modal = document.getElementById('delete-user-modal');
    const titleElement = modal.querySelector('h3');
    const itemElement = modal.querySelector('.bg-gray-50 strong');
    
    if (titleElement) titleElement.textContent = '¿Eliminar Usuario?';
    if (itemElement) itemElement.textContent = userName;
    
    // Abrir modal de confirmación
    openModal('delete-user-modal');
    
    // Configurar evento de confirmación
    window.addEventListener('confirm-delete', function(event) {
        if (event.detail.id === 'delete-user-modal') {
            performDeleteUser();
        }
    }, { once: true });
}

// Función para ejecutar eliminación
function performDeleteUser() {
    fetch(`{{ url('users') }}/${currentUserId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Usuario eliminado correctamente', 'success');
            window.location.reload();
        } else {
            showNotification('Error: ' + (data.message || 'No se pudo eliminar el usuario'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al eliminar el usuario', 'error');
    })
    .finally(() => {
        currentUserId = null;
    });
}

// Función para mostrar notificaciones
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
