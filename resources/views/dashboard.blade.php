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
