<?php
// app/Models/Exam.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'exam_type_id',
        'academic_year_id',
        'start_date',
        'end_date',
        'description',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Relationships
    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', today())
                     ->where('status', 'upcoming');
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', today())
                     ->where('end_date', '>=', today())
                     ->where('status', 'ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('end_date', '<', today())
                     ->where('status', 'completed');
    }
}