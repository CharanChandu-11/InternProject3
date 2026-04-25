<?php
// app/Http/Controllers/Api/Student/TimetableController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimetableController extends BaseController
{
    /**
     * Get full weekly timetable
     */
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
        
        $formattedTimetable = [];
        foreach ($days as $day) {
            $dayEntries = [];
            foreach ($timeSlots as $slot) {
                $entry = $timetable[$day] ?? collect();
                $class = $entry->firstWhere('time_slot_id', $slot->id);
                
                $dayEntries[] = [
                    'time_slot' => [
                        'id' => $slot->id,
                        'name' => $slot->name,
                        'start_time' => Carbon::parse($slot->start_time)->format('h:i A'),
                        'end_time' => Carbon::parse($slot->end_time)->format('h:i A'),
                        'is_break' => $slot->is_break,
                    ],
                    'subject' => $class ? [
                        'id' => $class->subject->id,
                        'name' => $class->subject->name,
                        'code' => $class->subject->code,
                    ] : null,
                    'teacher' => $class ? [
                        'id' => $class->teacher->id,
                        'name' => $class->teacher->name,
                    ] : null,
                    'room_number' => $class ? $class->room_number : null,
                ];
            }
            $formattedTimetable[$day] = $dayEntries;
        }
        
        return $this->sendResponse([
            'days' => $days,
            'time_slots' => $timeSlots->map(fn($slot) => [
                'id' => $slot->id,
                'name' => $slot->name,
                'start_time' => Carbon::parse($slot->start_time)->format('h:i A'),
                'end_time' => Carbon::parse($slot->end_time)->format('h:i A'),
                'is_break' => $slot->is_break,
            ]),
            'timetable' => $formattedTimetable,
        ], 'Weekly timetable retrieved');
    }
    
    /**
     * Get today's timetable with current and next class
     */
    public function today()
    {
        $student = Auth::user()->student;
        $today = strtolower(now()->format('l'));
        $currentTime = now()->format('H:i:s');
        
        $timetable = Timetable::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('day_of_week', $today)
            ->with(['subject', 'teacher', 'timeSlot'])
            ->orderBy('time_slot_id')
            ->get();
        
        $currentClass = null;
        $nextClass = null;
        
        foreach ($timetable as $class) {
            $start = $class->timeSlot->start_time;
            $end = $class->timeSlot->end_time;
            if ($start <= $currentTime && $end >= $currentTime) {
                $currentClass = $class;
            } elseif ($start > $currentTime && !$nextClass) {
                $nextClass = $class;
                break;
            }
        }
        
        $classes = $timetable->map(fn($class) => [
            'time_slot' => [
                'id' => $class->timeSlot->id,
                'name' => $class->timeSlot->name,
                'start_time' => Carbon::parse($class->timeSlot->start_time)->format('h:i A'),
                'end_time' => Carbon::parse($class->timeSlot->end_time)->format('h:i A'),
                'is_break' => $class->timeSlot->is_break,
            ],
            'subject' => [
                'id' => $class->subject->id,
                'name' => $class->subject->name,
                'code' => $class->subject->code,
            ],
            'teacher' => [
                'id' => $class->teacher->id,
                'name' => $class->teacher->name,
            ],
            'room_number' => $class->room_number,
        ]);
        
        return $this->sendResponse([
            'date' => now()->toDateString(),
            'day' => ucfirst($today),
            'classes' => $classes,
            'current_class' => $currentClass ? [
                'subject' => $currentClass->subject->name,
                'teacher' => $currentClass->teacher->name,
                'room' => $currentClass->room_number,
                'ends_at' => Carbon::parse($currentClass->timeSlot->end_time)->format('h:i A'),
            ] : null,
            'next_class' => $nextClass ? [
                'subject' => $nextClass->subject->name,
                'teacher' => $nextClass->teacher->name,
                'room' => $nextClass->room_number,
                'starts_at' => Carbon::parse($nextClass->timeSlot->start_time)->format('h:i A'),
            ] : null,
        ], "Today's timetable retrieved");
    }
}