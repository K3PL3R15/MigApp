@props([
    'size' => 'md',
    'type' => 'spinner',
    'message' => 'Cargando...',
    'overlay' => false,
    'color' => 'amber'
])

@php
    $sizes = [
        'xs' => 'w-4 h-4',
        'sm' => 'w-6 h-6', 
        'md' => 'w-8 h-8',
        'lg' => 'w-12 h-12',
        'xl' => 'w-16 h-16'
    ];
    
    $colors = [
        'amber' => 'text-amber-600',
        'blue' => 'text-blue-600',
        'green' => 'text-green-600',
        'red' => 'text-red-600',
        'gray' => 'text-gray-600',
        'white' => 'text-white'
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $colorClass = $colors[$color] ?? $colors['amber'];
@endphp

@if($overlay)
    <!-- Loading Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 shadow-2xl flex flex-col items-center space-y-4 max-w-sm mx-4">
@endif

<div class="flex items-center justify-center space-x-3 {{ $overlay ? '' : 'py-4' }}">
    @switch($type)
        @case('spinner')
            <div class="{{ $sizeClass }} {{ $colorClass }}">
                <i class="fas fa-spinner fa-spin text-inherit"></i>
            </div>
            @break
            
        @case('dots')
            <div class="flex space-x-1">
                <div class="w-2 h-2 bg-current rounded-full animate-bounce {{ $colorClass }}" style="animation-delay: 0ms;"></div>
                <div class="w-2 h-2 bg-current rounded-full animate-bounce {{ $colorClass }}" style="animation-delay: 150ms;"></div>
                <div class="w-2 h-2 bg-current rounded-full animate-bounce {{ $colorClass }}" style="animation-delay: 300ms;"></div>
            </div>
            @break
            
        @case('pulse')
            <div class="{{ $sizeClass }} {{ $colorClass }} rounded-full animate-pulse">
                <div class="w-full h-full bg-current rounded-full opacity-75"></div>
            </div>
            @break
            
        @default
            <div class="{{ $sizeClass }} {{ $colorClass }}">
                <i class="fas fa-spinner fa-spin text-inherit"></i>
            </div>
    @endswitch
    
    @if($message)
        <span class="text-sm font-medium {{ $overlay ? 'text-gray-700' : $colorClass }}">{{ $message }}</span>
    @endif
</div>

@if($overlay)
        </div>
    </div>
@endif
