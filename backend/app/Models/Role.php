<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions'
    ];

    protected $casts = [
        'permissions' => 'array'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot(['restaurant_id', 'is_active', 'assigned_at'])
            ->withTimestamps();
    }

    public function hasPermission($permission)
    {
        if (in_array('*', $this->permissions ?? [])) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    // Roles predefinidos
    public static function getSystemRoles()
    {
        return [
            'super_admin' => 'Super Administrador',
            'owner' => 'Propietario',
            'manager' => 'Gerente',
            'cook' => 'Cocinero',
            'delivery' => 'Repartidor',
            'waiter' => 'Mesero',
            'customer' => 'Cliente'
        ];
    }
}
