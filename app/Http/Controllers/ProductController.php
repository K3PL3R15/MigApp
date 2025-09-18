<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Inventory;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;

class ProductController extends Controller implements HasMiddleware
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
            new Middleware('role:owner,manager,employee', only: ['create', 'store', 'edit', 'update']),
            new Middleware('role:owner,manager', only: ['destroy']),
        ];
    }

    /**
     * Listar productos de un inventario específico
     */
    public function index(Request $request, Inventory $inventory)
    {
        $this->authorize('view', $inventory);
        
        // Obtener productos con filtros para panaderías
        $query = $inventory->products()
            ->orderBy('name');
        
        // Filtros específicos para panaderías
        if ($request->has('status')) {
            switch ($request->status) {
                case 'fresh':
                    // Productos frescos (pan, pasteles) - expiran en 1-3 días
                    $query->where('expiration_days', '<=', 3);
                    break;
                case 'expiring':
                    // Próximos a vencer
                    $query->whereRaw('DATE_ADD(lote, INTERVAL expiration_days DAY) <= DATE_ADD(NOW(), INTERVAL 7 DAY)')
                          ->whereRaw('DATE_ADD(lote, INTERVAL expiration_days DAY) >= NOW()');
                    break;
                case 'expired':
                    // Ya vencidos
                    $query->whereRaw('DATE_ADD(lote, INTERVAL expiration_days DAY) < NOW()');
                    break;
                case 'low_stock':
                    // Stock bajo
                    $query->whereRaw('stock <= min_stock');
                    break;
            }
        }
        
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $products = $query->get();
        
        // Análisis especial para panaderías
        $analysis = [
            'total_products' => $products->count(),
            'fresh_products' => $products->filter(fn($p) => $p->expiration_days <= 3)->count(),
            'dry_products' => $products->filter(fn($p) => $p->expiration_days > 30)->count(),
            'expiring_soon' => $products->filter(fn($p) => $p->is_expiring ?? false)->count(),
            'expired' => $products->filter(fn($p) => $p->is_expired ?? false)->count(),
            'low_stock' => $products->filter(fn($p) => $p->is_low_stock)->count(),
            'total_value' => $products->sum(fn($p) => $p->stock * $p->price)
        ];
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'products' => $products,
                'analysis' => $analysis,
                'can_create' => Auth::user()->can('create', Product::class)
            ]);
        }
        
        return view('products.index', compact('inventory', 'products', 'analysis'));
    }

    /**
     * Mostrar formulario para crear un producto (AJAX/Modal)
     */
    public function create(Request $request, Inventory $inventory)
    {
        $this->authorize('view', $inventory);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('products.partials.create-form', compact('inventory'))->render()
            ]);
        }
        
        return view('products.create', compact('inventory'));
    }

    /**
     * Guardar un nuevo producto de panadería
     */
    public function store(StoreProductRequest $request, Inventory $inventory)
    {
        $this->authorize('view', $inventory);
        
        try {
            DB::beginTransaction();
            
            $productData = $request->validated();
            $productData['id_inventory'] = $inventory->id_inventory;
            
            $product = Product::create($productData);
            
            DB::commit();
            
            Log::info('Producto de panadería creado', [
                'product_id' => $product->id_product,
                'inventory_id' => $inventory->id_inventory,
                'branch_id' => $inventory->id_branch,
                'user_id' => auth()->id(),
                'product_type' => $inventory->type
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto "' . $product->name . '" creado correctamente.',
                    'product' => $product->fresh()
                ]);
            }
            
            return redirect()->route('inventories.show', $inventory->id_inventory)
                ->with('success', 'Producto "' . $product->name . '" creado correctamente.');
                           
        } catch (Exception $e) {
            DB::rollback();
            
            Log::error('Error creando producto de panadería', [
                'inventory_id' => $inventory->id_inventory,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el producto: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al crear el producto.');
        }
    }

    /**
     * Mostrar detalles de un producto con información de panadería
     */
    public function show(Request $request, Inventory $inventory, Product $product)
    {
        // Verificar que el producto pertenece al inventario
        if ($product->id_inventory !== $inventory->id_inventory) {
            abort(404, 'Producto no encontrado en este inventario.');
        }
        
        // Verificación básica de acceso por rol
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'employee'])) {
            abort(403, 'No tienes permisos para ver este producto.');
        }
        
        // Información adicional para panaderas
        $bakeryInfo = [
            'expiration_date' => $product->expiration_date,
            'days_until_expiration' => $product->expiration_date ? now()->diffInDays($product->expiration_date, false) : null,
            'is_fresh_product' => $product->expiration_days <= 3,
            'is_dry_ingredient' => $product->expiration_days > 30,
            'stock_status' => $product->is_low_stock ? 'low' : 'normal',
            'total_value' => $product->stock * $product->price,
            'formatted_price' => $product->formatted_price
        ];
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'product' => $product,
                'bakery_info' => $bakeryInfo,
                'can_edit' => in_array(auth()->user()->role, ['owner', 'manager', 'employee']),
                'can_delete' => in_array(auth()->user()->role, ['owner', 'manager'])
            ]);
        }
        
        return view('products.show', compact('inventory', 'product', 'bakeryInfo'));
    }

    /**
     * Mostrar formulario para editar un producto (AJAX/Modal)
     */
    public function edit(Request $request, Inventory $inventory, Product $product)
    {
        // Verificar que el producto pertenece al inventario
        if ($product->id_inventory !== $inventory->id_inventory) {
            abort(404, 'Producto no encontrado en este inventario.');
        }
        
        // Verificación básica de acceso por rol
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'employee'])) {
            abort(403, 'No tienes permisos para editar este producto.');
        }
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('products.partials.edit-form', compact('inventory', 'product'))->render()
            ]);
        }
        
        return view('products.edit', compact('inventory', 'product'));
    }

    /**
     * Actualizar un producto de panadería
     */
    public function update(UpdateProductRequest $request, Inventory $inventory, Product $product)
    {
        // Verificar que el producto pertenece al inventario
        if ($product->id_inventory !== $inventory->id_inventory) {
            abort(404, 'Producto no encontrado en este inventario.');
        }
        
        // Verificación básica de acceso por rol
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'employee'])) {
            abort(403, 'No tienes permisos para editar este producto.');
        }
        
        try {
            DB::beginTransaction();
            
            $oldData = $product->toArray();
            $product->update($request->validated());
            
            DB::commit();
            
            Log::info('Producto de panadería actualizado', [
                'product_id' => $product->id_product,
                'inventory_id' => $inventory->id_inventory,
                'changes' => array_diff_assoc($request->validated(), $oldData),
                'user_id' => auth()->id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto "' . $product->name . '" actualizado correctamente.',
                    'product' => $product->fresh()
                ]);
            }
            
            return redirect()->route('inventories.show', $inventory->id_inventory)
                ->with('success', 'Producto "' . $product->name . '" actualizado correctamente.');
                           
        } catch (Exception $e) {
            DB::rollback();
            
            Log::error('Error actualizando producto de panadería', [
                'product_id' => $product->id_product,
                'inventory_id' => $inventory->id_inventory,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el producto.'
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al actualizar el producto.');
        }
    }

    /**
     * Eliminar un producto de panadería
     */
    public function destroy(Request $request, Inventory $inventory, Product $product)
    {
        // Verificar que el producto pertenece al inventario
        if ($product->id_inventory !== $inventory->id_inventory) {
            abort(404, 'Producto no encontrado en este inventario.');
        }
        
        // Verificación básica de acceso por rol
        if (!in_array(auth()->user()->role, ['owner', 'manager'])) {
            abort(403, 'No tienes permisos para eliminar este producto.');
        }
        
        try {
            DB::beginTransaction();
            
            $productName = $product->name;
            
            // Verificar si el producto tiene ventas asociadas
            if ($product->sales()->count() > 0) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar el producto porque tiene ventas registradas.'
                    ], 422);
                }
                
                return redirect()->back()
                    ->with('error', 'No se puede eliminar el producto porque tiene ventas registradas.');
            }
            
            $product->delete();
            
            DB::commit();
            
            Log::info('Producto de panadería eliminado', [
                'product_name' => $productName,
                'inventory_id' => $inventory->id_inventory,
                'user_id' => auth()->id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto "' . $productName . '" eliminado correctamente.'
                ]);
            }
            
            return redirect()->route('inventories.show', $inventory->id_inventory)
                ->with('success', 'Producto "' . $productName . '" eliminado correctamente.');
                           
        } catch (Exception $e) {
            DB::rollback();
            
            Log::error('Error eliminando producto de panadería', [
                'product_id' => $product->id_product,
                'inventory_id' => $inventory->id_inventory,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el producto.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Ocurrió un error al eliminar el producto.');
        }
    }
    
    /**
     * Ajustar stock de un producto (para panaderías con producción diaria)
     */
    public function adjustStock(Request $request, Inventory $inventory, Product $product)
    {
        $this->authorize('update', $product);
        
        $request->validate([
            'adjustment' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255'
        ], [
            'adjustment.required' => 'El ajuste de stock es obligatorio.',
            'adjustment.not_in' => 'El ajuste no puede ser cero.',
            'reason.required' => 'La razón del ajuste es obligatoria.'
        ]);
        
        try {
            DB::beginTransaction();
            
            $oldStock = $product->stock;
            $newStock = $oldStock + $request->adjustment;
            
            if ($newStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El ajuste resultaría en stock negativo.'
                ], 422);
            }
            
            $product->update(['stock' => $newStock]);
            
            DB::commit();
            
            Log::info('Ajuste de stock realizado', [
                'product_id' => $product->id_product,
                'old_stock' => $oldStock,
                'adjustment' => $request->adjustment,
                'new_stock' => $newStock,
                'reason' => $request->reason,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Stock ajustado correctamente.',
                'old_stock' => $oldStock,
                'new_stock' => $newStock
            ]);
            
        } catch (Exception $e) {
            DB::rollback();
            
            Log::error('Error ajustando stock', [
                'product_id' => $product->id_product,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al ajustar el stock.'
            ], 422);
        }
    }
    
    /**
     * Reporte de productos para panaderías
     */
    public function bakeryReport(Request $request, Inventory $inventory)
    {
        $this->authorize('view', $inventory);
        
        $products = $inventory->products;
        
        $report = [
            'fresh_products' => [
                'items' => $products->filter(fn($p) => $p->expiration_days <= 3),
                'total_value' => $products->filter(fn($p) => $p->expiration_days <= 3)->sum(fn($p) => $p->stock * $p->price)
            ],
            'expiring_today' => [
                'items' => $products->filter(function($p) {
                    return $p->expiration_date && $p->expiration_date->isToday();
                }),
            ],
            'expired_items' => [
                'items' => $products->filter(fn($p) => $p->is_expired ?? false)
            ],
            'low_stock_items' => [
                'items' => $products->filter(fn($p) => $p->is_low_stock)
            ],
            'production_needed' => [
                'items' => $products->filter(function($p) {
                    return $p->expiration_days <= 1 && $p->stock <= $p->min_stock;
                })
            ]
        ];
        
        return response()->json([
            'success' => true,
            'report' => $report,
            'generated_at' => now()->format('d/m/Y H:i')
        ]);
    }
}
