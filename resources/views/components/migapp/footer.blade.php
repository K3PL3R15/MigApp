@props(['showInfo' => true])

<footer class="mt-12 pb-6">
    @if($showInfo)
        <!-- Información de la aplicación -->
        <div class="container mx-auto px-4">
            <div class="text-center animate-fadeIn bg-white/10 backdrop-blur-sm rounded-2xl p-6 max-w-2xl mx-auto">
                <div class="flex items-center justify-center space-x-2 mb-3">
                    <img src="{{ asset('images/Logo_MigApp.png') }}" 
                         alt="MigApp Logo" 
                         class="w-8 h-8 object-contain">
                    <h3 class="text-lg font-semibold text-white">
                        MigApp - Sistema de Gestión
                    </h3>
                </div>
                
                <p class="text-white/80 text-sm mb-4">
                    Solución integral para la gestión de panaderías y pequeños negocios.
                    Controla tu inventario, ventas, personal y sucursales desde un solo lugar.
                </p>
                
                @auth
                    <div class="flex items-center justify-center space-x-4 text-white/70 text-xs">
                        <span>
                            <i class="fas fa-user mr-1"></i>
                            {{ auth()->user()->role_name ?? ucfirst(auth()->user()->role) }}
                        </span>
                        
                        @if(auth()->user()->branch)
                            <span>
                                <i class="fas fa-store mr-1"></i>
                                {{ auth()->user()->branch->name }}
                            </span>
                        @endif
                        
                        <span>
                            <i class="fas fa-clock mr-1"></i>
                            {{ now()->format('d/m/Y H:i') }}
                        </span>
                    </div>
                @endauth
            </div>
        </div>
    @endif
    
    <!-- Footer legal y links -->
    <div class="container mx-auto px-4 mt-6">
        <div class="border-t border-white/20 pt-4">
            <div class="flex flex-col md:flex-row justify-between items-center text-white/60 text-sm">
                <!-- Copyright -->
                <div class="mb-2 md:mb-0">
                    <p>&copy; {{ date('Y') }} MigApp. Todos los derechos reservados.</p>
                </div>
                
                <!-- Links -->
                <div class="flex space-x-6">
                    <a href="#" class="hover:text-white transition-colors">
                        <i class="fas fa-question-circle mr-1"></i>Ayuda
                    </a>
                    <a href="#" class="hover:text-white transition-colors">
                        <i class="fas fa-shield-alt mr-1"></i>Privacidad
                    </a>
                    <a href="#" class="hover:text-white transition-colors">
                        <i class="fas fa-file-contract mr-1"></i>Términos
                    </a>
                </div>
            </div>
            
            <!-- Versión y estado del sistema -->
            <div class="text-center mt-3 text-white/40 text-xs">
                <span>v1.0.0</span>
                <span class="mx-2">•</span>
                <span class="inline-flex items-center">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                    Sistema Operativo
                </span>
                @if(config('app.env') !== 'production')
                    <span class="mx-2">•</span>
                    <span class="text-yellow-400">{{ strtoupper(config('app.env')) }}</span>
                @endif
            </div>
        </div>
    </div>
</footer>
