<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nueva Sucursal - MigApp</title>
    
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
        
        .step-indicator {
            transition: all 0.3s ease;
        }
        
        .step-indicator.active {
            background: linear-gradient(45deg, #F59E0B, #D97706);
            color: white;
            transform: scale(1.1);
        }
        
        .form-input {
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-setup {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-setup:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="gradient-warm min-h-screen">
    <!-- Header con progreso -->
    <header class="bg-white/10 backdrop-blur-sm border-b border-white/20">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/Logo_MigApp.png') }}" 
                         alt="MigApp Logo" 
                         class="w-10 h-10 object-contain">
                    <div class="text-white">
                        <h1 class="text-lg font-bold">MigApp</h1>
                        <p class="text-xs text-white/80">Nueva Sucursal</p>
                    </div>
                </div>
                
                <!-- Indicador de pasos -->
                <div class="flex items-center space-x-2">
                    <div class="step-indicator active w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold">
                        1
                    </div>
                    <div class="w-8 h-0.5 bg-white/20"></div>
                    <div class="step-indicator w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold text-white/60">
                        2
                    </div>
                    <div class="w-8 h-0.5 bg-white/20"></div>
                    <div class="step-indicator w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold text-white/60">
                        3
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <!-- Bienvenida -->
        <div class="text-center mb-12 animate-fadeIn">
            <h2 class="text-4xl font-bold text-white mb-4">¡Bienvenido a MigApp!</h2>
            <p class="text-xl text-white/80 max-w-2xl mx-auto">
                Vamos a configurar tu primera panadería en pocos minutos. 
                Esta información nos ayudará a personalizar la experiencia para tu negocio.
            </p>
        </div>

        <!-- Formulario de configuración -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white/95 backdrop-blur-sm rounded-3xl p-8 md:p-12 shadow-2xl animate-fadeIn" style="animation-delay: 0.2s;">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-store text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Información de tu Panadería</h3>
                    <p class="text-gray-600">Cuéntanos sobre tu negocio para comenzar</p>
                </div>

                <form method="POST" action="{{ route('branches.store') }}" class="space-y-8">
                    @csrf
                    
                    <!-- Información básica -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Nombre del negocio -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-bread-slice mr-2 text-amber-600"></i>Nombre de tu Panadería *
                            </label>
                            <input id="name" 
                                   type="text" 
                                   name="name" 
                                   required 
                                   class="form-input w-full px-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   placeholder="Ej: Panadería San Miguel">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-phone mr-2 text-amber-600"></i>Teléfono
                            </label>
                            <input id="phone" 
                                   type="tel" 
                                   name="phone" 
                                   class="form-input w-full px-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   placeholder="Ej: +1 234 567 8900">
                        </div>

                        <!-- Email del negocio -->
                        <div>
                            <label for="business_email" class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-envelope mr-2 text-amber-600"></i>Email del Negocio
                            </label>
                            <input id="business_email" 
                                   type="email" 
                                   name="business_email" 
                                   class="form-input w-full px-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   placeholder="contacto@tupanaderia.com">
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label for="direction" class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-map-marker-alt mr-2 text-amber-600"></i>Dirección Completa *
                        </label>
                        <textarea id="direction" 
                                  name="direction" 
                                  required 
                                  rows="3" 
                                  class="form-input w-full px-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
                                  placeholder="Calle, número, colonia, ciudad, código postal"></textarea>
                        @error('direction')
                            <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de panadería -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-4">
                            <i class="fas fa-tags mr-2 text-amber-600"></i>¿Qué tipo de panadería tienes? *
                        </label>
                        <div class="grid md:grid-cols-3 gap-4">
                            <label class="card-setup cursor-pointer">
                                <input type="radio" name="business_type" value="traditional" class="sr-only" required>
                                <div class="p-6 border-2 border-gray-200 rounded-xl text-center hover:border-amber-400 transition-colors">
                                    <i class="fas fa-home text-amber-600 text-3xl mb-3"></i>
                                    <h4 class="font-semibold text-gray-800">Tradicional</h4>
                                    <p class="text-sm text-gray-600 mt-2">Pan artesanal y productos caseros</p>
                                </div>
                            </label>
                            
                            <label class="card-setup cursor-pointer">
                                <input type="radio" name="business_type" value="modern" class="sr-only" required>
                                <div class="p-6 border-2 border-gray-200 rounded-xl text-center hover:border-amber-400 transition-colors">
                                    <i class="fas fa-industry text-amber-600 text-3xl mb-3"></i>
                                    <h4 class="font-semibold text-gray-800">Moderna</h4>
                                    <p class="text-sm text-gray-600 mt-2">Producción semi-industrial</p>
                                </div>
                            </label>
                            
                            <label class="card-setup cursor-pointer">
                                <input type="radio" name="business_type" value="specialized" class="sr-only" required>
                                <div class="p-6 border-2 border-gray-200 rounded-xl text-center hover:border-amber-400 transition-colors">
                                    <i class="fas fa-award text-amber-600 text-3xl mb-3"></i>
                                    <h4 class="font-semibold text-gray-800">Especializada</h4>
                                    <p class="text-sm text-gray-600 mt-2">Productos gourmet o específicos</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Horarios -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="opening_time" class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-clock mr-2 text-amber-600"></i>Hora de Apertura
                            </label>
                            <input id="opening_time" 
                                   type="time" 
                                   name="opening_time" 
                                   class="form-input w-full px-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   value="06:00">
                        </div>

                        <div>
                            <label for="closing_time" class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-clock mr-2 text-amber-600"></i>Hora de Cierre
                            </label>
                            <input id="closing_time" 
                                   type="time" 
                                   name="closing_time" 
                                   class="form-input w-full px-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   value="18:00">
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-amber-50 p-6 rounded-xl">
                        <h4 class="font-semibold text-gray-800 mb-3">
                            <i class="fas fa-lightbulb mr-2 text-amber-600"></i>¿Algo más que debamos saber?
                        </h4>
                        <textarea name="additional_notes" 
                                  rows="3" 
                                  class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
                                  placeholder="Cuéntanos sobre tus productos especiales, objetivos, o cualquier cosa que nos ayude a personalizar mejor tu experiencia..."></textarea>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center pt-6">
                        <button type="button" onclick="window.history.back()" class="px-6 py-3 border-2 border-gray-300 text-gray-600 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </button>
                        
                        <button type="submit" class="px-8 py-4 bg-gradient-to-r from-amber-600 to-orange-600 text-white font-bold rounded-xl hover:from-amber-700 hover:to-orange-700 transition-all transform hover:scale-105 hover:shadow-lg">
                            <i class="fas fa-arrow-right mr-2"></i>Continuar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Beneficios mientras configuras -->
        <div class="max-w-4xl mx-auto mt-12">
            <div class="grid md:grid-cols-3 gap-6 animate-fadeIn" style="animation-delay: 0.4s;">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white text-center">
                    <div class="w-12 h-12 bg-amber-400 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-rocket text-amber-900"></i>
                    </div>
                    <h4 class="font-semibold mb-2">Configuración Rápida</h4>
                    <p class="text-sm text-white/80">Solo tomará unos minutos tener todo listo</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white text-center">
                    <div class="w-12 h-12 bg-amber-400 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-amber-900"></i>
                    </div>
                    <h4 class="font-semibold mb-2">Datos Seguros</h4>
                    <p class="text-sm text-white/80">Tu información está protegida y encriptada</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white text-center">
                    <div class="w-12 h-12 bg-amber-400 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-headset text-amber-900"></i>
                    </div>
                    <h4 class="font-semibold mb-2">Soporte 24/7</h4>
                    <p class="text-sm text-white/80">Estamos aquí para ayudarte cuando lo necesites</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Manejar selección de tipo de panadería
        document.querySelectorAll('input[name="business_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Remover selección anterior
                document.querySelectorAll('input[name="business_type"]').forEach(r => {
                    r.parentElement.querySelector('div').classList.remove('border-amber-500', 'bg-amber-50');
                    r.parentElement.querySelector('div').classList.add('border-gray-200');
                });
                
                // Agregar selección actual
                if (this.checked) {
                    const div = this.parentElement.querySelector('div');
                    div.classList.remove('border-gray-200');
                    div.classList.add('border-amber-500', 'bg-amber-50');
                }
            });
        });

        // Validación en tiempo real
        document.getElementById('name').addEventListener('input', function() {
            if (this.value.length < 2) {
                this.classList.add('border-red-300');
                this.classList.remove('border-green-300');
            } else {
                this.classList.add('border-green-300');
                this.classList.remove('border-red-300');
            }
        });
    </script>
</body>
</html>
