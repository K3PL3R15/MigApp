<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'id_branch', // solo aplica para managers y empleados
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Roles disponibles
     */
    public const ROLE_OWNER = 'owner';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_EMPLOYEE = 'employee';

    public const ROLES = [
        self::ROLE_OWNER    => 'Propietario',
        self::ROLE_MANAGER  => 'Gerente',
        self::ROLE_EMPLOYEE => 'Empleado',
    ];

    /**
     * Relación: manager/employee → sucursal asignada
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'id_branch', 'id_branch');
    }

    /**
     * Relación: owner → sucursales que creó
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'id_user', 'id');
    }

    /**
     * Relación: ventas realizadas por el usuario
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'id_user', 'id');
    }

    /**
     * Accessor para obtener el nombre del rol formateado
     */
    public function getRoleNameAttribute()
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    /**
     * Verificar si el usuario es propietario
     */
    public function isOwner()
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Verificar si el usuario es gerente
     */
    public function isManager()
    {
        return $this->role === self::ROLE_MANAGER;
    }

    /**
     * Verificar si el usuario es empleado
     */
    public function isEmployee()
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    /**
     * Verificar si el usuario puede gestionar una sucursal específica
     */
    public function canManageBranch($branchId)
    {
        if ($this->isOwner()) {
            return $this->branches()->where('id_branch', $branchId)->exists();
        }
        
        return $this->id_branch == $branchId;
    }

    /**
     * Scopes
     */
    public function scopeOwners($query)
    {
        return $query->where('role', self::ROLE_OWNER);
    }

    public function scopeManagers($query)
    {
        return $query->where('role', self::ROLE_MANAGER);
    }

    public function scopeEmployees($query)
    {
        return $query->where('role', self::ROLE_EMPLOYEE);
    }

    public function scopeOfBranch($query, $branchId)
    {
        return $query->where('id_branch', $branchId);
    }

    public function scopeOfRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
