<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'delivery_person_id',
        'total',
        'subtotal',
        'tax_amount',
        'tip_amount',
        'status',
        'order_type',
        'address',
        'phone',
        'payment_method',
        'payment_status',
        'special_instructions',
        'accepted_at',
        'prepared_at',
        'picked_up_at'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'accepted_at' => 'datetime',
        'prepared_at' => 'datetime',
        'picked_up_at' => 'datetime'
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    // Métodos útiles
    public function canBeReviewed()
    {
        return $this->status === 'completed' && !$this->reviews()->exists();
    }

    public function isDelivery()
    {
        return $this->order_type === 'delivery';
    }

    public function isPickup()
    {
        return $this->order_type === 'pickup';
    }
}
