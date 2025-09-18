<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Branch;
use App\Models\Product;
use App\Mail\InvoiceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SaleController extends Controller implements HasMiddleware
{
    /**
     * Definición de middlewares para este controlador
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:owner,manager,employee', only: ['index', 'show', 'create', 'store']),
            new Middleware('role:owner,manager', only: ['edit', 'update', 'destroy']),
        ];
    }

    /**
     * Listar todas las ventas según jerarquía
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Construir query según rol
        $query = Sale::with(['user', 'branch', 'products']);
        
        switch ($user->role) {
            case 'owner':
                // Owner ve ventas de todas sus sucursales
                $branchIds = Branch::where('id_user', $user->id)->pluck('id_branch');
                $query->whereIn('id_branch', $branchIds);
                break;
                
            case 'manager':
            case 'employee':
                // Manager y employee solo ven ventas de su sucursal
                $query->where('id_branch', $user->id_branch);
                break;
        }
        
        // Filtros adicionales
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        
        if ($request->has('branch_id') && $request->branch_id) {
            $query->where('id_branch', $request->branch_id);
        }
        
        $sales = $query->orderBy('created_at', 'desc')->get();
        
        // Obtener sucursales disponibles para filtros
        $availableBranches = match ($user->role) {
            'owner' => Branch::where('id_user', $user->id)->get(['id_branch', 'name']),
            'manager', 'employee' => Branch::where('id_branch', $user->id_branch)->get(['id_branch', 'name']),
            default => collect()
        };
        
        // Estadísticas para panadería
        $stats = [
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('total'),
            'average_sale' => $sales->count() > 0 ? $sales->avg('total') : 0,
            'today_sales' => $sales->filter(fn($sale) => $sale->date->isToday())->count(),
            'today_amount' => $sales->filter(fn($sale) => $sale->date->isToday())->sum('total'),
        ];

        // Si es una petición AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'sales' => $sales,
                'stats' => $stats,
                'can_create' => true // Todos los roles pueden crear ventas
            ]);
        }
        
        // Obtener productos para el carrito de ventas
        $products = Product::whereHas('inventory.branch', function($query) use ($user) {
            switch ($user->role) {
                case 'owner':
                    $query->where('id_user', $user->id);
                    break;
                case 'manager':
                case 'employee':
                    $query->where('id_branch', $user->id_branch);
                    break;
            }
        })
        ->where('stock', '>', 0)
        ->with(['inventory.branch'])
        ->get();
        
        return view('sales.index', compact('sales', 'availableBranches', 'products', 'stats'));
    }

    /**
     * Mostrar formulario para crear una venta (Modal/AJAX)
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Obtener sucursales según rol para selección
        $branches = match ($user->role) {
            'owner' => Branch::where('id_user', $user->id)->get(['id_branch', 'name']),
            'manager', 'employee' => Branch::where('id_branch', $user->id_branch)->get(['id_branch', 'name']),
            default => collect()
        };
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('sales.partials.create-form', compact('branches'))->render()
            ]);
        }
        
        return view('sales.create', compact('branches'));
    }

    /**
     * Guardar una nueva venta (usando relación many-to-many)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Validación diferente según el tipo de request
        if ($request->ajax()) {
            // Request AJAX desde el carrito
            $request->validate([
                'id_branch' => 'required|exists:branches,id_branch',
                'justify'   => 'nullable|string|max:500',
                'items'     => 'required|array|min:1',
                'items.*.id_product' => 'required|exists:products,id_product',
                'items.*.quantity' => 'required|integer|min:1',
                'customer_email' => 'nullable|email|max:255', // Email temporal, no se guarda
            ], [
                'items.required' => 'Debe agregar al menos un producto a la venta.',
                'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
                'customer_email.email' => 'El email del cliente no es válido.'
            ]);
        } else {
            // Request tradicional (formulario)
            $request->validate([
                'id_branch' => 'required|exists:branches,id_branch',
                'justify'   => 'nullable|string|max:500',
            ]);
        }
        
        // Verificar acceso a la sucursal
        $branch = Branch::findOrFail($request->id_branch);
        $canAccess = match ($user->role) {
            'owner' => $branch->id_user === $user->id,
            'manager', 'employee' => $branch->id_branch === $user->id_branch,
            default => false
        };
        
        if (!$canAccess) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para realizar ventas en esta sucursal.'
                ], 403);
            }
            abort(403, 'No tiene permisos para realizar ventas en esta sucursal.');
        }

        DB::beginTransaction();
        try {
            // Crear la venta
            $sale = Sale::create([
                'date'      => now(),
                'total'     => 0, // Se calculará después
                'id_user'   => $user->id,
                'id_branch' => $request->id_branch,
                'justify'   => $request->justify,
            ]);

            $totalAmount = 0;
            
            // Procesar productos usando la relación many-to-many
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['id_product']);
                    $requestedQuantity = $item['quantity'];
                    
                    // Verificar stock suficiente
                    if ($product->stock < $requestedQuantity) {
                        throw new \Exception("Stock insuficiente para {$product->name}. Disponible: {$product->stock}, Solicitado: {$requestedQuantity}");
                    }
                    
                    $unitPrice = $product->price;
                    $subtotal = $unitPrice * $requestedQuantity;
                    
                    // Usar la relación many-to-many con campos adicionales
                    $sale->products()->attach($product->id_product, [
                        'quantity' => $requestedQuantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Reducir stock del producto
                    $product->decrement('stock', $requestedQuantity);
                    
                    $totalAmount += $subtotal;
                }
            }
            
            // Actualizar el total de la venta
            $sale->update(['total' => $totalAmount]);
            
            DB::commit();
            
            Log::info('Venta de panadería procesada', [
                'sale_id' => $sale->id_sale,
                'branch_id' => $request->id_branch,
                'user_id' => $user->id,
                'total' => $totalAmount,
                'items_count' => count($request->items ?? [])
            ]);
            
            // Enviar factura por email si se proporcionó (sin guardar el email)
            $emailSentMessage = '';
            if ($request->filled('customer_email')) {
                try {
                    $this->sendInvoiceEmail($sale, $request->customer_email);
                    $emailSentMessage = ' Factura enviada a ' . $request->customer_email;
                } catch (\Exception $e) {
                    Log::error('Error enviando factura por email', [
                        'sale_id' => $sale->id_sale,
                        'email' => $request->customer_email,
                        'error' => $e->getMessage()
                    ]);
                    $emailSentMessage = ' (Error al enviar email)';
                }
            }
            
            // Si es petición AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Venta procesada exitosamente.' . $emailSentMessage,
                    'sale' => $sale->load(['products', 'branch', 'user']),
                    'sale_id' => $sale->id_sale
                ]);
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Venta creada exitosamente.' . $emailSentMessage);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error procesando venta de panadería', [
                'user_id' => $user->id,
                'branch_id' => $request->id_branch,
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar la venta: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error al procesar la venta: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar una venta con sus productos (también funciona como factura)
     */
    public function show(Request $request, Sale $sale)
    {
        $this->authorize('view', $sale);
        
        // Cargar productos con información de la tabla pivote
        $sale->load([
            'products' => function($query) {
                $query->withPivot('quantity', 'unit_price', 'subtotal');
            },
            'branch',
            'user'
        ]);
        
        // Generar número de factura
        $invoiceNumber = 'MIG-' . str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT);
        
        // Datos para la factura/show
        $invoiceData = [
            'sale' => $sale,
            'invoiceNumber' => $invoiceNumber,
            'issueDate' => $sale->date->format('d/m/Y H:i'),
            'subtotal' => $sale->products->sum('pivot.subtotal'),
            'total' => $sale->total,
            'bakery' => $sale->branch->name,
            'isEmailView' => $request->has('email') || isset($isEmailView), // Para distinguir vista email
        ];
        
        // Si es una petición AJAX, devolver JSON
        if ($request->ajax() && !$request->has('email')) {
            return response()->json([
                'success' => true,
                'sale' => $sale,
                'invoice_data' => $invoiceData
            ]);
        }
        
        return view('sales.show', $invoiceData);
    }

    /**
     * Mostrar formulario para editar una venta (AJAX/Modal)
     */
    public function edit(Request $request, Sale $sale)
    {
        $this->authorize('update', $sale);
        
        $user = Auth::user();
        
        // Obtener sucursales según rol
        $branches = match ($user->role) {
            'owner' => Branch::where('id_user', $user->id)->get(['id_branch', 'name']),
            'manager' => Branch::where('id_branch', $user->id_branch)->get(['id_branch', 'name']),
            default => collect()
        };
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('sales.partials.edit-form', compact('sale', 'branches'))->render()
            ]);
        }
        
        return view('sales.edit', compact('sale', 'branches'));
    }

    /**
     * Actualizar datos de la venta (no productos)
     */
    public function update(Request $request, Sale $sale)
    {
        $this->authorize('update', $sale);
        
        $user = Auth::user();
        
        // Validación según el rol del usuario
        if ($user->role === 'owner') {
            $request->validate([
                'id_branch' => 'required|exists:branches,id_branch',
                'justify'   => 'nullable|string|max:500',
            ]);
            $updateData = $request->only(['id_branch', 'justify']);
        } else {
            // Managers solo pueden actualizar justificación
            $request->validate([
                'justify' => 'nullable|string|max:500',
            ]);
            $updateData = $request->only(['justify']);
        }

        try {
            $oldData = $sale->toArray();
            $sale->update($updateData);
            
            Log::info('Venta actualizada', [
                'sale_id' => $sale->id_sale,
                'user_id' => $user->id,
                'changes' => array_diff_assoc($updateData, $oldData)
            ]);
            
            // Si es petición AJAX, devolver JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Venta actualizada exitosamente',
                    'sale' => $sale->fresh()->load(['products', 'branch', 'user'])
                ]);
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Venta actualizada exitosamente.');
                
        } catch (\Exception $e) {
            Log::error('Error actualizando venta', [
                'sale_id' => $sale->id_sale,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la venta.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error al actualizar la venta.')
                ->withInput();
        }
    }

    /**
     * Eliminar una venta
     */
    public function destroy(Request $request, Sale $sale)
    {
        $this->authorize('delete', $sale);
        
        try {
            DB::beginTransaction();
            
            // Restaurar stock de productos
            foreach ($sale->products as $product) {
                $product->increment('stock', $product->pivot->quantity);
            }
            
            $saleInfo = "Venta #{$sale->id_sale} por $" . number_format($sale->total, 2);
            $sale->delete();
            
            DB::commit();
            
            Log::info('Venta eliminada', [
                'sale_info' => $saleInfo,
                'user_id' => Auth::id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $saleInfo . ' eliminada correctamente.'
                ]);
            }
            
            return redirect()->route('sales.index')
                ->with('success', $saleInfo . ' eliminada correctamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error eliminando venta', [
                'sale_id' => $sale->id_sale,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la venta.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error al eliminar la venta.');
        }
    }
    
    /**
     * Enviar factura por email (método público para AJAX)
     */
    public function emailInvoice(Request $request, Sale $sale)
    {
        $this->authorize('view', $sale);
        
        $request->validate([
            'email' => 'required|email|max:255'
        ], [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no es válido.'
        ]);
        
        try {
            $this->sendInvoiceEmail($sale, $request->email);
            
            return response()->json([
                'success' => true,
                'message' => 'Factura enviada exitosamente a ' . $request->email
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error enviando factura por email', [
                'sale_id' => $sale->id_sale,
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la factura: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Enviar factura por email al cliente (email temporal, no se guarda)
     */
    private function sendInvoiceEmail(Sale $sale, string $customerEmail)
    {
        // Cargar relaciones necesarias
        $sale->load(['products', 'branch', 'user']);
        
        // Generar número de factura formateado
        $invoiceNumber = 'MIG-' . str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT);
        
        // Crear y enviar el email usando la misma vista del show
        Mail::to($customerEmail)->send(new InvoiceMail($sale, $invoiceNumber));
        
        // Log del envío exitoso (sin guardar el email)
        Log::info("Factura #{$invoiceNumber} enviada por email", [
            'sale_id' => $sale->id_sale,
            'bakery' => $sale->branch->name
        ]);
    }
}
