<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los roles autenticados pueden ver la lista de ventas
        return in_array($user->role, ['owner', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sale $sale): bool
    {
        // Verificar que el usuario tenga acceso a la sucursal de la venta
        return match ($user->role) {
            'owner' => $sale->branch->id_user === $user->id,
            'manager', 'employee' => $sale->id_branch === $user->id_branch,
            default => false
        };
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos los roles pueden crear ventas
        return in_array($user->role, ['owner', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sale $sale): bool
    {
        // Solo owner y manager pueden editar ventas, y deben tener acceso a la sucursal
        if (!in_array($user->role, ['owner', 'manager'])) {
            return false;
        }
        
        return match ($user->role) {
            'owner' => $sale->branch->id_user === $user->id,
            'manager' => $sale->id_branch === $user->id_branch,
            default => false
        };
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sale $sale): bool
    {
        // Solo owner y manager pueden eliminar ventas, y deben tener acceso a la sucursal
        if (!in_array($user->role, ['owner', 'manager'])) {
            return false;
        }
        
        return match ($user->role) {
            'owner' => $sale->branch->id_user === $user->id,
            'manager' => $sale->id_branch === $user->id_branch,
            default => false
        };
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sale $sale): bool
    {
        // Solo el owner puede restaurar ventas eliminadas
        return $user->role === 'owner' && $sale->branch->id_user === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sale $sale): bool
    {
        // Solo el owner puede eliminar permanentemente
        return $user->role === 'owner' && $sale->branch->id_user === $user->id;
    }
}
