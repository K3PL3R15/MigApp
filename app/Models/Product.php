<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_product';

    protected $fillable = [
        'id_inventory',
        'name',
        'lote',
        'stock',
        'expiration_days',
        'min_stock',
        'price',
    ];

    protected $casts = [
        'lote' => 'date',
        'stock' => 'integer',
        'expiration_days' => 'integer',
        'min_stock' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Un producto pertenece a un inventario
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'id_inventory', 'id_inventory');
    }

    /**
     * Relación many-to-many con ventas a través de la tabla pivote sale_product
     */
    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'sale_product', 'id_product', 'id_sale')
            ->withPivot('quantity', 'unit_price', 'subtotal')
            ->withTimestamps();
    }

    /**
     * Transferencias que involucran este producto
     */
    public function requestTransfers(): HasMany
    {
        return $this->hasMany(RequestTransfer::class, 'id_product', 'id_product');
    }

    /**
     * Accessor para obtener la fecha de expiración
     */
    public function getExpirationDateAttribute()
    {
        return $this->lote ? $this->lote->addDays($this->expiration_days) : null;
    }

    /**
     * Accessor para verificar si el producto está próximo a expirar (7 días)
     */
    public function getIsExpiringAttribute()
    {
        if (!$this->expiration_date) return false;
        return $this->expiration_date->diffInDays(now()) <= 7 && $this->expiration_date >= now();
    }

    /**
     * Accessor para verificar si el producto está vencido
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->expiration_date) return false;
        return $this->expiration_date < now();
    }

    /**
     * Accessor para verificar si el stock está bajo el mínimo
     */
    public function getIsLowStockAttribute()
    {
        return $this->stock <= $this->min_stock;
    }

    /**
     * Accessor para obtener el precio formateado
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Scope para productos con stock bajo
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock <= min_stock');
    }

    /**
     * Scope para productos próximos a expirar
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->whereRaw('DATE_ADD(lote, INTERVAL expiration_days DAY) <= DATE_ADD(NOW(), INTERVAL ? DAY)', [$days])
                    ->whereRaw('DATE_ADD(lote, INTERVAL expiration_days DAY) >= NOW()');
    }

    /**
     * Scope para productos vencidos
     */
    public function scopeExpired($query)
    {
        return $query->whereRaw('DATE_ADD(lote, INTERVAL expiration_days DAY) < NOW()');
    }

    /**
     * Scope para productos de un inventario específico
     */
    public function scopeOfInventory($query, $inventoryId)
    {
        return $query->where('id_inventory', $inventoryId);
    }
}
