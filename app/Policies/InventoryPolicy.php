<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InventoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los roles pueden ver la lista de inventarios
        return in_array($user->role, ['owner', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Inventory $inventory): bool
    {
        switch ($user->role) {
            case 'owner':
                // Owner puede ver inventarios de todas sus sucursales
                return $inventory->branch->id_user === $user->id;
                
            case 'manager':
            case 'employee':
                // Manager y employee solo pueden ver inventarios de su sucursal
                return $inventory->id_branch === $user->id_branch;
                
            default:
                return false;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Owner y manager pueden crear inventarios
        return in_array($user->role, ['owner', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Inventory $inventory): bool
    {
        switch ($user->role) {
            case 'owner':
                // Owner puede editar inventarios de todas sus sucursales
                return $inventory->branch->id_user === $user->id;
                
            case 'manager':
                // Manager solo puede editar inventarios de su sucursal
                return $inventory->id_branch === $user->id_branch;
                
            default:
                return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Inventory $inventory): bool
    {
        switch ($user->role) {
            case 'owner':
                // Owner puede eliminar inventarios de todas sus sucursales
                return $inventory->branch->id_user === $user->id;
                
            case 'manager':
                // Manager solo puede eliminar inventarios de su sucursal
                return $inventory->id_branch === $user->id_branch;
                
            default:
                return false;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Inventory $inventory): bool
    {
        // Solo owner puede restaurar
        return $user->role === 'owner' && $inventory->branch->id_user === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Inventory $inventory): bool
    {
        // Solo owner puede eliminar permanentemente
        return $user->role === 'owner' && $inventory->branch->id_user === $user->id;
    }
}
