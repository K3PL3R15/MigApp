@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'loading' => false,
    'disabled' => false
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variants = [
        'primary' => 'bg-amber-600 hover:bg-amber-700 text-white focus:ring-amber-500',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
        'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
        'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white focus:ring-yellow-500',
        'info' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
        'outline-primary' => 'border-2 border-amber-600 text-amber-600 hover:bg-amber-600 hover:text-white focus:ring-amber-500',
        'outline-secondary' => 'border-2 border-gray-600 text-gray-600 hover:bg-gray-600 hover:text-white focus:ring-gray-500',
        'ghost' => 'text-gray-600 hover:bg-gray-100 focus:ring-gray-500',
        'link' => 'text-amber-600 hover:text-amber-700 underline-offset-4 hover:underline focus:ring-amber-500'
    ];
    
    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs rounded',
        'sm' => 'px-3 py-2 text-sm rounded-md',
        'md' => 'px-6 py-2 text-sm rounded-lg',
        'lg' => 'px-8 py-3 text-base rounded-lg',
        'xl' => 'px-10 py-4 text-lg rounded-xl'
    ];
    
    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    
    $classes = $baseClasses . ' ' . $variantClass . ' ' . $sizeClass;
@endphp

@if($href)
    <!-- Link Button -->
    <a href="{{ $href }}" 
       {{ $attributes->merge(['class' => $classes]) }}
       @if($disabled) onclick="return false;" @endif>
        
        @if($loading)
            <i class="fas fa-spinner fa-spin mr-2"></i>
        @elseif($icon)
            <i class="{{ $icon }} mr-2"></i>
        @endif
        
        {{ $slot }}
    </a>
@else
    <!-- Button Element -->
    <button type="{{ $type }}" 
            {{ $attributes->merge(['class' => $classes]) }}
            @if($disabled || $loading) disabled @endif>
        
        @if($loading)
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Cargando...
        @else
            @if($icon)
                <i class="{{ $icon }} mr-2"></i>
            @endif
            {{ $slot }}
        @endif
    </button>
@endif
