<?php
// app/Models/Homework.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'class_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'submission_date',
        'submission_time',
        'attachments',
        'total_marks',
        'status'
    ];

    protected $casts = [
        'submission_date' => 'date',
        'submission_time' => 'datetime',
        'attachments' => 'array'
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('submission_date', '>=', today())
                     ->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('submission_date', '<', today())
                     ->orWhere('status', 'expired');
    }
}