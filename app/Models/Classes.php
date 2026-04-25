<?php
// app/Models/Classes.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'numeric_name',
        'academic_year_id',
        'class_teacher_id',
        'capacity'
    ];

    // Relationships
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classTeacher()
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'class_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'class_id', 'subject_id')
                    ->withPivot('teacher_id', 'theory_marks', 'practical_marks', 'is_lab_required')
                    ->withTimestamps();
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
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
        return $this->name . ($this->numeric_name ? ' (' . $this->numeric_name . ')' : '');
    }

    /**
     * Get average attendance for the class
     */
    public function getAverageAttendanceAttribute()
    {
        $students = $this->students;
        
        if ($students->isEmpty()) {
            return 0;
        }
        
        $totalAttendance = 0;
        foreach ($students as $student) {
            $totalAttendance += $student->attendance_percentage;
        }
        
        return round($totalAttendance / $students->count(), 2);
    }
}