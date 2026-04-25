<?php
// app/Models/HomeworkSubmission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'homework_id',
        'student_id',
        'submission_text',
        'attachments',
        'submitted_at',
        'obtained_marks',
        'feedback',
        'status'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'attachments' => 'array'
    ];

    // Relationships
    public function homework()
    {
        return $this->belongsTo(Homework::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Accessors
    public function getIsLateAttribute()
    {
        return $this->submitted_at > $this->homework->submission_date;
    }
}