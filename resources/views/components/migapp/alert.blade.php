@props([
    'type' => 'info',
    'message' => '',
    'dismissible' => true,
    'autoHide' => true
])

@php
    $typeClasses = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700',
    ];
    
    $icons = [
        'success' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
    ];
    
    $class = $typeClasses[$type] ?? $typeClasses['info'];
    $icon = $icons[$type] ?? $icons['info'];
@endphp

<div class="border-l-4 p-4 mb-4 rounded-r-lg {{ $class }} shadow-sm animate-fadeIn"
     @if($autoHide) data-auto-hide="true" @endif
     x-data="{ show: true }" 
     x-show="show" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-90"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-90">
    
    <div class="flex items-start">
        <!-- Icono -->
        <div class="flex-shrink-0">
            <i class="{{ $icon }} text-lg mr-3 mt-0.5"></i>
        </div>
        
        <!-- Mensaje -->
        <div class="flex-1">
            @if(is_string($message))
                <p class="font-medium">{{ $message }}</p>
            @else
                {{ $message }}
            @endif
        </div>
        
        <!-- Botón de cerrar -->
        @if($dismissible)
            <div class="flex-shrink-0 ml-4">
                <button @click="show = false" 
                        class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition ease-in-out duration-150">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        @endif
    </div>
</div>
