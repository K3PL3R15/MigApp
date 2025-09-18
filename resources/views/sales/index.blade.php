@extends('layouts.migapp')

@section('title', 'Ventas - MigApp')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header de la sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-shopping-cart mr-3"></i>Gestión de Ventas
                </h1>
                <p class="text-white/80 mt-2">
                    Registra y controla todas las ventas de tu panadería
                </p>
            </div>
            
            <x-migapp.button 
                variant="primary" 
                icon="fas fa-plus"
                onclick="openModal('create-sale')">
                Nueva Venta
            </x-migapp.button>
        </div>
        
        <!-- Estadísticas -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 stats-container">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['total_sales'] }}</div>
                        <div class="text-white/70 text-xs">Total Ventas</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-300">${{ number_format($stats['total_amount'], 2) }}</div>
                        <div class="text-white/70 text-xs">Monto Total</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-300">${{ number_format($stats['average_sale'], 2) }}</div>
                        <div class="text-white/70 text-xs">Promedio</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-day text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-amber-300">{{ $stats['today_sales'] }}</div>
                        <div class="text-white/70 text-xs">Hoy</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-coins text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-emerald-300">${{ number_format($stats['today_amount'], 2) }}</div>
                        <div class="text-white/70 text-xs">Ingresos Hoy</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de ventas -->
        <div class="glass-card rounded-lg p-6">
            @if($sales->isEmpty())
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay ventas registradas</h3>
                    <p class="text-gray-500 mb-6">Registra tu primera venta para comenzar</p>
                    
                    <x-migapp.button 
                        variant="primary" 
                        icon="fas fa-plus">
                        Registrar Venta
                    </x-migapp.button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Venta
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cliente/Empleado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sucursal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-receipt text-green-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    #{{ str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $sale->products->count() }} productos
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sale->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $sale->user->role_name ?? ucfirst($sale->user->role) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sale->date->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">
                                            ${{ number_format($sale->total, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $sale->branch->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('sales.show', $sale) }}" 
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @can('update', $sale)
                                                <a href="{{ route('sales.edit', $sale) }}" 
                                                   class="text-amber-600 hover:text-amber-900">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            
                                            <button onclick="confirmDelete({
                                                modalId: 'delete-sale-{{ $sale->id_sale }}',
                                                title: '¿Eliminar Venta?',
                                                itemName: 'Venta #{{ str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT) }}'
                                            })" 
                                                class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
