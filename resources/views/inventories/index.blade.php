@extends('layouts.migapp')

@section('title', 'Inventarios - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-boxes mr-3"></i>Gestión de Inventarios
                </h1>
                <p class="text-white/80 mt-2">
                    Controla y administra todos los inventarios de tu panadería
                </p>
            </div>
            
            @if(in_array(auth()->user()->role, ['owner', 'manager']))
                <x-migapp.button 
                    variant="primary" 
                    icon="fas fa-plus"
                    onclick="openModal('create-inventory')">
                    Nuevo Inventario
                </x-migapp.button>
            @endif
        </div>
        
        <!-- Estadísticas -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 stats-container">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-boxes text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['total_inventories'] }}</div>
                        <div class="text-white/70 text-xs">Total Inventarios</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bread-slice text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['productos_panaderia'] }}</div>
                        <div class="text-white/70 text-xs">Productos de Venta</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-seedling text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['materias_primas'] }}</div>
                        <div class="text-white/70 text-xs">Materias Primas</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-amber-300">{{ $stats['stock_bajo'] }}</div>
                        <div class="text-white/70 text-xs">Stock Crítico</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de inventarios -->
        <div class="glass-card rounded-lg p-6">
            @if($inventories->isEmpty())
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay inventarios</h3>
                    <p class="text-gray-500 mb-6">Comienza creando tu primer inventario</p>
                    
                    @if(in_array(auth()->user()->role, ['owner', 'manager']))
                        <x-migapp.button 
                            variant="primary" 
                            icon="fas fa-plus"
                            onclick="openModal('create-inventory')">
                            Crear Inventario
                        </x-migapp.button>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($inventories as $inventory)
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow group relative">
                            <!-- Enlace que cubre toda la tarjeta -->
                            <a href="{{ route('inventories.products.index', $inventory) }}" 
                               class="block p-6 cursor-pointer">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                                            <i class="fas {{ $inventory->type === 'sale_product' ? 'fa-bread-slice' : 'fa-seedling' }} text-blue-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $inventory->name }}</h3>
                                            <p class="text-sm text-gray-600">{{ $inventory->branch->name }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $inventory->products_count }}</div>
                                        <div class="text-xs text-gray-500">Productos</div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center pt-4 border-t">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $inventory->type === 'sale_product' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ $inventory->type === 'sale_product' ? 'Productos de Venta' : 'Materias Primas' }}
                                    </span>
                                    
                                    <div class="text-xs text-gray-500 group-hover:text-blue-600 transition-colors">
                                        <i class="fas fa-arrow-right mr-1"></i>
                                        Ver productos
                                    </div>
                                </div>
                            </a>
                            
                            <!-- Botones de acción en posición absoluta -->
                            <div class="absolute top-4 right-4 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                @can('update', $inventory)
                                    <button onclick="event.preventDefault(); event.stopPropagation(); editInventory({{ $inventory->id_inventory }});" 
                                            class="bg-white shadow-md rounded-full w-8 h-8 flex items-center justify-center text-amber-600 hover:bg-amber-50 hover:text-amber-700 transition-colors" 
                                            title="Editar inventario">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                @endcan
                                
                                @can('delete', $inventory)
                                    <button onclick="event.preventDefault(); event.stopPropagation(); deleteInventory({{ $inventory->id_inventory }}, '{{ $inventory->name }}');" 
                                            class="bg-white shadow-md rounded-full w-8 h-8 flex items-center justify-center text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors" 
                                            title="Eliminar inventario">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <!-- Modal para Crear Inventario -->
    <x-migapp.modal id="create-inventory" max-width="lg">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-plus-circle mr-2 text-blue-600"></i>
                Crear Nuevo Inventario
            </h3>
        </x-slot>
        
        <form id="create-inventory-form" onsubmit="submitCreateInventory(event)">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="create-name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre del Inventario *
                    </label>
                    <input type="text" id="create-name" name="name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           placeholder="Ej: Inventario Principal" required>
                </div>
                
                <div>
                    <label for="create-type" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Inventario *
                    </label>
                    <select id="create-type" name="type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" required>
                        <option value="">Seleccionar tipo...</option>
                        <option value="sale_product">Productos de Venta</option>
                        <option value="raw_material">Materias Primas</option>
                    </select>
                </div>
                
                <div>
                    <label for="create-branch" class="block text-sm font-medium text-gray-700 mb-1">
                        Sucursal *
                    </label>
                    <select id="create-branch" name="id_branch" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" required>
                        <option value="">Seleccionar sucursal...</option>
                        @foreach($availableBranches as $branch)
                            <option value="{{ $branch->id_branch }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mt-6 text-sm text-gray-600">
                <p><strong>Tipos de inventario:</strong></p>
                <ul class="mt-2 space-y-1 text-xs">
                    <li>• <strong>Productos de Venta:</strong> Pan, pasteles, productos terminados</li>
                    <li>• <strong>Materias Primas:</strong> Harina, azúcar, ingredientes</li>
                </ul>
            </div>
        </form>
        
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-migapp.button variant="secondary" onclick="closeModal('create-inventory')">
                    Cancelar
                </x-migapp.button>
                <x-migapp.button variant="primary" onclick="document.getElementById('create-inventory-form').requestSubmit()">
                    <i class="fas fa-save mr-2"></i>Crear Inventario
                </x-migapp.button>
            </div>
        </x-slot>
    </x-migapp.modal>
    
    <!-- Modal para Ver Inventario -->
    <x-migapp.modal id="view-inventory" max-width="xl">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-eye mr-2 text-blue-600"></i>
                <span id="view-inventory-title">Detalles del Inventario</span>
            </h3>
        </x-slot>
        
        <div id="view-inventory-content" class="space-y-4">
            <x-migapp.loading type="spinner" message="Cargando detalles..." />
        </div>
        
        <x-slot name="footer">
            <div class="flex justify-end">
                <x-migapp.button variant="secondary" onclick="closeModal('view-inventory')">
                    Cerrar
                </x-migapp.button>
            </div>
        </x-slot>
    </x-migapp.modal>
    
    <!-- Modal para Editar Inventario -->
    <x-migapp.modal id="edit-inventory" max-width="lg">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-edit mr-2 text-amber-600"></i>
                Editar Inventario
            </h3>
        </x-slot>
        
        <form id="edit-inventory-form" onsubmit="submitEditInventory(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-inventory-id" name="inventory_id">
            
            <div class="space-y-4">
                <div>
                    <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre del Inventario *
                    </label>
                    <input type="text" id="edit-name" name="name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           required>
                </div>
                
                <div>
                    <label for="edit-type" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Inventario *
                    </label>
                    <select id="edit-type" name="type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" required>
                        <option value="sale_product">Productos de Venta</option>
                        <option value="raw_material">Materias Primas</option>
                    </select>
                </div>
                
                <div>
                    <label for="edit-branch" class="block text-sm font-medium text-gray-700 mb-1">
                        Sucursal *
                    </label>
                    <select id="edit-branch" name="id_branch" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" required>
                        @foreach($availableBranches as $branch)
                            <option value="{{ $branch->id_branch }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
        
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-migapp.button variant="secondary" onclick="closeModal('edit-inventory')">
                    Cancelar
                </x-migapp.button>
                <x-migapp.button variant="primary" onclick="document.getElementById('edit-inventory-form').requestSubmit()">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </x-migapp.button>
            </div>
        </x-slot>
    </x-migapp.modal>
    
    <!-- Modal de Confirmación para Eliminar -->
    <x-migapp.delete-confirmation 
        id="delete-inventory-modal"
        title="¿Eliminar Inventario?"
        message="Esta acción eliminará permanentemente el inventario y todos los productos que contiene." />
