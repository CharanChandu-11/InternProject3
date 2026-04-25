<?php
// app/Http/Controllers/Api/Teacher/ClassController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClassController extends BaseController
{
    public function index()
    {
        $teacher = Auth::user();
        
        $classes = ClassSubject::where('teacher_id', $teacher->id)
            ->with(['class', 'class.sections', 'subject'])
            ->get()
            ->groupBy('class_id')
            ->map(function($items) {
                $class = $items->first()->class;
                $students = Student::where('class_id', $class->id)->count();
                
                return [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'full_name' => $class->full_name,
                    'students_count' => $students,
                    'sections' => $class->sections->map(function($section) {
                        return [
                            'id' => $section->id,
                            'name' => $section->name,
                            'students_count' => $section->students()->count(),
                        ];
                    }),
                    'subjects' => $items->map(function($item) {
                        return [
                            'id' => $item->subject->id,
                            'name' => $item->subject->name,
                            'code' => $item->subject->code,
                            'theory_marks' => $item->theory_marks,
                            'practical_marks' => $item->practical_marks,
                        ];
                    }),
                ];
            })
            ->values();
        
        return $this->sendResponse($classes, 'Classes retrieved successfully');
    }
    
    public function students($classId, Request $request)
    {
        $teacher = Auth::user();
        
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $query = Student::where('class_id', $classId)
            ->with(['user', 'section']);
        
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $students = $query->orderBy('roll_number')->paginate($request->per_page ?? 50);
        
        return $this->sendPaginatedResponse($students, 'Students retrieved successfully');
    }
    
    public function subjects($classId)
    {
        $teacher = Auth::user();
        
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $subjects = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->with('subject')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->subject->id,
                    'name' => $item->subject->name,
                    'code' => $item->subject->code,
                    'theory_marks' => $item->theory_marks,
                    'practical_marks' => $item->practical_marks,
                ];
            });
        
        return $this->sendResponse($subjects, 'Subjects retrieved successfully');
    }
    
    public function sections($classId)
    {
        $teacher = Auth::user();
        
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $class = \App\Models\Classes::find($classId);
        $sections = $class->sections->map(function($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'students_count' => $section->students()->count(),
            ];
        });
        
        return $this->sendResponse($sections, 'Sections retrieved successfully');
    }
    
    public function timetable()
    {
        $teacher = Auth::user();
        
        $timetable = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->orderBy('day_of_week')
            ->orderBy('time_slot_id')
            ->get()
            ->groupBy('day_of_week');
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $timeSlots = TimeSlot::orderBy('start_time')->get();
        
        $formatted = [];
        foreach ($days as $day) {
            $formatted[$day] = [
                'day' => ucfirst($day),
                'slots' => []
            ];
            
            foreach ($timeSlots as $slot) {
                $entry = $timetable[$day] ?? collect();
                $class = $entry->firstWhere('time_slot_id', $slot->id);
                
                $formatted[$day]['slots'][] = [
                    'time' => $slot->time_range,
                    'class' => $class ? [
                        'class_name' => $class->class->name,
                        'section_name' => $class->section->name,
                        'subject' => $class->subject->name,
                        'room' => $class->room_number,
                    ] : null,
                    'is_break' => $slot->is_break,
                ];
            }
        }
        
        return $this->sendResponse($formatted, 'Timetable retrieved');
    }
    
    public function todayTimetable()
    {
        $teacher = Auth::user();
        $today = strtolower(Carbon::now()->format('l'));
        $currentTime = Carbon::now()->format('H:i:s');
        
        $timetable = Timetable::where('teacher_id', $teacher->id)
            ->where('day_of_week', $today)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->orderBy('time_slot_id')
            ->get();
        
        $currentClass = null;
        $nextClass = null;
        
        foreach ($timetable as $class) {
            if ($class->timeSlot->start_time <= $currentTime && $class->timeSlot->end_time >= $currentTime) {
                $currentClass = $class;
            } elseif ($class->timeSlot->start_time > $currentTime && !$nextClass) {
                $nextClass = $class;
            }
        }
        
        return $this->sendResponse([
            'classes' => $timetable->map(function($class) {
                return [
                    'time' => $class->timeSlot->time_range,
                    'class' => $class->class->name,
                    'section' => $class->section->name,
                    'subject' => $class->subject->name,
                    'room' => $class->room_number,
                ];
            }),
            'current_class' => $currentClass ? [
                'subject' => $currentClass->subject->name,
                'class' => $currentClass->class->name,
                'section' => $currentClass->section->name,
                'room' => $currentClass->room_number,
                'ends_at' => Carbon::parse($currentClass->timeSlot->end_time)->format('h:i A'),
            ] : null,
            'next_class' => $nextClass ? [
                'subject' => $nextClass->subject->name,
                'class' => $nextClass->class->name,
                'section' => $nextClass->section->name,
                'room' => $nextClass->room_number,
                'starts_at' => Carbon::parse($nextClass->timeSlot->start_time)->format('h:i A'),
            ] : null,
        ], 'Today\'s timetable retrieved');
    }
    
    public function weeklyTimetable()
    {
        $teacher = Auth::user();
        
        $timetable = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->orderBy('day_of_week')
            ->orderBy('time_slot_id')
            ->get()
            ->groupBy('day_of_week');
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $result = [];
        
        foreach ($days as $day) {
            $dayClasses = $timetable[$day] ?? collect();
            $result[] = [
                'day' => ucfirst($day),
                'classes' => $dayClasses->map(function($class) {
                    return [
                        'time' => $class->timeSlot->time_range,
                        'class' => $class->class->name,
                        'section' => $class->section->name,
                        'subject' => $class->subject->name,
                        'room' => $class->room_number,
                    ];
                }),
                'total_classes' => $dayClasses->count(),
            ];
        }
        
        return $this->sendResponse($result, 'Weekly timetable retrieved');
    }
}