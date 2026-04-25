<?php
// app/Models/Subject.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description'
    ];

    // Relationships
    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_subjects', 'subject_id', 'class_id')
                    ->withPivot('teacher_id', 'theory_marks', 'practical_marks', 'is_lab_required')
                    ->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'class_subjects', 'subject_id', 'teacher_id')
                    ->withPivot('class_id', 'theory_marks', 'practical_marks')
                    ->withTimestamps();
    }

    // If you want to get the class_subject pivot record for a specific class
    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'subject_id');
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function examSchedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function homework()
    {
        return $this->hasMany(Homework::class);
    }
}