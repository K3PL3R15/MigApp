@extends('layouts.migapp')

@section('title', 'Dashboard - MigApp')

@section('content')
    <!-- Tarjetas de módulos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($modules as $moduleKey => $module)
            <x-migapp.module-card 
                :module-key="$moduleKey"
                :name="$module['name']"
                :description="$module['description']"
                :icon="$module['icon']"
                :color="$module['color']"
                :route="$module['route'] ?? '#'"
                :animation-delay="$loop->index * 0.1" />
        @endforeach
    </div>
@endsection

@push('styles')
<style>
    /* Asegurar que todas las tarjetas tengan la misma altura */
    .grid > div {
        height: 100%;
    }
    
    /* Altura mínima uniforme para todas las tarjetas */
    .card-hover {
        min-height: 280px;
    }
    
    /* Animaciones suaves */
    .animate-fadeIn {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease forwards;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Hover effects mejorados */
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
</style>
@endpush
