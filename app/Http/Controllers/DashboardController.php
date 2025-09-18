<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Definir módulos disponibles según el rol
        $modules = $this->getModulesForRole($user->role);
        
        return view('dashboard', compact('user', 'modules'));
    }
    
    private function getModulesForRole($role)
    {
        $allModules = [
            'inventory' => [
                'name' => 'Inventario',
                'description' => 'Gestiona el stock de productos',
                'icon' => 'fas fa-boxes',
                'color' => 'blue',
                'route' => 'inventory.index',
                'roles' => ['owner', 'manager', 'employee']
            ],
            'sales' => [
                'name' => 'Ventas',
                'description' => 'Registro y control de ventas',
                'icon' => 'fas fa-shopping-cart',
                'color' => 'green',
                'route' => 'sales.index',
                'roles' => ['owner', 'manager', 'employee']
            ],
            'products' => [
                'name' => 'Productos',
                'description' => 'Catálogo de productos',
                'icon' => 'fas fa-bread-slice',
                'color' => 'amber',
                'route' => 'products.index',
                'roles' => ['owner', 'manager', 'employee']
            ],
            'reports' => [
                'name' => 'Reportes',
                'description' => 'Análisis y reportes de negocio',
                'icon' => 'fas fa-chart-line',
                'color' => 'purple',
                'route' => 'reports.index',
                'roles' => ['owner', 'manager']
            ],
            'branches' => [
                'name' => 'Sucursales',
                'description' => 'Gestión de sucursales',
                'icon' => 'fas fa-store',
                'color' => 'indigo',
                'route' => 'branches.index',
                'roles' => ['owner']
            ],
            'users' => [
                'name' => 'Personal',
                'description' => 'Gestión de usuarios y empleados',
                'icon' => 'fas fa-users',
                'color' => 'pink',
                'route' => 'users.index',
                'roles' => ['owner', 'manager']
            ],
            'transfers' => [
                'name' => 'Traslados',
                'description' => 'Traslados entre sucursales',
                'icon' => 'fas fa-truck',
                'color' => 'orange',
                'route' => 'transfers.index',
                'roles' => ['owner', 'manager']
            ],
            'profile' => [
                'name' => 'Mi Perfil',
                'description' => 'Configuración personal',
                'icon' => 'fas fa-user-cog',
                'color' => 'gray',
                'route' => 'profile.edit',
                'roles' => ['owner', 'manager', 'employee']
            ]
        ];
        
        // Filtrar módulos según el rol del usuario
        return array_filter($allModules, function($module) use ($role) {
            return in_array($role, $module['roles']);
        });
    }
}
