<?php
// app/Models/Vehicle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_number',
        'vehicle_type',
        'model',
        'capacity',
        'driver_name',
        'driver_license',
        'driver_phone',
        'insurance_expiry'
    ];

    protected $casts = [
        'insurance_expiry' => 'date'
    ];

    // Relationships
    public function routes()
    {
        return $this->belongsToMany(TransportRoute::class, 'route_vehicles')
                    ->withPivot('shift')
                    ->withTimestamps();
    }
}