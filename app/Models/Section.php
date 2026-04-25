<?php
// app/Models/Section.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class_id',
        'capacity'
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function homework()
    {
        return $this->hasMany(Homework::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->class->name . ' - Section ' . $this->name;
    }
}