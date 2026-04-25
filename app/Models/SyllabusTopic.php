<?php
// app/Models/SyllabusTopic.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusTopic extends Model
{
    protected $fillable = [
        'syllabus_id', 'title', 'description', 'week_number', 'session_count',
        'learning_objectives', 'teaching_methods', 'assessment_methods', 'sort_order'
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function resources()
    {
        return $this->hasMany(SyllabusResource::class);
    }
}