@props([
    'id' => 'delete-confirmation',
    'title' => '¿Confirmar eliminación?',
    'message' => '¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.',
    'itemName' => null,
    'action' => null,
    'method' => 'DELETE'
])

<x-migapp.modal :id="$id" max-width="md" :closeable="true">
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
            </div>
            <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
        </div>
    </x-slot>
    
    <div class="space-y-4">
        <!-- Mensaje principal -->
        <div class="text-gray-600">
            {{ $message }}
        </div>
        
        <!-- Nombre del elemento (si se proporciona) -->
        @if($itemName)
            <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-amber-400">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-amber-500 mr-2"></i>
                    <span class="text-sm text-gray-700">
                        Elemento a eliminar: <strong>{{ $itemName }}</strong>
                    </span>
                </div>
            </div>
        @endif
        
        <!-- Advertencias adicionales -->
        <div class="bg-red-50 rounded-lg p-4 border-l-4 border-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-red-800">
                        Advertencia
                    </h4>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Esta acción es permanente y no se puede deshacer</li>
                            <li>Se perderán todos los datos relacionados</li>
                            <li>Puede afectar otros elementos del sistema</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <x-slot name="footer">
        <div class="flex justify-end space-x-3">
            <!-- Botón Cancelar -->
            <x-migapp.button 
                variant="secondary" 
                @click="close()" 
                type="button">
                <i class="fas fa-times mr-2"></i>
                Cancelar
            </x-migapp.button>
            
            <!-- Formulario de eliminación -->
            @if($action)
                <form action="{{ $action }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('¿Estás completamente seguro?');">
                    @csrf
                    @method($method)
                    
                    <x-migapp.button 
                        variant="danger" 
                        type="submit">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Sí, Eliminar
                    </x-migapp.button>
                </form>
            @else
                <!-- Botón personalizable (para acciones AJAX) -->
                <x-migapp.button 
                    variant="danger" 
                    x-on:click="$dispatch('confirm-delete', { id: '{{ $id }}' }); close()">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Sí, Eliminar
                </x-migapp.button>
            @endif
        </div>
    </x-slot>
</x-migapp.modal>

@push('scripts')
<script>
    // Función global para abrir confirmación de eliminación
    window.confirmDelete = function(options = {}) {
        const modalId = options.modalId || 'delete-confirmation';
        
        // Actualizar contenido del modal si se proporcionan opciones
        if (options.title) {
            const titleElement = document.querySelector(`#${modalId} h3`);
            if (titleElement) titleElement.textContent = options.title;
        }
        
        if (options.message) {
            const messageElement = document.querySelector(`#${modalId} .text-gray-600`);
            if (messageElement) messageElement.textContent = options.message;
        }
        
        if (options.itemName) {
            const itemElement = document.querySelector(`#${modalId} .bg-gray-50 strong`);
            if (itemElement) itemElement.textContent = options.itemName;
        }
        
        // Abrir modal
        window.openModal(modalId);
        
        return new Promise((resolve, reject) => {
            // Escuchar evento de confirmación
            const handleConfirm = (event) => {
                if (event.detail.id === modalId) {
                    resolve(true);
                    window.removeEventListener('confirm-delete', handleConfirm);
                }
            };
            
            window.addEventListener('confirm-delete', handleConfirm);
            
            // Timeout para limpiar el listener si no se confirma
            setTimeout(() => {
                window.removeEventListener('confirm-delete', handleConfirm);
                reject(false);
            }, 60000); // 1 minuto timeout
        });
    };
</script>
@endpush
