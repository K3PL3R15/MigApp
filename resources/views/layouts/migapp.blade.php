<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Title -->
    <title>@yield('title', 'MigApp - Sistema de Gestión')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Estilos personalizados de MigApp -->
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
        
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-primary {
            @apply bg-amber-600 hover:bg-amber-700 text-white font-medium px-6 py-2 rounded-lg transition-colors duration-200;
        }
        
        .btn-secondary {
            @apply bg-gray-600 hover:bg-gray-700 text-white font-medium px-6 py-2 rounded-lg transition-colors duration-200;
        }
        
        .btn-danger {
            @apply bg-red-600 hover:bg-red-700 text-white font-medium px-6 py-2 rounded-lg transition-colors duration-200;
        }
        
        /* Asegurar z-index alto para dropdowns */
        .dropdown-menu {
            z-index: 9999 !important;
        }
        
        /* Mejorar responsive de estadísticas */
        @media (max-width: 768px) {
            .stats-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .stats-item {
                justify-content: center;
            }
        }
    </style>
    
    <!-- Stack adicional para estilos personalizados -->
    @stack('styles')
</head>
<body class="@yield('body-class', 'gradient-warm min-h-screen')">
    <!-- Header -->
    @auth
        <x-migapp.header :user="auth()->user()" />
    @endauth
    
    <!-- Contenido principal -->
    <main class="@yield('main-class', 'container mx-auto px-4 py-8')">
        <!-- Mensajes de estado -->
        @if(session('success'))
            <x-migapp.alert type="success" :message="session('success')" />
        @endif
        
        @if(session('error'))
            <x-migapp.alert type="error" :message="session('error')" />
        @endif
        
        @if(session('warning'))
            <x-migapp.alert type="warning" :message="session('warning')" />
        @endif
        
        @if(session('info'))
            <x-migapp.alert type="info" :message="session('info')" />
        @endif
        
        <!-- Contenido de la página -->
        @yield('content')
    </main>
    
    <!-- Footer -->
    <x-migapp.footer />
    
    <!-- Scripts adicionales -->
    @stack('scripts')
    
    <!-- Script base para funcionalidades comunes -->
    <script>
        // Configuración global para peticiones AJAX
        if (window.axios) {
            window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        }
        
        // Helper function para mostrar mensajes
        window.showMessage = function(message, type = 'info') {
            // Implementar sistema de notificaciones toast aquí
            console.log(`[${type.toUpperCase()}] ${message}`);
        };
        
        // Helper function para confirmar acciones
        window.confirmAction = function(message = '¿Estás seguro?') {
            return confirm(message);
        };
        
        // Funciones globales para controlar modales
        window.openModal = function(id) {
            console.log('openModal called with id:', id);
            window.dispatchEvent(new CustomEvent('open-modal', { detail: { id } }));
        };
        
        window.closeModal = function(id) {
            console.log('closeModal called with id:', id);
            window.dispatchEvent(new CustomEvent('close-modal', { detail: { id } }));
        };
        
        // Debug: Verificar que las funciones están disponibles
        console.log('Modal functions loaded:', { openModal: typeof window.openModal, closeModal: typeof window.closeModal });
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('[data-auto-hide]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>
