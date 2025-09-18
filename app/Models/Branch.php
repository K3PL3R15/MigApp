<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';
    protected $primaryKey = 'id_branch';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'direction',
        'phone',
        'id_user',
        'unique_code',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($branch) {
            if (empty($branch->unique_code)) {
                $branch->unique_code = 'BRN-' . strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Dueño de la sucursal (owner)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Usuarios asignados a la sucursal (managers y empleados)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_branch', 'id_branch');
    }

    /**
     * Inventarios de la sucursal
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'id_branch', 'id_branch');
    }

    /**
     * Ventas realizadas en la sucursal
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'id_branch', 'id_branch');
    }

    /**
     * Transferencias de origen desde esta sucursal
     */
    public function originTransfers(): HasMany
    {
        return $this->hasMany(RequestTransfer::class, 'id_origin_branch', 'id_branch');
    }

    /**
     * Transferencias de destino hacia esta sucursal
     */
    public function destinyTransfers(): HasMany
    {
        return $this->hasMany(RequestTransfer::class, 'id_destiny_branch', 'id_branch');
    }

    /**
     * Scope para obtener solo la sucursal matriz
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    /**
     * Scope para obtener sucursales de un usuario específico
     */
    public function scopeOfUser($query, $userId)
    {
        return $query->where('id_user', $userId);
    }
}
