<?php

namespace App\Http\Controllers;

use App\Models\RequestTransfer;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class RequestTransferController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;
    
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:owner,manager', only: ['index', 'show', 'create', 'store', 'approve', 'reject', 'complete']),
        ];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = RequestTransfer::with(['product', 'originBranch', 'destinyBranch']);
        
        switch ($user->role) {
            case 'owner':
                $branchIds = Branch::where('id_user', $user->id)->pluck('id_branch');
                $query->where(function($q) use ($branchIds) {
                    $q->whereIn('id_origin_branch', $branchIds)
                      ->orWhereIn('id_destiny_branch', $branchIds);
                });
                break;
            case 'manager':
                $query->where(function($q) use ($user) {
                    $q->where('id_origin_branch', $user->id_branch)
                      ->orWhere('id_destiny_branch', $user->id_branch);
                });
                break;
        }
        
        if ($request->has('state') && $request->state) {
            $query->where('state', $request->state);
        }
        
        $transfers = $query->orderBy('created_at', 'desc')->get();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'transfers' => $transfers,
                'can_create' => true
            ]);
        }
        
        return view('transfers.index', compact('transfers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_product' => 'required|exists:products,id_product',
            'id_origin_branch' => 'required|exists:branches,id_branch',
            'id_destiny_branch' => 'required|exists:branches,id_branch|different:id_origin_branch',
            'quantity_products' => 'required|integer|min:1',
        ], [
            'id_destiny_branch.different' => 'La sucursal de destino debe ser diferente a la de origen.',
            'quantity_products.min' => 'La cantidad debe ser mayor a 0.'
        ]);

        try {
            DB::beginTransaction();
            
            $transfer = RequestTransfer::create([
                'id_product' => $request->id_product,
                'id_origin_branch' => $request->id_origin_branch,
                'id_destiny_branch' => $request->id_destiny_branch,
                'quantity_products' => $request->quantity_products,
                'state' => RequestTransfer::STATE_PENDING,
                'date_request' => now(),
            ]);
            
            DB::commit();
            
            Log::info('Solicitud de transferencia creada', [
                'transfer_id' => $transfer->id_request,
                'user_id' => Auth::id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud de transferencia creada exitosamente.',
                    'transfer' => $transfer->load(['product', 'originBranch', 'destinyBranch'])
                ]);
            }
            
            return redirect()->route('transfers.index')
                ->with('success', 'Solicitud creada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error creando transferencia', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la solicitud.'
                ], 422);
            }
            
            return redirect()->back()->with('error', 'Error al crear la solicitud.');
        }
    }

    public function approve(Request $request, RequestTransfer $transfer)
    {
        $user = Auth::user();
        
        // Verificación de autorización directa
        $canApprove = false;
        
        if ($user->isOwner()) {
            // Owner puede aprobar transferencias de sus sucursales
            $userBranchIds = Branch::where('id_user', $user->id)->pluck('id_branch')->toArray();
            $canApprove = in_array($transfer->id_origin_branch, $userBranchIds) || in_array($transfer->id_destiny_branch, $userBranchIds);
        } elseif ($user->role === 'manager') {
            // Manager puede aprobar transferencias donde su sucursal esté involucrada
            $canApprove = $transfer->id_origin_branch === $user->id_branch || $transfer->id_destiny_branch === $user->id_branch;
        }
        
        if (!$canApprove) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes autorización para aprobar esta transferencia.'
            ], 403);
        }
        
        try {
            $transfer->approve();
            
            Log::info('Transferencia aprobada', [
                'transfer_id' => $transfer->id_request,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Transferencia aprobada exitosamente.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar la transferencia.'
            ], 422);
        }
    }
    
    public function reject(Request $request, RequestTransfer $transfer)
    {
        $user = Auth::user();
        
        // Verificación de autorización directa
        $canReject = false;
        
        if ($user->isOwner()) {
            // Owner puede rechazar transferencias de sus sucursales
            $userBranchIds = Branch::where('id_user', $user->id)->pluck('id_branch')->toArray();
            $canReject = in_array($transfer->id_origin_branch, $userBranchIds) || in_array($transfer->id_destiny_branch, $userBranchIds);
        } elseif ($user->role === 'manager') {
            // Manager puede rechazar transferencias donde su sucursal esté involucrada
            $canReject = $transfer->id_origin_branch === $user->id_branch || $transfer->id_destiny_branch === $user->id_branch;
        }
        
        if (!$canReject) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes autorización para rechazar esta transferencia.'
            ], 403);
        }
        
        try {
            $transfer->reject();
            
            Log::info('Transferencia rechazada', [
                'transfer_id' => $transfer->id_request,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Transferencia rechazada.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar la transferencia.'
            ], 422);
        }
    }
    
    public function complete(Request $request, RequestTransfer $transfer)
    {
        $user = Auth::user();
        
        // Verificación de autorización directa
        $canComplete = false;
        
        if ($user->isOwner()) {
            // Owner puede completar transferencias de sus sucursales
            $userBranchIds = Branch::where('id_user', $user->id)->pluck('id_branch')->toArray();
            $canComplete = in_array($transfer->id_origin_branch, $userBranchIds) || in_array($transfer->id_destiny_branch, $userBranchIds);
        } elseif ($user->role === 'manager') {
            // Manager puede completar transferencias donde su sucursal esté involucrada
            $canComplete = $transfer->id_origin_branch === $user->id_branch || $transfer->id_destiny_branch === $user->id_branch;
        }
        
        if (!$canComplete) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes autorización para completar esta transferencia.'
            ], 403);
        }
        
        try {
            $transfer->complete();
            
            Log::info('Transferencia completada', [
                'transfer_id' => $transfer->id_request,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Transferencia completada exitosamente.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 422);
        }
    }
    
    public function destroy(Request $request, RequestTransfer $transfer)
    {
        $user = Auth::user();
        
        // Verificación de autorización directa
        $canDelete = false;
        
        if ($user->isOwner()) {
            // Owner puede eliminar transferencias de sus sucursales
            $userBranchIds = Branch::where('id_user', $user->id)->pluck('id_branch')->toArray();
            $canDelete = in_array($transfer->id_origin_branch, $userBranchIds) || in_array($transfer->id_destiny_branch, $userBranchIds);
        } elseif ($user->role === 'manager') {
            // Manager puede eliminar transferencias donde su sucursal esté involucrada
            $canDelete = $transfer->id_origin_branch === $user->id_branch || $transfer->id_destiny_branch === $user->id_branch;
        }
        
        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes autorización para eliminar esta transferencia.'
            ], 403);
        }
        
        if ($transfer->state !== RequestTransfer::STATE_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar transferencias pendientes.'
            ], 422);
        }
        
        try {
            $transfer->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Solicitud eliminada correctamente.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la solicitud.'
            ], 422);
        }
    }
    
    /**
     * Buscar productos disponibles para transferencia
     */
    public function searchProducts(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search', '');
        $destinationBranchId = $request->get('destination_branch_id');
        
        if (empty($search)) {
            return response()->json([
                'success' => true,
                'products' => []
            ]);
        }
        
        // Obtener sucursales accesibles según el rol
        $accessibleBranchIds = [];
        
        switch ($user->role) {
            case 'owner':
                $accessibleBranchIds = Branch::where('id_user', $user->id)->pluck('id_branch')->toArray();
                break;
            case 'manager':
                // Manager puede buscar en sucursales del mismo owner, excluyendo la suya
                $ownerBranches = Branch::where('id_user', $user->branch->id_user)
                    ->where('id_branch', '!=', $user->id_branch)
                    ->pluck('id_branch')
                    ->toArray();
                $accessibleBranchIds = $ownerBranches;
                break;
        }
        
        if (empty($accessibleBranchIds)) {
            return response()->json([
                'success' => true,
                'products' => []
            ]);
        }
        
        // Búsqueda fuzzy de productos
        $query = Product::with(['inventory.branch'])
            ->whereHas('inventory', function($q) use ($accessibleBranchIds) {
                $q->whereIn('id_branch', $accessibleBranchIds);
            })
            ->where('stock', '>', 0);
            
        // Excluir sucursal de destino si se especifica
        if ($destinationBranchId) {
            $query->whereHas('inventory', function($q) use ($destinationBranchId) {
                $q->where('id_branch', '!=', $destinationBranchId);
            });
        }
        
        // Búsqueda por nombre con LIKE para simular fuzzy search
        $searchTerms = explode(' ', $search);
        foreach ($searchTerms as $term) {
            if (strlen($term) >= 2) {
                $query->where('name', 'LIKE', "%{$term}%");
            }
        }
        
        $products = $query->limit(20)
            ->get()
            ->map(function($product) {
                return [
                    'id_product' => $product->id_product,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'min_stock' => $product->min_stock,
                    'price' => $product->price,
                    'formatted_price' => '$' . number_format($product->price, 2),
                    'inventory_name' => $product->inventory->name,
                    'inventory_type' => $product->inventory->type,
                    'inventory_type_name' => $product->inventory->type === 'sale_product' ? 'Productos de Venta' : 'Materias Primas',
                    'branch_id' => $product->inventory->branch->id_branch,
                    'branch_name' => $product->inventory->branch->name,
                    'branch_is_main' => $product->inventory->branch->is_main,
                    'is_low_stock' => $product->stock <= $product->min_stock,
                    'lote' => $product->lote ? $product->lote->format('d/m/Y') : null,
                    'expiration_date' => $product->expiration_date ? $product->expiration_date->format('d/m/Y') : null,
                    'is_expiring' => $product->is_expiring,
                    'is_expired' => $product->is_expired
                ];
            });
        
        return response()->json([
            'success' => true,
            'products' => $products,
            'total' => $products->count()
        ]);
    }
    
    /**
     * Obtener sucursales de destino disponibles
     */
    public function getDestinationBranches(Request $request)
    {
        $user = Auth::user();
        $branches = [];
        
        switch ($user->role) {
            case 'owner':
                $branches = Branch::where('id_user', $user->id)
                    ->orderBy('is_main', 'desc')
                    ->orderBy('name')
                    ->get(['id_branch', 'name', 'is_main'])
                    ->map(function($branch) {
                        return [
                            'id' => $branch->id_branch,
                            'name' => $branch->name,
                            'is_main' => $branch->is_main,
                            'display_name' => $branch->name . ($branch->is_main ? ' (Principal)' : '')
                        ];
                    });
                break;
            case 'manager':
                $branch = $user->branch;
                $branches = collect([[
                    'id' => $branch->id_branch,
                    'name' => $branch->name,
                    'is_main' => $branch->is_main,
                    'display_name' => $branch->name . ($branch->is_main ? ' (Principal)' : '')
                ]]);
                break;
        }
        
        return response()->json([
            'success' => true,
            'branches' => $branches
        ]);
    }
}
