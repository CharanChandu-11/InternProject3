<?php
// app/Models/CalendarEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'type', 'start_date', 'end_date',
        'start_time', 'end_time', 'repeat_type', 'venue', 'audience',
        'color', 'is_active', 'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    const TYPES = [
        'holiday' => 'Holiday',
        'event' => 'Event',
        'exam' => 'Exam',
        'meeting' => 'Meeting',
        'deadline' => 'Deadline',
        'other' => 'Other'
    ];

    const AUDIENCE = [
        'all' => 'Everyone',
        'students' => 'Students Only',
        'teachers' => 'Teachers Only',
        'parents' => 'Parents Only',
        'staff' => 'Staff Only'
    ];

    const REPEAT_TYPES = [
        'none' => 'No Repeat',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }

    public function scopeByAudience($query, $audience)
    {
        return $query->whereIn('audience', ['all', $audience]);
    }
}