<?php
// app/Models/SyllabusResource.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusResource extends Model
{
    protected $fillable = [
        'syllabus_topic_id', 'title', 'type', 'url', 'file_path', 'description'
    ];

    public function topic()
    {
        return $this->belongsTo(SyllabusTopic::class, 'syllabus_topic_id');
    }
}