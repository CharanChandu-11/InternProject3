<?php
// app/Models/HostelRoom.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id',
        'room_number',
        'room_type',
        'capacity',
        'occupied',
        'fee_per_month'
    ];

    protected $casts = [
        'fee_per_month' => 'decimal:2'
    ];

    // Relationships
    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }

    // Accessors
    public function getAvailableSeatsAttribute()
    {
        return $this->capacity - $this->occupied;
    }

    public function getIsFullAttribute()
    {
        return $this->occupied >= $this->capacity;
    }
}