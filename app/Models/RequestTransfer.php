<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestTransfer extends Model
{
    use HasFactory;

    protected $table = 'request_transfers';  // Nombre de la tabla
    protected $primaryKey = 'id_request';    // Clave primaria
    public $incrementing = true;             // Es autoincremental
    protected $keyType = 'int';              // Tipo de la PK

    protected $fillable = [
        'id_product',
        'id_origin_branch',
        'id_destiny_branch',
        'quantity_products', // CORREGIDO: era 'cuantity_products'
        'state',
        'date_request',
    ];

    protected $casts = [
        'date_request' => 'datetime',
        'quantity_products' => 'integer',
        'state' => 'string',
    ];

    /**
     * Constantes para estados de transferencia
     */
    public const STATE_PENDING = 'pending';
    public const STATE_APPROVED = 'approved';
    public const STATE_REJECTED = 'rejected';
    public const STATE_COMPLETED = 'completed';

    public const STATES = [
        self::STATE_PENDING => 'Pendiente',
        self::STATE_APPROVED => 'Aprobada',
        self::STATE_REJECTED => 'Rechazada',
        self::STATE_COMPLETED => 'Completada',
    ];

    /**
     * Relaciones
     */

    // Relación con Producto
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }

    // Relación con sucursal de origen
    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'id_origin_branch', 'id_branch');
    }

    // Relación con sucursal de destino
    public function destinyBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'id_destiny_branch', 'id_branch');
    }

    /**
     * Accessor para obtener el nombre del estado formateado
     */
    public function getStateNameAttribute()
    {
        return self::STATES[$this->state] ?? $this->state;
    }

    /**
     * Accessor para obtener la fecha formateada
     */
    public function getFormattedDateRequestAttribute()
    {
        return $this->date_request->format('d/m/Y H:i');
    }

    /**
     * Método para aprobar la transferencia
     */
    public function approve()
    {
        $this->state = self::STATE_APPROVED;
        $this->save();
    }

    /**
     * Método para rechazar la transferencia
     */
    public function reject()
    {
        $this->state = self::STATE_REJECTED;
        $this->save();
    }

    /**
     * Método para completar la transferencia (transferir stock)
     */
    public function complete()
    {
        if ($this->state !== self::STATE_APPROVED) {
            throw new \Exception('La transferencia debe estar aprobada para completarse');
        }

        // Encontrar el producto en la sucursal de origen
        $originProduct = Product::whereHas('inventory', function ($query) {
            $query->where('id_branch', $this->id_origin_branch);
        })->where('id_product', $this->id_product)->first();

        if (!$originProduct || $originProduct->stock < $this->quantity_products) {
            throw new \Exception('Stock insuficiente en sucursal de origen');
        }

        // Reducir stock en origen
        $originProduct->stock -= $this->quantity_products;
        $originProduct->save();

        // Encontrar o crear producto en sucursal de destino
        // Esto puede requerir lógica adicional dependiendo de cómo manejes los inventarios
        
        $this->state = self::STATE_COMPLETED;
        $this->save();
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('state', self::STATE_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('state', self::STATE_APPROVED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('state', self::STATE_COMPLETED);
    }

    public function scopeOfProduct($query, $productId)
    {
        return $query->where('id_product', $productId);
    }

    public function scopeOfOriginBranch($query, $branchId)
    {
        return $query->where('id_origin_branch', $branchId);
    }

    public function scopeOfDestinyBranch($query, $branchId)
    {
        return $query->where('id_destiny_branch', $branchId);
    }
}
