@props([
    'moduleKey' => '',
    'name' => '',
    'description' => '',
    'icon' => 'fas fa-cube',
    'color' => 'blue',
    'route' => '#',
    'animationDelay' => 0
])

@php
    $routeUrl = '#';
    $isRouteAvailable = false;
    
    if ($route && $route !== '#') {
        try {
            $routeUrl = route($route);
            $isRouteAvailable = true;
        } catch (\Exception $e) {
            // Si la ruta no existe, mantener como #
            $routeUrl = '#';
            $isRouteAvailable = false;
        }
    }
@endphp

<div class="card-hover animate-fadeIn h-full" style="animation-delay: {{ $animationDelay }}s;">
    <a href="{{ $routeUrl }}" 
       class="flex flex-col h-full bg-white/95 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-white/20 hover:border-{{ $color }}-400 transition-all group {{ !$isRouteAvailable ? 'cursor-not-allowed opacity-75' : '' }}"
       @if(!$isRouteAvailable) onclick="return false;" @endif>
        
        <div class="text-center flex flex-col h-full">
            <!-- Ícono del módulo -->
            <div class="w-16 h-16 bg-{{ $color }}-100 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                <i class="{{ $icon }} text-{{ $color }}-600 text-2xl"></i>
            </div>
            
            <!-- Nombre del módulo -->
            <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-{{ $color }}-700 transition-colors">
                {{ $name }}
            </h3>
            
            <!-- Descripción -->
            <p class="text-sm text-gray-600 mb-4 group-hover:text-gray-700 transition-colors flex-grow min-h-[40px] flex items-center justify-center">
                {{ $description }}
            </p>
            
            <!-- Botón de acceso -->
            <div class="inline-flex items-center text-{{ $color }}-600 font-medium text-sm group-hover:text-{{ $color }}-700 transition-colors mt-auto">
                <span>{{ $isRouteAvailable ? 'Acceder' : 'Próximamente' }}</span>
                <i class="fas {{ $isRouteAvailable ? 'fa-arrow-right' : 'fa-lock' }} ml-2 group-hover:translate-x-1 transition-transform"></i>
            </div>
            
            @if(!$isRouteAvailable)
                <!-- Indicador de módulo no disponible -->
                <div class="absolute top-2 right-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full">
                    <i class="fas fa-clock mr-1"></i>Próximamente
                </div>
            @endif
        </div>
    </a>
</div>
