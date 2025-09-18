<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - MigApp</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .gradient-warm {
            background: linear-gradient(135deg, #92400E 0%, #D97706 50%, #F59E0B 100%);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="gradient-warm min-h-screen">
    <!-- Header con información del usuario -->
    <header class="bg-white/10 backdrop-blur-sm border-b border-white/20 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Logo y bienvenida -->
            <div class="flex items-center space-x-4">
                <img src="{{ asset('images/Logo_MigApp.png') }}" 
                     alt="MigApp Logo" 
                     class="w-12 h-12 object-contain">
                <div class="text-white">
                    <h1 class="text-xl font-bold">Panel de Control - MigApp</h1>
                    <p class="text-sm text-white/80">Bienvenido, {{ $user->name }}</p>
                </div>
            </div>
            
            <!-- Información del usuario y logout -->
            <div class="flex items-center space-x-4">
                <div class="text-white text-right">
                    <p class="text-sm font-medium">{{ $user->role_name }}</p>
                    @if($user->branch)
                        <p class="text-xs text-white/80">{{ $user->branch->name }}</p>
                    @endif
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors font-medium">
                        <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </header>
    
    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-8">
        <!-- Tarjetas de módulos -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($modules as $moduleKey => $module)
                <div class="card-hover animate-fadeIn" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                    <a href="#" class="block bg-white/95 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-white/20 hover:border-{{ $module['color'] }}-400 transition-all">
                        <div class="text-center">
                            <!-- Ícono del módulo -->
                            <div class="w-16 h-16 bg-{{ $module['color'] }}-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="{{ $module['icon'] }} text-{{ $module['color'] }}-600 text-2xl"></i>
                            </div>
                            
                            <!-- Nombre del módulo -->
                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $module['name'] }}</h3>
                            
                            <!-- Descripción -->
                            <p class="text-sm text-gray-600 mb-4">{{ $module['description'] }}</p>
                            
                            <!-- Botón de acceso -->
                            <div class="inline-flex items-center text-{{ $module['color'] }}-600 font-medium text-sm">
                                <span>Acceder</span>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        
        <!-- Información adicional -->
        <div class="mt-12 text-center animate-fadeIn" style="animation-delay: 0.8s;">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 max-w-2xl mx-auto">
                <h3 class="text-lg font-semibold text-white mb-2">
                    <i class="fas fa-info-circle mr-2 text-amber-300"></i>
                    Información de Acceso
                </h3>
                <p class="text-white/80 text-sm">
                    Los módulos mostrados corresponden a tu rol: <strong>{{ $user->role_name }}</strong>.
                    @if($user->branch)
                        Trabajas en: <strong>{{ $user->branch->name }}</strong>.
                    @else
                        Como propietario, puedes gestionar múltiples sucursales.
                    @endif
                </p>
            </div>
        </div>
    </main>
</body>
</html>
