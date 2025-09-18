@props([
    'id' => 'modal',
    'maxWidth' => 'lg',
    'closeable' => true,
    'show' => false
])

@php
    $maxWidthClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
    ];
    
    $maxWidthClass = $maxWidthClasses[$maxWidth] ?? $maxWidthClasses['lg'];
@endphp

<div x-data="{ 
        show: @js($show),
        close() { 
            this.show = false; 
            setTimeout(() => this.$el.style.display = 'none', 200);
        },
        open() { 
            this.$el.style.display = 'flex'; 
            setTimeout(() => this.show = true, 10);
        }
     }"
     x-show="show"
     x-on:open-modal.window="if ($event.detail.id === '{{ $id }}') open()"
     x-on:close-modal.window="if ($event.detail.id === '{{ $id }}') close()"
     x-on:keydown.escape.window="if (show && {{ $closeable ? 'true' : 'false' }}) close()"
     id="{{ $id }}"
     class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50 modal-backdrop"
     style="display: none;">
    
    <div x-show="show" 
         class="fixed inset-0 transform transition-all" 
         x-on:click="@if($closeable) close() @endif"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div x-show="show" 
         class="mb-6 bg-white rounded-lg shadow-xl transform transition-all sm:w-full {{ $maxWidthClass }} sm:mx-auto"
         x-on:click.stop
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        
        <!-- Modal Header -->
        @isset($header)
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>{{ $header }}</div>
                
                @if($closeable)
                    <button @click="close()" 
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                @endif
            </div>
        @else
            @if($closeable)
                <div class="absolute top-4 right-4 z-10">
                    <button @click="close()" 
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            @endif
        @endisset

        <!-- Modal Body -->
        <div class="px-6 py-4">
            {{ $slot }}
        </div>

        <!-- Modal Footer -->
        @isset($footer)
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
