<?php
// app/Http/Controllers/Student/TimetableController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimetableController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;
        
        $timetable = Timetable::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with(['subject', 'teacher', 'timeSlot'])
            ->get()
            ->groupBy('day_of_week');
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $timeSlots = TimeSlot::orderBy('start_time')->get();
        
        // Format timetable for display
        $formattedTimetable = [];
        foreach ($days as $day) {
            $formattedTimetable[$day] = [];
            foreach ($timeSlots as $slot) {
                $class = $timetable[$day] ?? collect();
                $entry = $class->firstWhere('time_slot_id', $slot->id);
                $formattedTimetable[$day][$slot->id] = $entry;
            }
        }
        
        return view('student.timetable', compact('formattedTimetable', 'days', 'timeSlots'));
    }
    
    public function today()
    {
        $student = Auth::user()->student;
        $today = strtolower(now()->format('l'));
        
        $timetable = Timetable::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('day_of_week', $today)
            ->with(['subject', 'teacher', 'timeSlot'])
            ->orderBy('time_slot_id')
            ->get();
        
        $currentTime = now()->format('H:i:s');
        $currentClass = null;
        $nextClass = null;
        
        foreach ($timetable as $class) {
            if ($class->timeSlot->start_time <= $currentTime && $class->timeSlot->end_time >= $currentTime) {
                $currentClass = $class;
            } elseif ($class->timeSlot->start_time > $currentTime && !$nextClass) {
                $nextClass = $class;
                break;
            }
        }
        
        return view('student.timetable-today', compact('timetable', 'currentClass', 'nextClass'));
    }
}