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
        'food_rating',
        'service_rating',
        'delivery_rating',
        'comment',
        'is_anonymous',
        'response',
        'responded_at',
        'images'
    ];

    protected $casts = [
        'rating' => 'integer',
        'food_rating' => 'integer',
        'service_rating' => 'integer',
        'delivery_rating' => 'integer',
        'is_anonymous' => 'boolean',
        'responded_at' => 'datetime',
        'images' => 'array'
    ];

    protected $appends = ['helpful_count', 'not_helpful_count'];

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

    public function helpfulVotes()
    {
        return $this->hasMany(ReviewVote::class)->where('is_helpful', true);
    }

    public function notHelpfulVotes()
    {
        return $this->hasMany(ReviewVote::class)->where('is_helpful', false);
    }

    // Accessors
    public function getHelpfulCountAttribute()
    {
        return $this->helpfulVotes()->count();
    }

    public function getNotHelpfulCountAttribute()
    {
        return $this->notHelpfulVotes()->count();
    }

    public function getDisplayNameAttribute()
    {
        return $this->is_anonymous ? 'Usuario Anónimo' : $this->user->name;
    }

    public function getDisplayAvatarAttribute()
    {
        return $this->is_anonymous ? null : $this->user->avatar;
    }

    // Métodos útiles
    public function hasResponse()
    {
        return !empty($this->response);
    }

    public function getAverageRating()
    {
        $ratings = array_filter([
            $this->food_rating,
            $this->service_rating,
            $this->delivery_rating
        ]);

        return count($ratings) > 0 ? array_sum($ratings) / count($ratings) : $this->rating;
    }
}

class ReviewVote extends Model
{
    protected $fillable = [
        'review_id',
        'user_id',
        'is_helpful'
    ];

    protected $casts = [
        'is_helpful' => 'boolean'
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
