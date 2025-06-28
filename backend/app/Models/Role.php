<?php
.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'display_name', 'description', 'permissions'
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
        return in_array($permission, $this->permissions);
    }

    // Roles predefinidos
    public static function getSystemRoles()
    {
        return [
            'super_admin' => [
                'display_name' => 'Super Administrador',
                'description' => 'Control total del sistema',
                'permissions' => ['*'] // Todos los permisos
            ],
            'owner' => [
                'display_name' => 'Dueño del Restaurante',
                'description' => 'Propietario y administrador del restaurante',
                'permissions' => [
                    'restaurant.manage', 'menu.manage', 'orders.view',
                    'employees.manage', 'reports.view', 'settings.manage'
                ]
            ],
            'manager' => [
                'display_name' => 'Gerente',
                'description' => 'Administra operaciones diarias',
                'permissions' => [
                    'orders.manage', 'inventory.manage', 'employees.view',
                    'reports.view', 'menu.edit'
                ]
            ],
            'cook' => [
                'display_name' => 'Cocinero',
                'description' => 'Prepara pedidos en cocina',
                'permissions' => [
                    'orders.kitchen', 'orders.update_status', 'menu.view'
                ]
            ],
            'delivery' => [
                'display_name' => 'Repartidor',
                'description' => 'Entrega pedidos a domicilio',
                'permissions' => [
                    'orders.delivery', 'orders.update_location', 'orders.complete'
                ]
            ],
            'waiter' => [
                'display_name' => 'Mesero/Atención',
                'description' => 'Atiende clientes y toma pedidos',
                'permissions' => [
                    'orders.create', 'orders.view', 'customers.assist', 'menu.view'
                ]
            ],
            'customer' => [
                'display_name' => 'Cliente',
                'description' => 'Usuario final del sistema',
                'permissions' => [
                    'orders.create', 'orders.view_own', 'profile.manage', 'reviews.create'
                ]
            ]
        ];
    }
}