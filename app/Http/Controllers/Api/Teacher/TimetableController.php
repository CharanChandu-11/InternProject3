<?php
// app/Http/Controllers/Api/Teacher/TimetableController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\TimetableResource;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimetableController extends BaseController
{
    /**
     * Get teacher's timetable
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        $query = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject', 'timeSlot']);
        
        // Filter by day
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
            $formattedTimetable[$day] = [
                'day' => ucfirst($day),
                'slots' => []
            ];
            
            foreach ($timeSlots as $slot) {
                $entry = $timetable[$day] ?? collect();
                $class = $entry->firstWhere('time_slot_id', $slot->id);
                
                $formattedTimetable[$day]['slots'][] = [
                    'time_slot' => [
                        'id' => $slot->id,
                        'name' => $slot->name,
                        'time_range' => $slot->time_range,
                        'start_time' => $slot->start_time->format('h:i A'),
                        'end_time' => $slot->end_time->format('h:i A'),
                        'is_break' => $slot->is_break,
                    ],
                    'class' => $class ? [
                        'id' => $class->id,
                        'class_id' => $class->class_id,
                        'class_name' => $class->class->name,
                        'section_id' => $class->section_id,
                        'section_name' => $class->section->name,
                        'subject_id' => $class->subject_id,
                        'subject_name' => $class->subject->name,
                        'subject_code' => $class->subject->code,
                        'room_number' => $class->room_number,
                    ] : null,
                ];
            }
        }
        
        return $this->sendResponse($formattedTimetable, 'Timetable retrieved successfully');
    }
    
    /**
     * Get today's timetable
     */
    public function today()
    {
        $teacher = Auth::user();
        $today = strtolower(now()->format('l'));
        
        $timetable = Timetable::where('teacher_id', $teacher->id)
            ->where('day_of_week', $today)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->orderBy('time_slot_id')
            ->get();
        
        $currentTime = now()->format('H:i:s');
        $nextClass = null;
        $currentClass = null;
        
        foreach ($timetable as $class) {
            if ($class->timeSlot->start_time <= $currentTime && $class->timeSlot->end_time >= $currentTime) {
                $currentClass = $class;
            } elseif ($class->timeSlot->start_time > $currentTime && !$nextClass) {
                $nextClass = $class;
                break;
            }
        }
        
        return $this->sendResponse([
            'today' => TimetableResource::collection($timetable),
            'current_class' => $currentClass ? new TimetableResource($currentClass) : null,
            'next_class' => $nextClass ? new TimetableResource($nextClass) : null,
            'classes_completed' => $timetable->filter(function($class) use ($currentTime) {
                return $class->timeSlot->end_time < $currentTime;
            })->count(),
            'classes_remaining' => $timetable->filter(function($class) use ($currentTime) {
                return $class->timeSlot->start_time > $currentTime;
            })->count(),
        ], "Today's timetable retrieved successfully");
    }
    
    /**
     * Get weekly schedule summary
     */
    public function weeklySummary()
    {
        $teacher = Auth::user();
        
        $timetable = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->get()
            ->groupBy('day_of_week');
        
        $summary = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        foreach ($days as $day) {
            $dayClasses = $timetable[$day] ?? collect();
            $summary[] = [
                'day' => ucfirst($day),
                'total_classes' => $dayClasses->count(),
                'subjects' => $dayClasses->pluck('subject.name')->unique()->values(),
                'classes' => $dayClasses->map(function($class) {
                    return [
                        'time' => $class->timeSlot->time_range,
                        'subject' => $class->subject->name,
                        'class' => $class->class->name,
                        'section' => $class->section->name,
                        'room' => $class->room_number,
                    ];
                }),
            ];
        }
        
        return $this->sendResponse($summary, 'Weekly schedule summary retrieved');
    }
}