<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "🌱 Iniciando seeders completos...\n\n";
        
        $this->call([
            RoleSeeder::class,       // Roles del sistema
            UserSeeder::class,       // Usuarios (admin, owners, customers)
            RestaurantSeeder::class, // Restaurantes completos
            ProductSeeder::class,    // Productos por categoría
        ]);
        
        echo "\n🎉 ¡Base de datos poblada completamente!\n";
        echo "🔑 Credenciales de acceso:\n";
        echo "👨‍💼 Admin: admin@deliveryapp.com / admin123\n";
        echo "🏪 Owner: mario@pizzaitalia.com / password123\n";
        echo "👥 Cliente: juan@cliente.com / cliente123\n";
    }
}