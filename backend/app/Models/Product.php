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
        'servings'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:1'
    ];

    // Relación con los items de pedido
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
