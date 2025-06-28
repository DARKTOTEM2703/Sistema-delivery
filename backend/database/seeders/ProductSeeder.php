<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // ✅ AGREGAR ESTA LÍNEA
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un restaurante de ejemplo si no existe
        $restaurant = Restaurant::first();
        
        if (!$restaurant) {
            // Crear usuario propietario primero
            $owner = User::create([
                'name' => 'Propietario Demo',
                'email' => 'owner@demo.com',
                'password' => Hash::make('password123'), // ✅ CORREGIDO
                'is_active' => true
            ]);

            // Crear restaurante
            $restaurant = Restaurant::create([
                'name' => 'Restaurante Demo',
                'description' => 'Restaurante de ejemplo para testing',
                'slug' => 'restaurante-demo',
                'address' => 'Calle Principal 123',
                'phone' => '+1234567890',
                'email' => 'demo@restaurante.com',
                'category' => 'italiana',
                'delivery_fee' => 3.50,
                'delivery_time_min' => 30,
                'delivery_time_max' => 45,
                'minimum_order' => 15.00,
                'rating' => 4.5,
                'total_reviews' => 0,
                'is_active' => true,
                'accepts_cash' => true,
                'accepts_card' => true,
                'business_hours' => [
                    'monday' => ['is_open' => true, 'open' => '09:00', 'close' => '22:00'],
                    'tuesday' => ['is_open' => true, 'open' => '09:00', 'close' => '22:00'],
                    'wednesday' => ['is_open' => true, 'open' => '09:00', 'close' => '22:00'],
                    'thursday' => ['is_open' => true, 'open' => '09:00', 'close' => '22:00'],
                    'friday' => ['is_open' => true, 'open' => '09:00', 'close' => '23:00'],
                    'saturday' => ['is_open' => true, 'open' => '10:00', 'close' => '23:00'],
                    'sunday' => ['is_open' => false, 'open' => '', 'close' => '']
                ],
                'delivery_zones' => [
                    ['radius' => 5, 'fee' => 3.50]
                ],
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'owner_id' => $owner->id
            ]);
        }

        $products = [
            [
                'name' => 'Hamburguesa Clásica',
                'description' => 'Carne de res, lechuga, tomate, cebolla, pepinillos y salsa especial',
                'price' => 15.99,
                'image' => 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'hamburguesas',
                'rating' => 4.7,
                'time' => '10-15 min',
                'servings' => '1 persona',
                'restaurant_id' => $restaurant->id,
                'is_available' => true,
                'preparation_time' => 12,
                'allergens' => 'gluten'
            ],
            [
                'name' => 'Pizza Margherita',
                'description' => 'Salsa de tomate, mozzarella fresca, albahaca y aceite de oliva',
                'price' => 18.99,
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'pizzas',
                'rating' => 4.8,
                'time' => '15-20 min',
                'servings' => '2 personas',
                'restaurant_id' => $restaurant->id,
                'is_available' => true,
                'preparation_time' => 18,
                'allergens' => 'gluten,lactosa'
            ],
            [
                'name' => 'Ensalada César',
                'description' => 'Lechuga romana, crutones, parmesano y aderezo César',
                'price' => 12.99,
                'image' => 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'ensaladas',
                'rating' => 4.5,
                'time' => '5-10 min',
                'servings' => '1 persona',
                'restaurant_id' => $restaurant->id,
                'is_available' => true,
                'preparation_time' => 8,
                'allergens' => 'lactosa'
            ],
            [
                'name' => 'Espaguetis Bolognesa',
                'description' => 'Espaguetis con salsa de carne, tomate y hierbas',
                'price' => 16.99,
                'image' => 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'pastas',
                'rating' => 4.7,
                'time' => '15-20 min',
                'servings' => '1 persona',
                'restaurant_id' => $restaurant->id,
                'is_available' => true,
                'preparation_time' => 16,
                'allergens' => 'gluten'
            ],
            [
                'name' => 'Tacos de Pollo',
                'description' => 'Tortillas de maíz con pollo marinado, cebolla y cilantro',
                'price' => 13.99,
                'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'mexicana',
                'rating' => 4.6,
                'time' => '8-12 min',
                'servings' => '1 persona',
                'restaurant_id' => $restaurant->id,
                'is_available' => true,
                'preparation_time' => 10,
                'allergens' => ''
            ],
            [
                'name' => 'Sushi Roll California',
                'description' => 'Rollo de sushi con cangrejo, aguacate y pepino',
                'price' => 22.99,
                'image' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'japonesa',
                'rating' => 4.9,
                'time' => '12-18 min',
                'servings' => '1 persona',
                'restaurant_id' => $restaurant->id,
                'is_available' => true,
                'preparation_time' => 15,
                'allergens' => 'pescado'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}