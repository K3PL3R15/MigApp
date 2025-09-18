<?php

namespace App\Http\Controllers;

use App\Models\RequestTransfer;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RequestTransferController extends Controller implements HasMiddleware
{
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
        $this->authorize('update', $transfer);
        
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
        $this->authorize('update', $transfer);
        
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
        $this->authorize('update', $transfer);
        
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
        $this->authorize('delete', $transfer);
        
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
}
