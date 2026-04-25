<?php
// app/Models/LeaveType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'days_allowed',
        'applicable_for'
    ];

    // Relationships
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }
}