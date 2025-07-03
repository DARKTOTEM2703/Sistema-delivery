<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Restaurant;
use App\Models\Order;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'avatar',
        'is_active',
        'last_login_at',
        'role',                    // ✅ SOLO AGREGAR ESTE
        'owned_restaurant_id'      // ✅ SOLO AGREGAR ESTE
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
            'is_active' => 'boolean',
            'last_login_at' => 'datetime'
        ];
    }

    // Relaciones
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['restaurant_id', 'is_active', 'assigned_at'])
            ->withTimestamps();
    }

    public function ownedRestaurants()
    {
        return $this->hasMany(Restaurant::class, 'owner_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function employeeRestaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'user_roles')
            ->wherePivotNotNull('restaurant_id')
            ->wherePivot('is_active', true)
            ->withPivot(['role_id', 'assigned_at']);
    }

    // Métodos de roles y permisos con manejo de errores
    public function hasRole($roleName, $restaurantId = null)
    {
        try {
            return $this->roles()
                ->where('name', $roleName)
                ->when($restaurantId, function ($query, $restaurantId) {
                    return $query->wherePivot('restaurant_id', $restaurantId);
                })
                ->wherePivot('is_active', true)
                ->exists();
        } catch (\Exception $e) {
            \Log::error('Error verificando rol: ' . $e->getMessage());
            return false;
        }
    }

    public function assignRole($roleName, $restaurantId = null)
    {
        try {
            // Verificar si existe la tabla roles
            if (!\Schema::hasTable('roles')) {
                \Log::warning('Tabla roles no existe, omitiendo asignación de rol');
                return false; // ✅ Cambiar return; por return false;
            }

            $role = Role::where('name', $roleName)->first();

            if (!$role) {
                \Log::warning("Rol '{$roleName}' no encontrado");
                return false; // ✅ Cambiar return; por return false;
            }

            // Verificar si ya tiene el rol
            $existingRole = $this->roles()
                ->where('role_id', $role->id)
                ->when($restaurantId, function ($query, $restaurantId) {
                    return $query->wherePivot('restaurant_id', $restaurantId);
                })
                ->first();

            if ($existingRole) {
                // Reactivar si está inactivo
                if (!$existingRole->pivot->is_active) {
                    $this->roles()->updateExistingPivot($role->id, [
                        'is_active' => true,
                        'assigned_at' => now()
                    ]);
                }
                return true; // ✅ Cambiar return; por return true;
            }

            // Asignar nuevo rol
            $this->roles()->attach($role->id, [
                'restaurant_id' => $restaurantId,
                'is_active' => true,
                'assigned_at' => now()
            ]);

            return true; // ✅ AGREGAR ESTA LÍNEA
        } catch (\Exception $e) {
            \Log::error('Error asignando rol: ' . $e->getMessage());
            return false; // ✅ AGREGAR ESTA LÍNEA
        }
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }

    public function isOwner($restaurantId = null)
    {
        return $this->hasRole('owner', $restaurantId);
    }

    public function isEmployee($restaurantId = null)
    {
        $employeeRoles = ['manager', 'cook', 'delivery', 'waiter'];

        foreach ($employeeRoles as $role) {
            if ($this->hasRole($role, $restaurantId)) {
                return true;
            }
        }

        return false;
    }
}