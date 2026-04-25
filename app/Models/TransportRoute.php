<?php
// app/Models/TransportRoute.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportRoute extends Model
{
    use HasFactory;

    protected $table = 'transport_routes';

    protected $fillable = [
        'route_name',
        'route_number',
        'description'
    ];

    // Relationships
    public function stops()
    {
        return $this->hasMany(Stop::class);
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'route_vehicles')
                    ->withPivot('shift')
                    ->withTimestamps();
    }

    public function studentTransports()
    {
        return $this->hasMany(StudentTransport::class, 'transport_route_id');
    }

    
}