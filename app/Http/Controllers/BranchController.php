<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BranchController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;
    /**
     * Definición de middlewares para este controlador
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:owner', only: ['create', 'store', 'edit', 'update', 'destroy']),
            new Middleware('role:owner,manager', only: ['index', 'show']),
        ];
    }

    /**
     * Listar todas las sucursales del usuario autenticado
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Filtro según rol
        $branches = match ($user->role) {
            'owner' => Branch::where('id_user', $user->id)
                ->with(['users', 'inventories'])
                ->withCount(['inventories', 'sales'])
                ->get(),
            'manager' => Branch::where('id_branch', $user->id_branch)
                ->with(['users', 'inventories'])
                ->withCount(['inventories', 'sales'])
                ->get(),
            default => collect() // Employee no puede ver sucursales
        };

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'branches' => $branches,
                'can_create' => $user->role === 'owner'
            ]);
        }

        return view('branches.index', compact('branches'));
    }

    /**
     * Mostrar formulario para crear una sucursal (AJAX/Modal)
     */
    public function create(Request $request)
    {
        $this->authorize('create', Branch::class);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('branches.partials.create-form')->render()
            ]);
        }
        
        return view('branches.create');
    }

    /**
     * Guardar una nueva sucursal
     */
    public function store(Request $request)
    {
        $this->authorize('create', Branch::class);
        
        $user = Auth::user();
        
        $request->validate([
            'name'      => 'required|string|max:255',
            'direction' => 'required|string|max:500|unique:branches,direction',
            'phone'     => 'nullable|string|max:20',
        ], [
            'direction.unique' => 'Ya existe una sucursal registrada en esta dirección.'
        ]);

        // Verificar si es la primera sucursal (será principal)
        $isMain = !$user->branches()->exists();

        try {
            DB::beginTransaction();
            
            $branch = Branch::create([
                'name'      => $request->name,
                'direction' => $request->direction,
                'phone'     => $request->phone,
                'id_user'   => $user->id,
                'is_main'   => $isMain,
            ]);

            Log::info('Sucursal creada', [
                'branch_id' => $branch->id_branch,
                'user_id' => $user->id,
                'is_main' => $isMain
            ]);

            DB::commit();

            $message = $isMain 
                ? 'Sucursal principal creada correctamente.'
                : 'Sucursal creada correctamente.';

            // Respuesta AJAX para formularios emergentes
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'branch' => $branch->load(['inventories', 'users']),
                    'redirect' => $isMain && session('just_registered') ? route('dashboard') : null
                ]);
            }

            // Redirección especial si es la primera sucursal después del registro
            if ($isMain && session('just_registered')) {
                session()->forget('just_registered');
                return redirect()->route('dashboard')
                    ->with('success', $message);
            }

            return redirect()->route('branches.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error creando sucursal', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la sucursal: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Error al crear la sucursal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar los detalles de una sucursal con sus inventarios
     */
    public function show(Request $request, Branch $branch)
    {
        $this->authorize('view', $branch);
        
        // Cargar inventarios con productos y contadores
        $branch->load([
            'inventories.products' => function ($query) {
                $query->orderBy('name');
            },
            'users' => function ($query) {
                $query->orderBy('role')->orderBy('name');
            }
        ]);
        
        // Estadísticas de la sucursal
        $stats = [
            'total_inventories' => $branch->inventories->count(),
            'total_products' => $branch->inventories->sum(function($inv) { 
                return $inv->products->count(); 
            }),
            'low_stock_products' => $branch->inventories->sum(function($inv) {
                return $inv->products->filter(function($product) {
                    return $product->stock <= $product->min_stock;
                })->count();
            }),
            'total_users' => $branch->users->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'branch' => $branch,
                'stats' => $stats,
                'can_edit' => Auth::user()->can('update', $branch),
                'can_delete' => Auth::user()->can('delete', $branch)
            ]);
        }

        return view('branches.show', compact('branch', 'stats'));
    }

    /**
     * Mostrar formulario para editar una sucursal (AJAX/Modal)
     */
    public function edit(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('branches.partials.edit-form', compact('branch'))->render()
            ]);
        }
        
        return view('branches.edit', compact('branch'));
    }

    /**
     * Actualizar una sucursal existente
     */
    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);
        
        $request->validate([
            'name'      => 'required|string|max:255',
            'direction' => 'required|string|max:500|unique:branches,direction,' . $branch->id_branch . ',id_branch',
            'phone'     => 'nullable|string|max:20',
        ], [
            'direction.unique' => 'Ya existe otra sucursal registrada en esta dirección.'
        ]);

        try {
            $oldData = $branch->toArray();
            
            $branch->update($request->only(['name', 'direction', 'phone']));
            
            Log::info('Sucursal actualizada', [
                'branch_id' => $branch->id_branch,
                'user_id' => Auth::id(),
                'changes' => array_diff_assoc($request->only(['name', 'direction', 'phone']), $oldData)
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sucursal actualizada correctamente.',
                    'branch' => $branch->fresh()
                ]);
            }

            return redirect()->route('branches.index')
                ->with('success', 'Sucursal actualizada correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error actualizando sucursal', [
                'branch_id' => $branch->id_branch,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la sucursal.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error al actualizar la sucursal.')
                ->withInput();
        }
    }

    /**
     * Eliminar una sucursal
     */
    public function destroy(Request $request, Branch $branch)
    {
        $this->authorize('delete', $branch);
        
        try {
            $branchName = $branch->name;
            $branch->delete();
            
            Log::info('Sucursal eliminada', [
                'branch_name' => $branchName,
                'user_id' => Auth::id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Sucursal '{$branchName}' eliminada correctamente."
                ]);
            }
            
            return redirect()->route('branches.index')
                ->with('success', "Sucursal '{$branchName}' eliminada correctamente.");
                
        } catch (\Exception $e) {
            Log::error('Error eliminando sucursal', [
                'branch_id' => $branch->id_branch,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la sucursal.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error al eliminar la sucursal.');
        }
    }
    
    /**
     * Obtener inventarios de una sucursal (para transferencias)
     */
    public function inventories(Request $request, Branch $branch)
    {
        $this->authorize('view', $branch);
        
        $inventories = $branch->inventories()
            ->with(['products' => function($query) {
                $query->where('stock', '>', 0)
                      ->orderBy('name');
            }])
            ->get();
            
        return response()->json([
            'success' => true,
            'inventories' => $inventories
        ]);
    }
}
