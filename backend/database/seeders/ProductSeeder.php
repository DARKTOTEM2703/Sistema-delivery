<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Restaurant;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurants = Restaurant::all();
        
        foreach ($restaurants as $restaurant) {
            $this->createProductsForRestaurant($restaurant);
        }
    }
    
    private function createProductsForRestaurant($restaurant)
    {
        $products = [];
        
        switch ($restaurant->category) {
            case 'italiana':
                $products = [
                    [
                        'name' => 'Pizza Margherita Clásica',
                        'description' => 'Salsa de tomate San Marzano, mozzarella di bufala, albahaca fresca',
                        'price' => 18.99,
                        'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                        'category' => 'pizzas',
                        'preparation_time' => 15
                    ],
                    [
                        'name' => 'Espaguetis Carbonara',
                        'description' => 'Pasta fresca con pancetta, huevos y queso pecorino romano',
                        'price' => 16.99,
                        'image' => 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                        'category' => 'pastas',
                        'preparation_time' => 12
                    ]
                ];
                break;
                
            case 'americana':
                $products = [
                    [
                        'name' => 'Burger Clásica Angus',
                        'description' => 'Carne de res Angus 200g, lechuga, tomate, cebolla',
                        'price' => 15.99,
                        'image' => 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                        'category' => 'hamburguesas',
                        'preparation_time' => 12
                    ],
                    [
                        'name' => 'Buffalo Wings',
                        'description' => 'Alitas de pollo bañadas en salsa buffalo',
                        'price' => 18.99,
                        'image' => 'https://images.unsplash.com/photo-1527477396000-e27163b481c2?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                        'category' => 'aperitivos',
                        'preparation_time' => 18
                    ]
                ];
                break;
                
            case 'japonesa':
                $products = [
                    [
                        'name' => 'Sushi Roll California',
                        'description' => 'Rollo con cangrejo, aguacate, pepino y masago',
                        'price' => 22.99,
                        'image' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                        'category' => 'sushi',
                        'preparation_time' => 15
                    ],
                    [
                        'name' => 'Ramen Tonkotsu',
                        'description' => 'Caldo cremoso de hueso de cerdo, chashu y huevo',
                        'price' => 19.99,
                        'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                        'category' => 'ramen',
                        'preparation_time' => 20
                    ]
                ];
                break;
                
            case 'mexicana':
                $products = [
                    [
                        'name' => 'Tacos de Pastor',
                        'description' => 'Carne de cerdo marinada con piña, cebolla y cilantro',
                        'price' => 12.99,
                        'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                        'category' => 'tacos',
                        'preparation_time' => 8
                    ],
                    [
                        'name' => 'Guacamole con Totopos',
                        'description' => 'Aguacate fresco con tomate, cebolla y cilantro',
                        'price' => 8.99,
                        'image' => 'https://images.unsplash.com/photo-1553909489-cd47e0ef937f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
                        'category' => 'aperitivos',
                        'preparation_time' => 5
                    ]
                ];
                break;
        }
        
        foreach ($products as $productData) {
            $productData['restaurant_id'] = $restaurant->id;
            $productData['rating'] = fake()->randomFloat(1, 4.0, 5.0);
            $productData['time'] = $productData['preparation_time'] . '-' . ($productData['preparation_time'] + 5) . ' min';
            $productData['servings'] = '1 persona';
            $productData['is_available'] = true;
            $productData['allergens'] = '';
            
            Product::create($productData);
        }
        
        echo "🍽️ Productos creados para: {$restaurant->name}\n";
    }
}