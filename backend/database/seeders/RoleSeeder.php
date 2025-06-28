<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrador',
                'description' => 'Administrador de la plataforma',
                'permissions' => ['*'] // Todos los permisos
            ],
            [
                'name' => 'owner',
                'display_name' => 'Propietario',
                'description' => 'Propietario y administrador del restaurante',
                'permissions' => [
                    'restaurant.manage',
                    'menu.manage',
                    'orders.view',
                    'employees.manage',
                    'reports.view',
                    'settings.manage'
                ]
            ],
            [
                'name' => 'manager',
                'display_name' => 'Gerente',
                'description' => 'Administra operaciones diarias',
                'permissions' => [
                    'orders.manage',
                    'inventory.manage',
                    'employees.view',
                    'reports.view',
                    'menu.edit'
                ]
            ],
            [
                'name' => 'cook',
                'display_name' => 'Cocinero',
                'description' => 'Prepara pedidos en cocina',
                'permissions' => [
                    'orders.kitchen',
                    'orders.update_status',
                    'menu.view'
                ]
            ],
            [
                'name' => 'delivery',
                'display_name' => 'Repartidor',
                'description' => 'Entrega pedidos a domicilio',
                'permissions' => [
                    'orders.delivery',
                    'orders.update_location',
                    'orders.complete'
                ]
            ],
            [
                'name' => 'waiter',
                'display_name' => 'Mesero/Atención',
                'description' => 'Atiende clientes y toma pedidos',
                'permissions' => [
                    'orders.create',
                    'orders.view',
                    'customers.assist',
                    'menu.view'
                ]
            ],
            [
                'name' => 'customer',
                'display_name' => 'Cliente',
                'description' => 'Usuario final del sistema',
                'permissions' => [
                    'orders.create',
                    'orders.view_own',
                    'profile.manage',
                    'reviews.create'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}