<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BranchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Solo owners pueden ver todas sus sucursales
        return $user->role === 'owner';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Branch $branch): bool
    {
        switch ($user->role) {
            case 'owner':
                return $branch->id_user === $user->id;
            case 'manager':
            case 'employee':
                return $branch->id_branch === $user->id_branch;
            default:
                return false;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo owners pueden crear sucursales
        return $user->role === 'owner';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Branch $branch): bool
    {
        // Solo el owner de la sucursal puede actualizarla
        return $user->role === 'owner' && $branch->id_user === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Branch $branch): bool
    {
        // Solo el owner puede eliminar, y no puede eliminar la sucursal principal si es la única
        if ($user->role !== 'owner' || $branch->id_user !== $user->id) {
            return false;
        }
        
        // No puede eliminar la sucursal principal si es la única
        if ($branch->is_main && $user->branches()->count() === 1) {
            return false;
        }
        
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Branch $branch): bool
    {
        return $user->role === 'owner' && $branch->id_user === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Branch $branch): bool
    {
        return $user->role === 'owner' && $branch->id_user === $user->id;
    }
    
    /**
     * Determine whether the user can manage inventories of this branch.
     */
    public function manageInventories(User $user, Branch $branch): bool
    {
        return $this->view($user, $branch);
    }
    
    /**
     * Determine whether the user can view transfers related to this branch.
     */
    public function viewTransfers(User $user, Branch $branch): bool
    {
        switch ($user->role) {
            case 'owner':
                return $branch->id_user === $user->id;
            case 'manager':
                return $branch->id_branch === $user->id_branch;
            case 'employee':
                return false; // Employees can't view transfers
            default:
                return false;
        }
    }
}
