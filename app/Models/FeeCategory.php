<?php
// app/Models/FeeCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description'
    ];

    // Relationships
    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }
}