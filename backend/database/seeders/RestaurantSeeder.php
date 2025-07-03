<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\User;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurants = [
            [
                'name' => 'Pizza Italia Auténtica',
                'description' => 'Auténtica pizza italiana con ingredientes frescos',
                'slug' => 'pizza-italia-autentica',
                'address' => 'Via Roma 45, Centro Histórico',
                'phone' => '+1234567891',
                'email' => 'mario@pizzaitalia.com',
                'category' => 'italiana',
                'delivery_fee' => 3.50,
                'delivery_time_min' => 25,
                'delivery_time_max' => 35,
                'minimum_order' => 15.00,
                'rating' => 4.8,
                'total_reviews' => 247,
                'owner_email' => 'mario@pizzaitalia.com'
            ],
            [
                'name' => 'Burger House Premium',
                'description' => 'Las mejores hamburguesas gourmet de la ciudad',
                'slug' => 'burger-house-premium',
                'address' => 'Av. Principal 789, Zona Comercial',
                'phone' => '+1234567892',
                'email' => 'carlos@burgerhouse.com',
                'category' => 'americana',
                'delivery_fee' => 2.99,
                'delivery_time_min' => 20,
                'delivery_time_max' => 30,
                'minimum_order' => 12.00,
                'rating' => 4.6,
                'total_reviews' => 189,
                'owner_email' => 'carlos@burgerhouse.com'
            ],
            [
                'name' => 'Sushi Zen',
                'description' => 'Sushi fresco preparado por maestros japoneses',
                'slug' => 'sushi-zen',
                'address' => 'Calle Sakura 321, Distrito Japonés',
                'phone' => '+1234567893',
                'email' => 'ana@sushizen.com',
                'category' => 'japonesa',
                'delivery_fee' => 4.99,
                'delivery_time_min' => 30,
                'delivery_time_max' => 45,
                'minimum_order' => 20.00,
                'rating' => 4.9,
                'total_reviews' => 156,
                'owner_email' => 'ana@sushizen.com'
            ],
            [
                'name' => 'Tacos Chingones',
                'description' => 'Auténticos tacos mexicanos con recetas familiares',
                'slug' => 'tacos-chingones',
                'address' => 'Av. Revolución 456, Barrio Mexicano',
                'phone' => '+1234567894',
                'email' => 'jose@tacoschingones.com',
                'category' => 'mexicana',
                'delivery_fee' => 2.50,
                'delivery_time_min' => 15,
                'delivery_time_max' => 25,
                'minimum_order' => 8.00,
                'rating' => 4.7,
                'total_reviews' => 203,
                'owner_email' => 'jose@tacoschingones.com'
            ]
        ];

        foreach ($restaurants as $restaurantData) {
            $owner = User::where('email', $restaurantData['owner_email'])->first();
            
            if ($owner) {
                unset($restaurantData['owner_email']);
                $restaurantData['owner_id'] = $owner->id;
                $restaurantData['latitude'] = fake()->latitude(40.7589, 40.7789);
                $restaurantData['longitude'] = fake()->longitude(-74.0079, -73.9579);
                $restaurantData['is_active'] = true;
                $restaurantData['accepts_cash'] = true;
                $restaurantData['accepts_card'] = true;
                $restaurantData['business_hours'] = [
                    'monday' => ['is_open' => true, 'open' => '11:00', 'close' => '23:00'],
                    'tuesday' => ['is_open' => true, 'open' => '11:00', 'close' => '23:00'],
                    'wednesday' => ['is_open' => true, 'open' => '11:00', 'close' => '23:00'],
                    'thursday' => ['is_open' => true, 'open' => '11:00', 'close' => '23:00'],
                    'friday' => ['is_open' => true, 'open' => '11:00', 'close' => '00:00'],
                    'saturday' => ['is_open' => true, 'open' => '12:00', 'close' => '00:00'],
                    'sunday' => ['is_open' => true, 'open' => '12:00', 'close' => '22:00']
                ];
                $restaurantData['delivery_zones'] = [
                    ['name' => 'Centro', 'radius' => 3],
                    ['name' => 'Norte', 'radius' => 5]
                ];
                
                $restaurant = Restaurant::create($restaurantData);
                
                // Actualizar el usuario owner
                $owner->update([
                    'owned_restaurant_id' => $restaurant->id
                ]);
                
                echo "🏪 Restaurante creado: {$restaurant->name}\n";
            }
        }
    }
}