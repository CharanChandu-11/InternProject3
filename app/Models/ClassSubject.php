<?php
// app/Models/ClassSubject.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    use HasFactory;

    protected $table = 'class_subjects';

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'is_lab_required',
        'theory_marks',
        'practical_marks'
    ];

    protected $casts = [
        'is_lab_required' => 'boolean'
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function examSchedules()
    {
        return $this->hasMany(ExamSchedule::class, 'class_subject_id');
    }
}