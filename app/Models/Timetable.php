<?php
// app/Models/Timetable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'section_id',
        'day_of_week',
        'time_slot_id',
        'subject_id',
        'teacher_id',
        'room_number'
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

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Accessors
    public function getDayFormattedAttribute()
    {
        return ucfirst($this->day_of_week);
    }

    public function getTimeRangeAttribute()
    {
        return $this->timeSlot->start_time . ' - ' . $this->timeSlot->end_time;
    }
}