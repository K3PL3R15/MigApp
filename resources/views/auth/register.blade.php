<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro - MigApp</title>
    
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
    </style>
</head>
<body class="gradient-warm min-h-screen flex items-center justify-center p-4">
    <!-- Decoración de fondo -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-amber-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-orange-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse" style="animation-delay: 2s;"></div>
    </div>
    
    <div class="relative w-full max-w-md">
        <!-- Logo y encabezado -->
        <div class="text-center mb-8 animate-fadeIn">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <img src="{{ asset('images/Logo_MigApp.png') }}" 
                     alt="MigApp Logo" 
                     class="w-16 h-16 object-contain">
                <div class="text-white">
                    <h1 class="text-2xl font-bold">MigApp</h1>
                    <p class="text-sm text-white/80">Gestión para Panaderías</p>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-white mb-2">Crear Cuenta</h2>
            <p class="text-white/80">Comienza a gestionar tu panadería</p>
        </div>
        
        <!-- Formulario -->
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl p-8 shadow-2xl animate-fadeIn" style="animation-delay: 0.2s;">
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf
                
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-amber-600"></i>Nombre completo
                    </label>
                    <input id="name" 
                           type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           autofocus 
                           autocomplete="name"
                           class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                           placeholder="Ingresa tu nombre completo">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
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
                           autocomplete="username"
                           class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                           placeholder="tu@email.com">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Rol -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag mr-2 text-amber-600"></i>Rol en la panadería
                    </label>
                    <select id="role" 
                            name="role" 
                            required 
                            onchange="toggleBranchCode()"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all">
                        <option value="">Selecciona tu rol</option>
                        <option value="owner" {{ old('role') === 'owner' ? 'selected' : '' }}>Propietario de Panadería</option>
                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Gerente de Sucursal</option>
                        <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Empleado</option>
                    </select>
                    @error('role')
                        <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                    
                    <!-- Información sobre roles -->
                    <div class="mt-2 text-xs text-gray-600">
                        <p id="role-info" class="hidden"></p>
                    </div>
                </div>
                
                <!-- Código de Sucursal (solo para manager y empleados) -->
                <div id="branch-code-section" class="hidden">
                    <label for="branch_code" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-key mr-2 text-amber-600"></i>Código de Sucursal
                    </label>
                    <input id="branch_code" 
                           type="text" 
                           name="branch_code" 
                           value="{{ old('branch_code') }}"
                           class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                           placeholder="Ingresa el código único de la sucursal">
                    @error('branch_code')
                        <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                    <div class="mt-2 text-xs text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>Solicita este código al propietario de la panadería
                    </div>
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
                               autocomplete="new-password"
                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                               placeholder="Mínimo 8 caracteres">
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Confirmar contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-amber-600"></i>Confirmar contraseña
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" 
                               type="password" 
                               name="password_confirmation" 
                               required 
                               autocomplete="new-password"
                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                               placeholder="Repite tu contraseña">
                        <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="password_confirmation-eye"></i>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Términos y condiciones -->
                <div class="bg-amber-50 p-4 rounded-xl">
                    <p class="text-sm text-gray-600 text-center">
                        Al crear tu cuenta, aceptas nuestros 
                        <a href="#" class="text-amber-600 hover:text-amber-700 font-medium">términos de servicio</a> 
                        y <a href="#" class="text-amber-600 hover:text-amber-700 font-medium">política de privacidad</a>
                    </p>
                </div>
                
                <!-- Botones -->
                <div class="space-y-4">
                    <button type="submit" class="w-full bg-gradient-to-r from-amber-600 to-orange-600 text-white font-bold py-4 px-6 rounded-xl hover:from-amber-700 hover:to-orange-700 transition-all transform hover:scale-105 hover:shadow-lg">
                        <i class="fas fa-user-plus mr-2"></i>Crear cuenta
                    </button>
                    
                    <div class="text-center">
                        <p class="text-gray-600">¿Ya tienes cuenta?</p>
                        <a href="{{ route('login') }}" class="text-amber-600 hover:text-amber-700 font-medium hover:underline transition-colors">
                            <i class="fas fa-sign-in-alt mr-1"></i>Iniciar sesión
                        </a>
                    </div>
                </div>
            </form>
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
        
        function toggleBranchCode() {
            const roleSelect = document.getElementById('role');
            const branchSection = document.getElementById('branch-code-section');
            const branchInput = document.getElementById('branch_code');
            const roleInfo = document.getElementById('role-info');
            
            const selectedRole = roleSelect.value;
            
            // Mostrar información del rol seleccionado
            let roleDescription = '';
            switch(selectedRole) {
                case 'owner':
                    roleDescription = 'Como propietario podrás crear y gestionar múltiples sucursales.';
                    break;
                case 'manager':
                    roleDescription = 'Como gerente tendrás acceso completo a la gestión de una sucursal específica.';
                    break;
                case 'employee':
                    roleDescription = 'Como empleado tendrás acceso a las funcionalidades operativas de tu sucursal.';
                    break;
            }
            
            if (roleDescription) {
                roleInfo.textContent = roleDescription;
                roleInfo.classList.remove('hidden');
            } else {
                roleInfo.classList.add('hidden');
            }
            
            // Mostrar/ocultar campo de código de sucursal
            if (selectedRole === 'manager' || selectedRole === 'employee') {
                branchSection.classList.remove('hidden');
                branchInput.required = true;
                // Animar entrada
                branchSection.style.opacity = '0';
                branchSection.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    branchSection.style.transition = 'all 0.3s ease';
                    branchSection.style.opacity = '1';
                    branchSection.style.transform = 'translateY(0)';
                }, 10);
            } else {
                branchSection.classList.add('hidden');
                branchInput.required = false;
                branchInput.value = '';
            }
        }
        
        // Ejecutar al cargar la página para manejar old values
        document.addEventListener('DOMContentLoaded', function() {
            toggleBranchCode();
        });
    </script>
</body>
</html>
