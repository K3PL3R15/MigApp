@extends('layouts.migapp')

@section('title', 'Sucursales - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-store mr-3"></i>Gestión de Sucursales
                </h1>
                <p class="text-white/80 mt-2">
                    Administra todas tus sucursales y ubicaciones
                </p>
            </div>
            
            <x-migapp.button 
                variant="primary" 
                icon="fas fa-plus"
                href="{{ route('branches.create') }}">
                Nueva Sucursal
            </x-migapp.button>
        </div>
        
        <!-- Lista de sucursales -->
        <div class="glass-card rounded-lg p-6">
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-store"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Gestión de Sucursales</h3>
                <p class="text-gray-500 mb-6">Aquí podrás administrar todas tus sucursales</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
                    <!-- Ejemplo de tarjetas de sucursales -->
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-store text-indigo-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Sucursal Principal</h3>
                                    <p class="text-sm text-gray-600">Centro - Activa</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center py-4">
                            <div class="text-2xl font-bold text-gray-800">5</div>
                            <div class="text-xs text-gray-500">Empleados</div>
                        </div>
                        
                        <div class="flex justify-between items-center pt-4 border-t">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Operativa
                            </span>
                            
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </button>
                                <button class="text-amber-600 hover:text-amber-800 text-sm">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tarjeta de ejemplo 2 -->
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-store text-indigo-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Sucursal Norte</h3>
                                    <p class="text-sm text-gray-600">Mall del Norte - Activa</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center py-4">
                            <div class="text-2xl font-bold text-gray-800">3</div>
                            <div class="text-xs text-gray-500">Empleados</div>
                        </div>
                        
                        <div class="flex justify-between items-center pt-4 border-t">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Operativa
                            </span>
                            
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </button>
                                <button class="text-amber-600 hover:text-amber-800 text-sm">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón para agregar nueva sucursal -->
                    <div class="bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 p-6 hover:border-gray-400 transition-colors">
                        <div class="text-center">
                            <div class="text-gray-400 text-4xl mb-4">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">Agregar Sucursal</h3>
                            <p class="text-sm text-gray-500 mb-4">Expande tu negocio</p>
                            
                            <x-migapp.button 
                                variant="outline-primary" 
                                size="sm"
                                href="{{ route('branches.create') }}">
                                Crear Sucursal
                            </x-migapp.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
