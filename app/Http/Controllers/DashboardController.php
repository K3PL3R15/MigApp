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
                'route' => 'inventories.index',
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
            'branches' => [
                'name' => 'Sucursales',
                'description' => 'Gestión de sucursales',
                'icon' => 'fas fa-store',
                'color' => 'indigo',
                'route' => 'branches.index',
                'roles' => ['owner', 'manager']
            ],
            'transfers' => [
                'name' => 'Transferencias',
                'description' => 'Gestiona transferencias entre sucursales',
                'icon' => 'fas fa-exchange-alt',
                'color' => 'amber',
                'route' => 'transfers.index',
                'roles' => ['owner', 'manager']
            ],
            'users' => [
                'name' => 'Personal',
                'description' => 'Gestión de usuarios y empleados',
                'icon' => 'fas fa-users',
                'color' => 'pink',
                'route' => 'users.index',
                'roles' => ['owner', 'manager']
            ],

        ];
        
        // Filtrar módulos según el rol del usuario
        return array_filter($allModules, function($module) use ($role) {
            return in_array($role, $module['roles']);
        });
    }
}
