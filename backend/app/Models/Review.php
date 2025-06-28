<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'order_id',
        'rating',
        'comment',
        'is_anonymous',
        'response',
        'responded_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_anonymous' => 'boolean',
        'responded_at' => 'datetime'
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->whereNotNull('comment');
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    // Métodos útiles
    public function getDisplayNameAttribute()
    {
        return $this->is_anonymous ? 'Usuario anónimo' : $this->user->name;
    }

    public function hasResponse()
    {
        return !is_null($this->response);
    }
}
