<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\RequestTransfer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResourceOwnershipMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario tenga ownership del recurso solicitado
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $route = $request->route();
        
        // Verificar ownership según el tipo de recurso en la ruta
        if ($route->hasParameter('branch')) {
            $this->checkBranchOwnership($user, $route->parameter('branch'));
        }
        
        if ($route->hasParameter('inventory')) {
            $this->checkInventoryOwnership($user, $route->parameter('inventory'));
        }
        
        if ($route->hasParameter('product')) {
            $this->checkProductOwnership($user, $route->parameter('product'));
        }
        
        if ($route->hasParameter('sale')) {
            $this->checkSaleOwnership($user, $route->parameter('sale'));
        }
        
        if ($route->hasParameter('requestTransfer')) {
            $this->checkTransferOwnership($user, $route->parameter('requestTransfer'));
        }

        return $next($request);
    }
    
    private function checkBranchOwnership($user, Branch $branch)
    {
        switch ($user->role) {
            case 'owner':
                if ($branch->id_user !== $user->id) {
                    abort(403, 'Acceso no autorizado a esta sucursal.');
                }
                break;
                
            case 'manager':
            case 'employee':
                if ($branch->id_branch !== $user->id_branch) {
                    abort(403, 'Acceso no autorizado a esta sucursal.');
                }
                break;
        }
    }
    
    private function checkInventoryOwnership($user, Inventory $inventory)
    {
        $branch = $inventory->branch;
        
        switch ($user->role) {
            case 'owner':
                if ($branch->id_user !== $user->id) {
                    abort(403, 'Acceso no autorizado a este inventario.');
                }
                break;
                
            case 'manager':
            case 'employee':
                if ($branch->id_branch !== $user->id_branch) {
                    abort(403, 'Acceso no autorizado a este inventario.');
                }
                break;
        }
    }
    
    private function checkProductOwnership($user, Product $product)
    {
        $branch = $product->inventory->branch;
        
        switch ($user->role) {
            case 'owner':
                if ($branch->id_user !== $user->id) {
                    abort(403, 'Acceso no autorizado a este producto.');
                }
                break;
                
            case 'manager':
            case 'employee':
                if ($branch->id_branch !== $user->id_branch) {
                    abort(403, 'Acceso no autorizado a este producto.');
                }
                break;
        }
    }
    
    private function checkSaleOwnership($user, Sale $sale)
    {
        $branch = $sale->branch;
        
        switch ($user->role) {
            case 'owner':
                if ($branch->id_user !== $user->id) {
                    abort(403, 'Acceso no autorizado a esta venta.');
                }
                break;
                
            case 'manager':
            case 'employee':
                if ($branch->id_branch !== $user->id_branch) {
                    abort(403, 'Acceso no autorizado a esta venta.');
                }
                break;
        }
    }
    
    private function checkTransferOwnership($user, RequestTransfer $transfer)
    {
        $canAccess = false;
        
        switch ($user->role) {
            case 'owner':
                // Owner puede ver transferencias de/hacia sus sucursales
                $canAccess = $transfer->originBranch->id_user === $user->id ||
                           $transfer->destinyBranch->id_user === $user->id;
                break;
                
            case 'manager':
                // Manager puede ver transferencias de/hacia su sucursal
                $canAccess = $transfer->id_origin_branch === $user->id_branch ||
                           $transfer->id_destiny_branch === $user->id_branch;
                break;
                
            case 'employee':
                // Employee no puede ver transferencias
                $canAccess = false;
                break;
        }
        
        if (!$canAccess) {
            abort(403, 'Acceso no autorizado a esta transferencia.');
        }
    }
}
