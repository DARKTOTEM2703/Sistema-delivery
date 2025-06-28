<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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
        'is_active'
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

    public function employeeRestaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'user_roles')
                    ->withPivot(['role_id', 'is_active', 'assigned_at']);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Order::class, 'delivery_person_id');
    }

    public function employeeSettings()
    {
        return $this->hasMany(EmployeeSetting::class);
    }

    // Métodos de roles y permisos
    public function hasRole($roleName, $restaurantId = null)
    {
        return $this->roles()
                   ->where('name', $roleName)
                   ->when($restaurantId, function($query, $restaurantId) {
                       return $query->wherePivot('restaurant_id', $restaurantId);
                   })
                   ->wherePivot('is_active', true)
                   ->exists();
    }

    public function hasPermission($permission, $restaurantId = null)
    {
        $roles = $this->roles()
                     ->when($restaurantId, function($query, $restaurantId) {
                         return $query->wherePivot('restaurant_id', $restaurantId);
                     })
                     ->wherePivot('is_active', true)
                     ->get();

        foreach ($roles as $role) {
            if (in_array('*', $role->permissions) || in_array($permission, $role->permissions)) {
                return true;
            }
        }

        return false;
    }

    public function assignRole($roleName, $restaurantId = null)
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            throw new \Exception("Role {$roleName} not found");
        }

        return $this->roles()->attach($role->id, [
            'restaurant_id' => $restaurantId,
            'is_active' => true,
            'assigned_at' => now()
        ]);
    }

    public function removeRole($roleName, $restaurantId = null)
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return false;
        }

        return $this->roles()
                   ->wherePivot('role_id', $role->id)
                   ->when($restaurantId, function($query, $restaurantId) {
                       return $query->wherePivot('restaurant_id', $restaurantId);
                   })
                   ->detach();
    }

    public function getRolesForRestaurant($restaurantId)
    {
        return $this->roles()
                   ->wherePivot('restaurant_id', $restaurantId)
                   ->wherePivot('is_active', true)
                   ->get();
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }

    public function isOwner($restaurantId = null)
    {
        if ($restaurantId) {
            return $this->hasRole('owner', $restaurantId);
        }

        return $this->ownedRestaurants()->exists();
    }

    public function canAccessRestaurant($restaurantId)
    {
        return $this->isSuperAdmin() ||
               $this->isOwner($restaurantId) ||
               $this->employeeRestaurants()->where('id', $restaurantId)->exists();
    }
}
