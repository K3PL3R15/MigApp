<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BranchAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario tenga acceso a la sucursal especificada
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Obtener ID de sucursal de la ruta o parámetros
        $branchId = $request->route('branch') 
            ? $request->route('branch')->id_branch 
            : $request->input('id_branch');

        if ($branchId) {
            $canAccess = false;

            switch ($user->role) {
                case 'owner':
                    // Owner puede acceder a cualquier sucursal que posea
                    $canAccess = Branch::where('id_branch', $branchId)
                        ->where('id_user', $user->id)
                        ->exists();
                    break;
                    
                case 'manager':
                case 'employee':
                    // Manager y employee solo pueden acceder a su sucursal asignada
                    $canAccess = $user->id_branch == $branchId;
                    break;
            }

            if (!$canAccess) {
                abort(403, 'Acceso no autorizado a esta sucursal.');
            }
        }

        return $next($request);
    }
}
