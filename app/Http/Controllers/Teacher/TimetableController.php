<?php
// app/Http/Controllers/Teacher/TimetableController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        $query = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject', 'timeSlot']);
        
        if ($request->has('day')) {
            $query->where('day_of_week', $request->day);
        }
        
        $timetable = $query->orderBy('day_of_week')
            ->orderBy('time_slot_id')
            ->get()
            ->groupBy('day_of_week');
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $timeSlots = TimeSlot::orderBy('start_time')->get();
        
        $formattedTimetable = [];
        foreach ($days as $day) {
            $formattedTimetable[$day] = [];
            foreach ($timeSlots as $slot) {
                $entry = $timetable[$day] ?? collect();
                $class = $entry->firstWhere('time_slot_id', $slot->id);
                $formattedTimetable[$day][$slot->id] = $class;
            }
        }
        
        return view('teacher.timetable', compact('formattedTimetable', 'days', 'timeSlots'));
    }
}