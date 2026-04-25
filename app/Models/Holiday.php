<?php
// app/Models/Holiday.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name', 'description', 'date', 'type', 'is_optional', 'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_optional' => 'boolean',
        'is_active' => 'boolean'
    ];

    const TYPES = [
        'public' => 'Public Holiday',
        'school' => 'School Holiday',
        'national' => 'National Holiday',
        'religious' => 'Religious Holiday',
        'festival' => 'Festival'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }
}