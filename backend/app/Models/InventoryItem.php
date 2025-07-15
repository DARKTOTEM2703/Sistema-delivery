<?php
// filepath: c:\xampp\htdocs\Sistema-delivery\backend\app\Models\InventoryItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'category',
        'current_stock',
        'unit',
        'min_stock',
        'last_restock_at'
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'last_restock_at' => 'datetime'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function isLowStock()
    {
        return $this->current_stock <= $this->min_stock && $this->current_stock > 0;
    }

    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }
}