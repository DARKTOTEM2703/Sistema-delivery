<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'slug', 'logo', 'cover_image', 
        'address', 'phone', 'email', 'category', 'delivery_fee',
        'delivery_time_min', 'delivery_time_max', 'minimum_order', 
        'rating', 'total_reviews', 'is_active', 'accepts_cash', 
        'accepts_card', 'business_hours', 'delivery_zones', 
        'latitude', 'longitude', 'owner_id'
    ];

    protected $casts = [
        'business_hours' => 'array',
        'delivery_zones' => 'array',
        'is_active' => 'boolean',
        'accepts_cash' => 'boolean',
        'accepts_card' => 'boolean',
        'rating' => 'decimal:1',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    // Relaciones
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->wherePivot('restaurant_id', $this->id)
                    ->withPivot(['role_id', 'is_active', 'assigned_at'])
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Métodos útiles
    public function isOpen()
    {
        $now = now();
        $dayOfWeek = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');
        
        $hours = $this->business_hours[$dayOfWeek] ?? null;
        
        if (!$hours || !$hours['is_open']) {
            return false;
        }
        
        return $currentTime >= $hours['open'] && $currentTime <= $hours['close'];
    }

    public function canDeliverTo($latitude, $longitude)
    {
        // Lógica para verificar si entrega a esa ubicación
        foreach ($this->delivery_zones as $zone) {
            $distance = $this->calculateDistance(
                $this->latitude, $this->longitude,
                $latitude, $longitude
            );
            
            if ($distance <= $zone['radius']) {
                return true;
            }
        }
        
        return false;
    }

    public function getAverageDeliveryTime()
    {
        return ($this->delivery_time_min + $this->delivery_time_max) / 2;
    }

    public function getTodayOrders()
    {
        return $this->orders()->whereDate('created_at', today())->count();
    }

    public function getTodayRevenue()
    {
        return $this->orders()
                   ->whereDate('created_at', today())
                   ->where('status', 'completed')
                   ->sum('total');
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Fórmula de Haversine para calcular distancia
        $earthRadius = 6371; // Radio de la Tierra en km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
}