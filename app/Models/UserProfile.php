<?php
// app/Models/UserProfile.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'blood_group',
        'religion',
        'nationality',
        'permanent_address',
        'current_address',
        'emergency_contact',
        'emergency_contact_name',
        'medical_conditions',
        'qualification',
        'experience_years',  // Changed from 'experience' to match column name
        'bio',
        'social_links'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'social_links' => 'array',
        'experience_years' => 'integer'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getGenderTextAttribute()
    {
        return ucfirst($this->gender);
    }
}