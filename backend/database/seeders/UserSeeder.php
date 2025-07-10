<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador
        User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@deliveryapp.com',
            'password' => Hash::make('admin123'),
            'phone' => '+1234567890',
            'address' => 'Oficina Central 123',
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now()
        ]);

        // Propietarios de restaurantes
        $owners = [
            [
                'name' => 'Mario Rossi',
                'email' => 'mario@pizzaitalia.com',
                'password' => Hash::make(value: 'password123'),
                'phone' => '+1234567891',
                'address' => 'Via Roma 45',
                'role' => 'owner',
                'is_active' => true,
                'email_verified_at' => now()
            ],
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos@burgerhouse.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567892',
                'address' => 'Av. Principal 789',
                'role' => 'owner',
                'is_active' => true,
                'email_verified_at' => now()
            ],
            [
                'name' => 'Ana García',
                'email' => 'ana@sushizen.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567893',
                'address' => 'Calle Sakura 321',
                'role' => 'owner',
                'is_active' => true,
                'email_verified_at' => now()
            ],
            [
                'name' => 'José Tacos',
                'email' => 'jose@tacoschingones.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567894',
                'address' => 'Av. Revolución 456',
                'role' => 'owner',
                'is_active' => true,
                'email_verified_at' => now()
            ]
        ];

        foreach ($owners as $ownerData) {
            User::create($ownerData);
        }

        // Clientes de prueba
        $customers = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan@cliente.com',
                'password' => Hash::make('cliente123'),
                'phone' => '+1234567896',
                'address' => 'Residencial Norte 123',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now()
            ],
            [
                'name' => 'María López',
                'email' => 'maria@cliente.com',
                'password' => Hash::make('cliente123'),
                'phone' => '+1234567897',
                'address' => 'Colonia Centro 456',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now()
            ]
        ];

        foreach ($customers as $customerData) {
            User::create($customerData);
        }

        echo "✅ Usuarios creados\n";
    }
}
