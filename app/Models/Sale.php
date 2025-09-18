<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sale extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'sales';

    // Clave primaria
    protected $primaryKey = 'id_sale';

    // Los campos que se pueden asignar en masa
    protected $fillable = [
        'date',
        'total',
        'id_user',
        'id_branch',
        'justify',
    ];

    protected $casts = [
        'date' => 'datetime',
        'total' => 'decimal:2',
    ];

    // Relaciones con otras tablas

    /**
     * Una venta pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Una venta pertenece a una sucursal
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'id_branch', 'id_branch');
    }

    /**
     * CRÍTICO: Relación many-to-many con productos
     * Una venta puede tener muchos productos y un producto puede estar en muchas ventas
     * A través de la tabla pivote 'sale_product' con campos adicionales
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'sale_product', 'id_sale', 'id_product')
            ->withPivot('quantity', 'unit_price', 'subtotal')
            ->withTimestamps();
    }

    /**
     * Accessor para obtener el total formateado
     */
    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->total, 2);
    }

    /**
     * Accessor para obtener la fecha formateada
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('d/m/Y H:i');
    }

    /**
     * Método para agregar un producto a la venta
     */
    public function addProduct($product, $quantity, $unitPrice = null)
    {
        $unitPrice = $unitPrice ?? $product->price;
        $subtotal = $quantity * $unitPrice;
        
        $this->products()->attach($product->id_product, [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ]);
        
        // Actualizar el total de la venta
        $this->total += $subtotal;
        $this->save();
        
        // Reducir stock del producto
        $product->stock -= $quantity;
        $product->save();
    }

    /**
     * Método para calcular el total desde los productos
     */
    public function calculateTotal()
    {
        $this->total = $this->products()->sum('sale_product.subtotal');
        $this->save();
        return $this->total;
    }

    /**
     * Scope para ventas de un día específico
     */
    public function scopeOfDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope para ventas entre fechas
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope para ventas de una sucursal específica
     */
    public function scopeOfBranch($query, $branchId)
    {
        return $query->where('id_branch', $branchId);
    }

    /**
     * Scope para ventas de un usuario específico
     */
    public function scopeOfUser($query, $userId)
    {
        return $query->where('id_user', $userId);
    }
}