@endsection

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .inventory-detail {
        @apply bg-gray-50 rounded-lg p-4 border border-gray-200;
    }
    
    .inventory-stat {
        @apply text-center p-4 bg-white rounded-lg shadow-sm;
    }
    
    .inventory-stat-value {
        @apply text-2xl font-bold text-gray-800;
    }
    
    .inventory-stat-label {
        @apply text-sm text-gray-600 mt-1;
    }
</style>
@endpush

@push('scripts')
<script>
// Variables globales para inventarios
let currentInventoryId = null;

// Función para crear inventario
function submitCreateInventory(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Mostrar loading
    const submitButton = document.querySelector('button[onclick*="create-inventory-form"]');
    const originalText = submitButton ? submitButton.innerHTML : '';
    if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';
        submitButton.disabled = true;
    }
    
    fetch('{{ route("inventories.store") }}', {
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
            closeModal('create-inventory');
            window.location.reload(); // Recargar página para mostrar el nuevo inventario
        } else {
            alert('Error: ' + (data.message || 'No se pudo crear el inventario'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear el inventario');
    })
    .finally(() => {
        if (submitButton) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    });
}

// Función para ver inventario
function viewInventory(inventoryId) {
    currentInventoryId = inventoryId;
    
    // Abrir modal y mostrar loading
    openModal('view-inventory');
    document.getElementById('view-inventory-content').innerHTML = `
        <div class="flex items-center justify-center py-8">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-2"></i>
                <p class="text-gray-600">Cargando detalles del inventario...</p>
            </div>
        </div>
    `;
    
    fetch(`{{ url('inventories') }}/${inventoryId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayInventoryDetails(data.inventory, data.analysis);
        } else {
            document.getElementById('view-inventory-content').innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                    <p>Error al cargar los detalles</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('view-inventory-content').innerHTML = `
            <div class="text-center py-8 text-red-600">
                <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                <p>Error al cargar los detalles</p>
            </div>
        `;
    });
}

// Función para mostrar detalles del inventario
function displayInventoryDetails(inventory, analysis) {
    document.getElementById('view-inventory-title').textContent = inventory.name;
    
    const content = `
        <div class="space-y-6">
            <!-- Información básica -->
            <div class="inventory-detail">
                <h4 class="font-semibold text-gray-800 mb-3">Información General</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Nombre:</label>
                        <p class="text-gray-800">${inventory.name}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Tipo:</label>
                        <p class="text-gray-800">${inventory.type === 'sale_product' ? 'Productos de Venta' : 'Materias Primas'}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Sucursal:</label>
                        <p class="text-gray-800">${inventory.branch.name}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Creado:</label>
                        <p class="text-gray-800">${new Date(inventory.created_at).toLocaleDateString('es-ES')}</p>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas de productos -->
            <div>
                <h4 class="font-semibold text-gray-800 mb-3">Análisis de Productos</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="inventory-stat">
                        <div class="inventory-stat-value text-blue-600">${analysis.productos_frescos.length}</div>
                        <div class="inventory-stat-label">Productos Frescos</div>
                    </div>
                    <div class="inventory-stat">
                        <div class="inventory-stat-value text-green-600">${analysis.productos_secos.length}</div>
                        <div class="inventory-stat-label">Productos Secos</div>
                    </div>
                    <div class="inventory-stat">
                        <div class="inventory-stat-value text-amber-600">${analysis.proximos_vencer.length}</div>
                        <div class="inventory-stat-label">Por Vencer</div>
                    </div>
                    <div class="inventory-stat">
                        <div class="inventory-stat-value text-red-600">${analysis.stock_critico.length}</div>
                        <div class="inventory-stat-label">Stock Crítico</div>
                    </div>
                </div>
            </div>
            
            <!-- Productos recientes -->
            <div>
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-semibold text-gray-800">Productos</h4>
                    <a href="{{ url('inventories') }}/${inventory.id_inventory}" 
                       class="text-blue-600 hover:text-blue-800 text-sm">
                        Ver todos los productos <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-center text-gray-600">
                        <i class="fas fa-box mr-2"></i>
                        ${inventory.products_count || 0} productos en este inventario
                    </p>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('view-inventory-content').innerHTML = content;
}

// Función para editar inventario
function editInventory(inventoryId) {
    currentInventoryId = inventoryId;
    
    console.log('Iniciando edición de inventario:', inventoryId);
    
    // Cargar datos del inventario usando la ruta edit
    fetch(`{{ url('inventories') }}/${inventoryId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos del servidor:', data);
        
        if (data.success && data.inventory) {
            // Asegurar que el modal esté completamente cargado antes de llenar
            setTimeout(() => {
                // Llenar formulario con datos actuales
                const nameField = document.getElementById('edit-name');
                const typeField = document.getElementById('edit-type');
                const branchField = document.getElementById('edit-branch');
                const idField = document.getElementById('edit-inventory-id');
                
                if (nameField) nameField.value = data.inventory.name || '';
                if (typeField) typeField.value = data.inventory.type || '';
                if (branchField) branchField.value = data.inventory.id_branch || '';
                if (idField) idField.value = inventoryId;
                
                // Si hay datos de sucursales disponibles, actualizar el select
                if (data.branches && data.branches.length > 0 && branchField) {
                    branchField.innerHTML = '';
                    
                    data.branches.forEach(branch => {
                        const option = document.createElement('option');
                        option.value = branch.id_branch;
                        option.textContent = branch.name;
                        option.selected = branch.id_branch == data.inventory.id_branch;
                        branchField.appendChild(option);
                    });
                }
                
                console.log('Formulario llenado con:', {
                    name: nameField ? nameField.value : 'campo no encontrado',
                    type: typeField ? typeField.value : 'campo no encontrado', 
                    branch: branchField ? branchField.value : 'campo no encontrado',
                    id: idField ? idField.value : 'campo no encontrado'
                });
            }, 100);
            
            openModal('edit-inventory');
        } else {
            throw new Error(data.message || 'Error al cargar los datos del inventario');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los datos del inventario: ' + error.message);
    });
}

// Función para enviar edición
function submitEditInventory(event) {
    event.preventDefault();
    
    // Verificar que todos los campos estén llenos
    const name = document.getElementById('edit-name').value.trim();
    const type = document.getElementById('edit-type').value;
    const branchId = document.getElementById('edit-branch').value;
    
    if (!name || !type || !branchId) {
        alert('Por favor, complete todos los campos obligatorios.');
        return;
    }
    
    // Crear FormData con todos los campos necesarios
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('name', name);
    formData.append('type', type);
    formData.append('id_branch', branchId);
    
    console.log('Enviando datos:', {
        name: name,
        type: type,
        id_branch: branchId
    });
    
    // Mostrar loading
    const submitButton = document.querySelector('#edit-inventory button[onclick*="edit-inventory-form"]');
    const originalText = submitButton ? submitButton.innerHTML : '';
    if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
        submitButton.disabled = true;
    }
    
    fetch(`{{ url('inventories') }}/${currentInventoryId}`, {
        method: 'POST', // Usar POST con _method=PUT para formularios
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeModal('edit-inventory');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo actualizar el inventario'));
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error al actualizar el inventario: ' + error.message);
    })
    .finally(() => {
        if (submitButton) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    });
}

// Función para eliminar inventario
function deleteInventory(inventoryId, inventoryName) {
    currentInventoryId = inventoryId;
    
    // Configurar modal de confirmación
    const modal = document.getElementById('delete-inventory-modal');
    const titleElement = modal.querySelector('h3');
    const itemElement = modal.querySelector('.bg-gray-50 strong');
    
    if (titleElement) titleElement.textContent = '¿Eliminar Inventario?';
    if (itemElement) itemElement.textContent = inventoryName;
    
    // Abrir modal de confirmación
    openModal('delete-inventory-modal');
    
    // Configurar evento de confirmación
    window.addEventListener('confirm-delete', function(event) {
        if (event.detail.id === 'delete-inventory-modal') {
            performDeleteInventory();
        }
    }, { once: true });
}

// Función para ejecutar eliminación
function performDeleteInventory() {
    fetch(`{{ url('inventories') }}/${currentInventoryId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo eliminar el inventario'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el inventario');
    });
}
</script>
@endpush
