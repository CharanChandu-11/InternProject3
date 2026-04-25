<?php
// app/Models/Stop.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stop extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_route_id',
        'stop_name',
        'latitude',
        'longitude',
        'pickup_time',
        'drop_time',
        'fee'
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'drop_time' => 'datetime',
        'fee' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    // Relationships
    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'transport_route_id');
    }

    public function studentTransport()
    {
        return $this->hasMany(StudentTransport::class);
    }
}