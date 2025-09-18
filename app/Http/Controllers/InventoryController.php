<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InventoryController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;
    /**
     * Definición de middlewares para este controlador
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:owner,manager,employee', only: ['index', 'show']),
            new Middleware('role:owner,manager', only: ['create', 'store', 'edit', 'update', 'destroy']),
        ];
    }

    /**
     * Listar todos los inventarios según jerarquía
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Construir query según rol
        $query = Inventory::with(['branch', 'products' => function($q) {
            $q->select('id_inventory', 'name', 'stock', 'min_stock', 'price')
              ->orderBy('name');
        }]);
        
        switch ($user->role) {
            case 'owner':
                // Owner ve inventarios de todas sus sucursales
                $branchIds = Branch::where('id_user', $user->id)
                    ->pluck('id_branch');
                $query->whereIn('id_branch', $branchIds);
                break;
                
            case 'manager':
            case 'employee':
                // Manager y employee solo ven inventarios de su sucursal
                $query->where('id_branch', $user->id_branch);
                break;
        }
        
        // Filtros adicionales
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('branch_id') && $request->branch_id) {
            $query->where('id_branch', $request->branch_id);
        }
        
        $inventories = $query->withCount('products')
            ->orderBy('name')
            ->get();
        
        // Obtener sucursales disponibles para filtros
        $availableBranches = match ($user->role) {
            'owner' => Branch::where('id_user', $user->id)->get(['id_branch', 'name']),
            'manager', 'employee' => Branch::where('id_branch', $user->id_branch)->get(['id_branch', 'name']),
            default => collect()
        };
        
        // Estadísticas para panaderías
        $stats = [
            'total_inventories' => $inventories->count(),
            'productos_panaderia' => $inventories->where('type', 'sale_product')->sum('products_count'),
            'materias_primas' => $inventories->where('type', 'raw_material')->sum('products_count'),
            'stock_bajo' => $inventories->sum(function($inv) {
                return $inv->products->filter(function($product) {
                    return $product->stock <= $product->min_stock;
                })->count();
            })
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'inventories' => $inventories,
                'stats' => $stats,
                'can_create' => in_array($user->role, ['owner', 'manager'])
            ]);
        }

        return view('inventories.index', compact('inventories', 'availableBranches', 'stats'));
    }

    /**
     * Mostrar formulario para crear inventario (AJAX/Modal)
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Obtener sucursales según rol
        $branches = match ($user->role) {
            'owner' => Branch::where('id_user', $user->id)->get(['id_branch', 'name', 'unique_code']),
            'manager' => Branch::where('id_branch', $user->id_branch)->get(['id_branch', 'name', 'unique_code']),
            default => collect()
        };
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('inventories.partials.create-form', compact('branches'))->render()
            ]);
        }
        
        return view('inventories.create', compact('branches'));
    }

    /**
     * Mostrar inventario con sus productos (Show = Productos del inventario)
     */
    public function show(Request $request, Inventory $inventory)
    {
        $this->authorize('view', $inventory);
        
        // Si es una petición AJAX (para el modal de vista rápida)
        if ($request->ajax()) {
            // Cargar productos con información de panadería
            $inventory->load([
                'branch',
                'products' => function($query) {
                    $query->orderBy('name');
                }
            ]);
            
            // Clasificación especial para panaderías
            $productsAnalysis = [
                'productos_frescos' => $inventory->products->filter(function($product) {
                    return $product->expiration_days <= 3; // Pan fresco, pasteles, etc.
                }),
                'productos_secos' => $inventory->products->filter(function($product) {
                    return $product->expiration_days > 30; // Harinas, azúcar, etc.
                }),
                'proximos_vencer' => $inventory->products->filter(function($product) {
                    return $product->is_expiring;
                }),
                'stock_critico' => $inventory->products->filter(function($product) {
                    return $product->is_low_stock;
                })
            ];
            
            return response()->json([
                'success' => true,
                'inventory' => $inventory,
                'analysis' => $productsAnalysis,
                'can_edit' => Auth::user()->can('update', $inventory),
                'can_delete' => Auth::user()->can('delete', $inventory)
            ]);
        }

        // Para navegación directa, redirigir al índice de productos del inventario
        return redirect()->route('inventories.products.index', $inventory)
            ->with('info', "Mostrando productos del inventario: {$inventory->name}");
    }

    /**
     * Guardar un nuevo inventario
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:sale_product,raw_material',
            'id_branch' => 'required|exists:branches,id_branch',
        ], [
            'name.required' => 'El nombre del inventario es obligatorio.',
            'type.required' => 'Debe seleccionar el tipo de inventario.',
            'type.in' => 'Tipo de inventario no válido.',
            'id_branch.exists' => 'La sucursal seleccionada no existe.'
        ]);
        
        // Verificar acceso a la sucursal
        $branch = Branch::findOrFail($request->id_branch);
        
        $canAccess = match ($user->role) {
            'owner' => $branch->id_user === $user->id,
            'manager' => $branch->id_branch === $user->id_branch,
            default => false
        };
        
        if (!$canAccess) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para crear inventarios en esta sucursal.'
                ], 403);
            }
            abort(403, 'No tiene permisos para crear inventarios en esta sucursal.');
        }
        
        try {
            DB::beginTransaction();
            
            $inventory = Inventory::create([
                'name' => $request->name,
                'type' => $request->type,
                'id_branch' => $request->id_branch,
            ]);
            
            Log::info('Inventario creado', [
                'inventory_id' => $inventory->id_inventory,
                'branch_id' => $request->id_branch,
                'user_id' => $user->id,
                'type' => $request->type
            ]);
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inventario creado con éxito.',
                    'inventory' => $inventory->load('branch')
                ]);
            }

            return redirect()->route('inventories.index')
                ->with('success', 'Inventario creado con éxito.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error creando inventario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el inventario.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error al crear el inventario.')
                ->withInput();
        }
    }

    /**
     * Mostrar formulario para editar inventario (AJAX/Modal)
     */
    public function edit(Request $request, Inventory $inventory)
    {
        $this->authorize('update', $inventory);
        
        $user = Auth::user();
        
        // Obtener sucursales según rol
        $branches = match ($user->role) {
            'owner' => Branch::where('id_user', $user->id)->get(['id_branch', 'name', 'unique_code']),
            'manager' => Branch::where('id_branch', $user->id_branch)->get(['id_branch', 'name', 'unique_code']),
            default => collect()
        };
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('inventories.partials.edit-form', compact('inventory', 'branches'))->render()
            ]);
        }
        
        return view('inventories.edit', compact('inventory', 'branches'));
    }

    /**
     * Actualizar inventario
     */
    public function update(Request $request, Inventory $inventory)
    {
        $this->authorize('update', $inventory);
        
        $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:sale_product,raw_material',
            'id_branch' => 'required|exists:branches,id_branch',
        ], [
            'name.required' => 'El nombre del inventario es obligatorio.',
            'type.required' => 'Debe seleccionar el tipo de inventario.',
            'type.in' => 'Tipo de inventario no válido.',
            'id_branch.exists' => 'La sucursal seleccionada no existe.'
        ]);
        
        try {
            $oldData = $inventory->toArray();
            
            $inventory->update($request->only(['name', 'type', 'id_branch']));
            
            Log::info('Inventario actualizado', [
                'inventory_id' => $inventory->id_inventory,
                'user_id' => Auth::id(),
                'changes' => array_diff_assoc($request->only(['name', 'type', 'id_branch']), $oldData)
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inventario actualizado con éxito.',
                    'inventory' => $inventory->fresh()->load('branch')
                ]);
            }

            return redirect()->route('inventories.index')
                ->with('success', 'Inventario actualizado con éxito.');
                
        } catch (\Exception $e) {
            Log::error('Error actualizando inventario', [
                'inventory_id' => $inventory->id_inventory,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el inventario.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error al actualizar el inventario.')
                ->withInput();
        }
    }

    /**
     * Eliminar inventario
     */
    public function destroy(Request $request, Inventory $inventory)
    {
        $this->authorize('delete', $inventory);
        
        // Verificar si tiene productos
        if ($inventory->products()->count() > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el inventario porque contiene productos.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'No se puede eliminar el inventario porque contiene productos.');
        }
        
        try {
            $inventoryName = $inventory->name;
            $inventory->delete();
            
            Log::info('Inventario eliminado', [
                'inventory_name' => $inventoryName,
                'user_id' => Auth::id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Inventario '{$inventoryName}' eliminado con éxito."
                ]);
            }

            return redirect()->route('inventories.index')
                ->with('success', "Inventario '{$inventoryName}' eliminado con éxito.");
                
        } catch (\Exception $e) {
            Log::error('Error eliminando inventario', [
                'inventory_id' => $inventory->id_inventory,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el inventario.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error al eliminar el inventario.');
        }
    }
}
