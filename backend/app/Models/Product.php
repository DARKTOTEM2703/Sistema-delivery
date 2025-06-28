<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category',
        'rating',
        'time',
        'servings',
        'restaurant_id',
        'is_available',
        'preparation_time',
        'allergens'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:1',
        'is_available' => 'boolean',
        'preparation_time' => 'integer'
    ];

    // Relaciones
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    // Métodos útiles
    public function isAvailable()
    {
        return $this->is_available && $this->restaurant->is_active;
    }

    public function getAllergensList()
    {
        return $this->allergens ? explode(',', $this->allergens) : [];
    }
}
