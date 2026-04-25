<?php
// app/Models/ExamResult.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_schedule_id',
        'student_id',
        'theory_marks_obtained',
        'practical_marks_obtained',
        'total_marks_obtained',
        'grade',
        'remarks'
    ];

    // Relationships
    public function examSchedule()
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Accessors
    public function getPercentageAttribute()
    {
        $totalMarks = $this->examSchedule->total_marks;
        if ($totalMarks == 0) return 0;
        return round(($this->total_marks_obtained / $totalMarks) * 100, 2);
    }

    public function getResultStatusAttribute()
    {
        $passingMarks = $this->examSchedule->passing_marks;
        return $this->total_marks_obtained >= $passingMarks ? 'Pass' : 'Fail';
    }
}