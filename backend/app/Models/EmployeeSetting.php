<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'hourly_rate',
        'commission_rate',
        'work_schedule',
        'can_receive_orders',
        'status',
        'max_orders_per_hour',
        'vehicle_type',
        'emergency_contact',
        'start_date',
        'notes'
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'work_schedule' => 'array',
        'can_receive_orders' => 'boolean',
        'start_date' => 'date',
        'max_orders_per_hour' => 'integer'
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    // Métodos útiles
    public function isAvailable()
    {
        return $this->status === 'available' && $this->can_receive_orders;
    }

    public function canWorkToday()
    {
        $today = strtolower(now()->format('l'));
        return isset($this->work_schedule[$today]) && $this->work_schedule[$today]['is_working'];
    }

    public function getTodaySchedule()
    {
        $today = strtolower(now()->format('l'));
        return $this->work_schedule[$today] ?? null;
    }
}
