<?php
// app/Models/ParentModel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'parent_type',
        'occupation',
        'office_address',
        'office_phone'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // CORRECT: Relationship with Children (Students)
    public function children()
    {
        return $this->belongsToMany(Student::class, 'student_parents', 'parent_id', 'student_id')
                    ->withPivot('relationship', 'is_primary_contact')
                    ->withTimestamps();
    }

    // Alternative: Get children through the user if needed
    public function students()
    {
        return $this->children();
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->user?->name;
    }

    public function getEmailAttribute()
    {
        return $this->user?->email;
    }

    public function getPhoneAttribute()
    {
        return $this->user?->phone;
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->user?->profile_photo_url;
    }
}