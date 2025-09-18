<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bienvenido a MigApp - Sistema de Gestión para Panaderías</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Animaciones personalizadas para MigApp */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        .animate-slideIn {
            animation: slideIn 0.6s ease-out forwards;
        }
        
        /* Gradientes para tema panadería */
        .gradient-warm {
            background: linear-gradient(135deg, #92400E 0%, #D97706 50%, #F59E0B 100%);
        }
        
        .gradient-overlay {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.2));
        }
        
        /* Efectos hover para cards */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        /* Carrusel */
        .carousel-container {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
        }
        
        .carousel-btn {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            transition: all 0.3s ease;
        }
        
        .carousel-btn:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.1);
        }
    </style>
</head>
<body class="gradient-warm min-h-screen">
    <!-- Header con logo y navegación -->
    <header class="relative z-50 bg-white/10 backdrop-blur-sm border-b border-white/20">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center space-x-4">
                <img src="{{ asset('images/Logo_MigApp.png') }}" 
                     alt="MigApp Logo" 
                     class="w-16 h-16 object-contain">
                <div class="text-white">
                    <h1 class="text-2xl font-bold">MigApp</h1>
                    <p class="text-sm text-white/80">Gestión para Panaderías</p>
                </div>
            </div>
            
            <!-- Navegación -->
            @if (Route::has('login'))
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" 
                           class="text-white hover:text-amber-200 transition-colors font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="text-white hover:text-amber-200 transition-colors font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" 
                               class="bg-white text-amber-800 px-4 py-2 rounded-lg font-medium hover:bg-amber-50 transition-colors">
                                Registrarse
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    <!-- Hero Section con Carrusel -->
    <main class="container mx-auto px-4 py-12">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Contenido principal -->
            <section class="text-white animate-fadeIn">
                <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Gestiona tu 
                    <span class="text-amber-300">Panadería</span>
                    con Facilidad
                </h1>
                
                <p class="text-xl text-white/90 mb-8 leading-relaxed">
                    Sistema completo de gestión diseñado especialmente para panaderías. 
                    Controla inventarios, ventas, sucursales y más desde una sola plataforma.
                </p>
                
                <!-- Características principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="flex items-center space-x-3 animate-slideIn" style="animation-delay: 0.2s;">
                        <div class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center">
                            <i class="fas fa-boxes text-amber-900 text-sm"></i>
                        </div>
                        <span class="text-white/90">Control de Inventarios</span>
                    </div>
                    
                    <div class="flex items-center space-x-3 animate-slideIn" style="animation-delay: 0.3s;">
                        <div class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center">
                            <i class="fas fa-chart-line text-amber-900 text-sm"></i>
                        </div>
                        <span class="text-white/90">Reportes de Ventas</span>
                    </div>
                    
                    <div class="flex items-center space-x-3 animate-slideIn" style="animation-delay: 0.4s;">
                        <div class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center">
                            <i class="fas fa-store text-amber-900 text-sm"></i>
                        </div>
                        <span class="text-white/90">Múltiples Sucursales</span>
                    </div>
                    
                    <div class="flex items-center space-x-3 animate-slideIn" style="animation-delay: 0.5s;">
                        <div class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-amber-900 text-sm"></i>
                        </div>
                        <span class="text-white/90">Gestión de Personal</span>
                    </div>
                </div>
                
                <!-- Call to action -->
                <div class="flex flex-col sm:flex-row gap-4 animate-fadeIn" style="animation-delay: 0.6s;">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" 
                           class="bg-amber-500 hover:bg-amber-400 text-amber-900 px-8 py-4 rounded-xl font-bold text-lg transition-all transform hover:scale-105 hover:shadow-lg text-center">
                            <i class="fas fa-rocket mr-2"></i>Comenzar Gratis
                        </a>
                    @endif
                    <a href="#features" 
                       class="border-2 border-white text-white px-8 py-4 rounded-xl font-medium text-lg hover:bg-white hover:text-amber-800 transition-all text-center">
                        Ver Características
                    </a>
                </div>
            </section>
            
            <!-- Carrusel de imágenes -->
            <section class="animate-fadeIn" style="animation-delay: 0.3s;">
                <div class="carousel-container relative h-96 lg:h-[500px] rounded-2xl overflow-hidden shadow-2xl">
                    <!-- Slides -->
                    <div id="carousel" class="flex h-full transition-transform duration-500 ease-in-out">
                        <div class="w-full flex-shrink-0 relative">
                            <img src="{{ asset('images/carrusel_1.jpg') }}" 
                                 alt="Panadería moderna" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 gradient-overlay flex items-end">
                                <div class="p-6 text-white">
                                    <h3 class="text-xl font-bold mb-2">Panadería Moderna</h3>
                                    <p class="text-white/90">Administra tu negocio con tecnología de vanguardia</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="w-full flex-shrink-0 relative">
                            <img src="{{ asset('images/carrusel_2.jpg') }}" 
                                 alt="Productos frescos" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 gradient-overlay flex items-end">
                                <div class="p-6 text-white">
                                    <h3 class="text-xl font-bold mb-2">Productos Frescos</h3>
                                    <p class="text-white/90">Controla la frescura y calidad de tus productos</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="w-full flex-shrink-0 relative">
                            <img src="{{ asset('images/carrusel_3.jpg') }}" 
                                 alt="Gestión eficiente" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 gradient-overlay flex items-end">
                                <div class="p-6 text-white">
                                    <h3 class="text-xl font-bold mb-2">Gestión Eficiente</h3>
                                    <p class="text-white/90">Optimiza tus procesos y aumenta la productividad</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Controles del carrusel -->
                    <button id="prevBtn" class="carousel-btn absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full">
                        <i class="fas fa-chevron-left text-amber-800"></i>
                    </button>
                    <button id="nextBtn" class="carousel-btn absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full">
                        <i class="fas fa-chevron-right text-amber-800"></i>
                    </button>
                    
                    <!-- Indicadores -->
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                        <button class="carousel-indicator w-3 h-3 bg-white rounded-full opacity-50 transition-opacity" data-slide="0"></button>
                        <button class="carousel-indicator w-3 h-3 bg-white rounded-full opacity-50 transition-opacity" data-slide="1"></button>
                        <button class="carousel-indicator w-3 h-3 bg-white rounded-full opacity-50 transition-opacity" data-slide="2"></button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Sección de características -->
    <section id="features" class="py-20 bg-white/5 backdrop-blur-sm mt-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">¿Por qué elegir MigApp?</h2>
                <p class="text-xl text-white/80 max-w-3xl mx-auto">
                    Diseñado específicamente para panaderías, con todas las herramientas que necesitas
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Característica 1 -->
                <div class="card-hover bg-white/10 backdrop-blur-sm p-8 rounded-2xl border border-white/20">
                    <div class="w-16 h-16 bg-amber-400 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                        <i class="fas fa-bread-slice text-amber-900 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4 text-center">Especializado en Panaderías</h3>
                    <p class="text-white/80 text-center">
                        Funcionalidades diseñadas específicamente para el negocio de panadería, 
                        desde control de productos perecederos hasta gestión de recetas.
                    </p>
                </div>
                
                <!-- Característica 2 -->
                <div class="card-hover bg-white/10 backdrop-blur-sm p-8 rounded-2xl border border-white/20">
                    <div class="w-16 h-16 bg-amber-400 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                        <i class="fas fa-mobile-alt text-amber-900 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4 text-center">Fácil de Usar</h3>
                    <p class="text-white/80 text-center">
                        Interfaz intuitiva y moderna que tu equipo podrá usar desde el primer día. 
                        Sin complicaciones técnicas.
                    </p>
                </div>
                
                <!-- Característica 3 -->
                <div class="card-hover bg-white/10 backdrop-blur-sm p-8 rounded-2xl border border-white/20">
                    <div class="w-16 h-16 bg-amber-400 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                        <i class="fas fa-shield-alt text-amber-900 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4 text-center">Seguro y Confiable</h3>
                    <p class="text-white/80 text-center">
                        Tus datos están protegidos con los más altos estándares de seguridad. 
                        Acceso controlado por roles.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black/20 backdrop-blur-sm py-8 mt-20">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <img src="{{ asset('images/Logo_MigApp.png') }}" 
                     alt="MigApp Logo" 
                     class="w-8 h-8 object-contain">
                <span class="text-white font-bold text-lg">MigApp</span>
            </div>
            <p class="text-white/60">
                © 2025 MigApp. Sistema de gestión para panaderías.
            </p>
        </div>
    </footer>

    <!-- JavaScript del carrusel -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('carousel');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const indicators = document.querySelectorAll('.carousel-indicator');
            
            let currentSlide = 0;
            const totalSlides = 3;
            
            function updateCarousel() {
                const translateX = -currentSlide * 100;
                carousel.style.transform = `translateX(${translateX}%)`;
                
                // Actualizar indicadores
                indicators.forEach((indicator, index) => {
                    indicator.classList.toggle('opacity-100', index === currentSlide);
                    indicator.classList.toggle('opacity-50', index !== currentSlide);
                });
            }
            
            function nextSlide() {
                currentSlide = (currentSlide + 1) % totalSlides;
                updateCarousel();
            }
            
            function prevSlide() {
                currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
                updateCarousel();
            }
            
            // Event listeners
            nextBtn.addEventListener('click', nextSlide);
            prevBtn.addEventListener('click', prevSlide);
            
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    currentSlide = index;
                    updateCarousel();
                });
            });
            
            // Auto-play
            setInterval(nextSlide, 5000);
            
            // Inicializar
            updateCarousel();
            
            // Smooth scrolling para enlaces de anclaje
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
