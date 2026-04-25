<?php
// app/Models/FeeStructure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'fee_category_id',
        'amount',
        'frequency',
        'is_optional'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_optional' => 'boolean'
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }
}