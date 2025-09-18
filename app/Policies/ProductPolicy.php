<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los roles autenticados pueden ver la lista de productos
        return in_array($user->role, ['owner', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Verificar que el usuario pertenezca a la misma empresa que el producto
        $productBranchId = $product->inventory->id_branch;
        $userBranchIds = $user->branches->pluck('id_branch')->toArray();
        
        return in_array($user->role, ['owner', 'manager', 'employee']) && 
               (in_array($productBranchId, $userBranchIds) || $user->role === 'owner');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Owner, managers y employees pueden crear productos
        return in_array($user->role, ['owner', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        // Verificar que el usuario pertenezca a la misma empresa que el producto
        $productBranchId = $product->inventory->id_branch;
        $userBranchIds = $user->branches->pluck('id_branch')->toArray();
        
        return in_array($user->role, ['owner', 'manager', 'employee']) && 
               (in_array($productBranchId, $userBranchIds) || $user->role === 'owner');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Solo owners y managers pueden eliminar productos
        if (!in_array($user->role, ['owner', 'manager'])) {
            return false;
        }
        
        // Verificar que el usuario pertenezca a la misma empresa que el producto
        $productBranchId = $product->inventory->id_branch;
        $userBranchIds = $user->branches->pluck('id_branch')->toArray();
        
        return in_array($productBranchId, $userBranchIds) || $user->role === 'owner';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $this->delete($user, $product);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Solo owners pueden hacer delete permanente
        return $user->role === 'owner';
    }
}
