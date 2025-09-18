<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_inventory';

    protected $fillable = [
        'name',
        'type',
        'id_branch',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Constantes para tipos de inventario
     */
    public const TYPE_SALE_PRODUCT = 'sale_product';
    public const TYPE_RAW_MATERIAL = 'raw_material';

    public const TYPES = [
        self::TYPE_SALE_PRODUCT => 'Producto de Venta',
        self::TYPE_RAW_MATERIAL => 'Materia Prima',
    ];

    /**
     * Un inventario pertenece a una sucursal
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'id_branch', 'id_branch');
    }

    /**
     * Un inventario puede tener muchos productos
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_inventory', 'id_inventory');
    }

    /**
     * Scope para filtrar por tipo de inventario
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para inventarios de productos de venta
     */
    public function scopeSaleProducts($query)
    {
        return $query->where('type', self::TYPE_SALE_PRODUCT);
    }

    /**
     * Scope para inventarios de materia prima
     */
    public function scopeRawMaterials($query)
    {
        return $query->where('type', self::TYPE_RAW_MATERIAL);
    }

    /**
     * Accessor para obtener el nombre del tipo formateado
     */
    public function getTypeNameAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
