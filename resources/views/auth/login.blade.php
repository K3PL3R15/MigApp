<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión - MigApp</title>
    
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
        
        .animate-fadeIn {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-input {
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .pulse-slow {
            animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse-slow {
            0%, 100% {
                opacity: 0.3;
            }
            50% {
                opacity: 0.1;
            }
        }
    </style>
</head>
<body class="gradient-warm min-h-screen flex items-center justify-center p-4">
    <!-- Decoración de fondo -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-amber-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-orange-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 pulse-slow"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-pulse" style="animation-delay: 1s;"></div>
    </div>
    
    <div class="relative w-full max-w-md">
        <!-- Logo y encabezado -->
        <div class="text-center mb-8 animate-fadeIn">
            <div class="flex items-center justify-center space-x-3 mb-6">
                <img src="{{ asset('images/Logo_MigApp.png') }}" 
                     alt="MigApp Logo" 
                     class="w-20 h-20 object-contain">
                <div class="text-white">
                    <h1 class="text-3xl font-bold">MigApp</h1>
                    <p class="text-sm text-white/80">Gestión para Panaderías</p>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-white mb-2">¡Bienvenido!</h2>
            <p class="text-white/80">Inicia sesión para acceder a tu panadería</p>
        </div>
        
        <!-- Mensajes de estado -->
        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 animate-fadeIn">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('status') }}
                </div>
            </div>
        @endif
        
        <!-- Formulario de login -->
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl p-8 shadow-2xl animate-fadeIn" style="animation-delay: 0.2s;">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-amber-600"></i>Correo electrónico
                    </label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                           placeholder="tu@email.com">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-amber-600"></i>Contraseña
                    </label>
                    <div class="relative">
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                               placeholder="Ingresa tu contraseña">
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Recordarme y olvidé contraseña -->
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" 
                               type="checkbox" 
                               name="remember" 
                               class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500 transition-colors">
                        <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" 
                           class="text-sm text-amber-600 hover:text-amber-700 font-medium hover:underline transition-colors">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>
                
                <!-- Botón de login -->
                <div class="space-y-4">
                    <button type="submit" class="w-full bg-gradient-to-r from-amber-600 to-orange-600 text-white font-bold py-4 px-6 rounded-xl hover:from-amber-700 hover:to-orange-700 transition-all transform hover:scale-105 hover:shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar sesión
                    </button>
                    
                    <!-- Divider -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">o</span>
                        </div>
                    </div>
                    
                    <!-- Registro -->
                    <div class="text-center">
                        <p class="text-gray-600 mb-2">¿No tienes cuenta?</p>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border-2 border-amber-600 text-amber-600 font-medium rounded-xl hover:bg-amber-600 hover:text-white transition-all transform hover:scale-105">
                            <i class="fas fa-user-plus mr-2"></i>Crear cuenta gratis
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Beneficios rápidos -->
        <div class="mt-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white animate-fadeIn" style="animation-delay: 0.4s;">
            <h3 class="text-lg font-semibold mb-4 text-center">¿Por qué elegir MigApp?</h3>
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center">
                        <i class="fas fa-boxes text-amber-900 text-sm"></i>
                    </div>
                    <span class="text-sm">Control completo de inventarios</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-amber-900 text-sm"></i>
                    </div>
                    <span class="text-sm">Reportes detallados de ventas</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center">
                        <i class="fas fa-mobile-alt text-amber-900 text-sm"></i>
                    </div>
                    <span class="text-sm">Fácil de usar desde cualquier dispositivo</span>
                </div>
            </div>
        </div>
        
        <!-- Volver al inicio -->
        <div class="text-center mt-6">
            <a href="{{ route('welcome') }}" class="text-white/80 hover:text-white transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Volver al inicio
            </a>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(inputId + '-eye');
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
