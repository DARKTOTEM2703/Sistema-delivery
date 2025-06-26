<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Hamburguesa Clásica',
                'description' => 'Carne de res, lechuga, tomate, cebolla, pepinillos y salsa especial',
                'price' => 15.99,
                'image' => 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'hamburguesas',
                'rating' => 4.7,
                'time' => '10-15 min',
                'servings' => '1 persona'
            ],
            [
                'name' => 'Pizza Margherita',
                'description' => 'Salsa de tomate, mozzarella fresca, albahaca y aceite de oliva',
                'price' => 18.99,
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'pizzas',
                'rating' => 4.8,
                'time' => '15-20 min',
                'servings' => '2 personas'
            ],
            [
                'name' => 'Ensalada César',
                'description' => 'Lechuga romana, crutones, parmesano y aderezo César',
                'price' => 12.99,
                'image' => 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'ensaladas',
                'rating' => 4.5,
                'time' => '5-10 min',
                'servings' => '1 persona'
            ],
            [
                'name' => 'Espaguetis Bolognesa',
                'description' => 'Espaguetis con salsa de carne, tomate y hierbas',
                'price' => 16.99,
                'image' => 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                'category' => 'pastas',
                'rating' => 4.7,
                'time' => '15-20 min',
                'servings' => '1 persona'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}