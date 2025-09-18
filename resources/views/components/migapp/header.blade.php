@props(['user'])

<header class="bg-white/10 backdrop-blur-sm border-b border-white/20 p-4">
    <div class="container mx-auto flex justify-between items-center">
        <!-- Logo y bienvenida -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-4 hover:opacity-80 transition-opacity">
                <img src="{{ asset('images/Logo_MigApp.png') }}" 
                     alt="MigApp Logo" 
                     class="w-12 h-12 object-contain">
                <div class="text-white">
                    <h1 class="text-xl font-bold">Panel de Control - MigApp</h1>
                    <p class="text-sm text-white/80">Bienvenido, {{ $user->name }}</p>
                </div>
            </a>
        </div>
        
        <!-- Información del usuario y acciones -->
        <div class="flex items-center space-x-4">
            <!-- Información del usuario -->
            <div class="text-white text-right hidden md:block">
                <p class="text-sm font-medium">{{ $user->role_name ?? ucfirst($user->role) }}</p>
                @if($user->branch)
                    <p class="text-xs text-white/80">{{ $user->branch->name }}</p>
                @endif
            </div>
            
            <!-- Dropdown de usuario (versión desktop) -->
            <div class="relative hidden md:block" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center space-x-2 bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors font-medium">
                    <i class="fas fa-user-circle"></i>
                    <span class="hidden lg:inline">{{ $user->name }}</span>
                    <i class="fas fa-chevron-down text-xs" :class="{'rotate-180': open}"></i>
                </button>
                
                <!-- Dropdown menu -->
                <div x-show="open" 
                     x-transition
                     @click.away="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-[9999]">
                    <a href="{{ route('dashboard') }}" 
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    
                    @if($user->role === 'owner')
                        <a href="#" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-cog mr-2"></i>Configuración
                        </a>
                        <div class="border-t border-gray-100"></div>
                    @endif
                    
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" 
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Botón móvil de logout -->
            <form method="POST" action="{{ route('logout') }}" class="inline md:hidden">
                @csrf
                <button type="submit" 
                        class="bg-white/20 hover:bg-white/30 text-white p-2 rounded-lg transition-colors"
                        title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</header>

<!-- Script para Alpine.js (si no está incluido globalmente) -->
@push('scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
