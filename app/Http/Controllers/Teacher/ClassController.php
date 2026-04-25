<?php
// app/Http/Controllers/Teacher/ClassController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClassController extends Controller
{
    /**
     * Display classes taught by the teacher
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        // Get unique class-section combinations from timetable
        $timetableData = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject'])
            ->get()
            ->groupBy(function($item) {
                return $item->class_id . '_' . $item->section_id;
            });
        
        $classes = [];
        
        foreach ($timetableData as $key => $entries) {
            $firstEntry = $entries->first();
            $class = $firstEntry->class;
            $section = $firstEntry->section;
            
            // Get all subjects taught by this teacher for this class-section
            $subjects = $entries->map(function($entry) {
                return $entry->subject;
            })->unique('id')->values();
            
            // Get students in this class-section
            $students = Student::where('class_id', $class->id)
                ->where('section_id', $section->id)
                ->with('user')
                ->orderBy('roll_number')
                ->get();
            
            // Get today's attendance for these students
            $todayAttendance = Attendance::whereDate('attendance_date', Carbon::today())
                ->whereIn('attendable_id', $students->pluck('id'))
                ->where('attendable_type', Student::class)
                ->get()
                ->keyBy('attendable_id');
            
            // Calculate attendance statistics for this class
            $totalStudents = $students->count();
            $presentToday = $todayAttendance->where('status', 'present')->count();
            $absentToday = $todayAttendance->where('status', 'absent')->count();
            $lateToday = $todayAttendance->where('status', 'late')->count();
            
            // Get timetable for this class-section
            $timetable = Timetable::where('class_id', $class->id)
                ->where('section_id', $section->id)
                ->where('teacher_id', $teacher->id)
                ->with(['subject', 'timeSlot'])
                ->orderBy('day_of_week')
                ->orderBy('time_slot_id')
                ->get()
                ->groupBy('day_of_week');
            
            $classes[] = [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'class_full_name' => $class->full_name,
                'section_id' => $section->id,
                'section_name' => $section->name,
                'capacity' => $section->capacity,
                'students' => $students,
                'students_count' => $students->count(),
                'subjects' => $subjects,
                'timetable' => $timetable,
                'attendance_stats' => [
                    'total' => $totalStudents,
                    'present' => $presentToday,
                    'absent' => $absentToday,
                    'late' => $lateToday,
                    'percentage' => $totalStudents > 0 ? round(($presentToday / $totalStudents) * 100, 2) : 0,
                ],
            ];
        }
        
        // Sort classes by class name
        usort($classes, function($a, $b) {
            return strcmp($a['class_name'], $b['class_name']);
        });
        
        // Get statistics summary
        $totalStudents = collect($classes)->sum('students_count');
        $totalClasses = count($classes);
        $totalSubjects = collect($classes)->flatMap(function($class) {
            return $class['subjects'];
        })->unique('id')->count();
        
        $stats = [
            'total_classes' => $totalClasses,
            'total_students' => $totalStudents,
            'total_subjects' => $totalSubjects,
            'average_class_size' => $totalClasses > 0 ? round($totalStudents / $totalClasses, 2) : 0,
        ];
        
        return view('teacher.classes', compact('classes', 'stats'));
    }
    
    /**
     * Get students for a specific class-section (AJAX)
     */
    public function getStudents($classId, $sectionId)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this class-section
        $teachesClass = Timetable::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->exists();
        
        if (!$teachesClass) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $students = Student::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->with('user')
            ->orderBy('roll_number')
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'admission_number' => $student->admission_number,
                    'roll_number' => $student->roll_number,
                    'email' => $student->user->email,
                    'phone' => $student->user->phone,
                    'profile_photo' => $student->user->profile_photo_url,
                    'attendance_percentage' => $student->attendance_percentage,
                ];
            });
        
        return response()->json($students);
    }
    
    /**
     * Get subjects for a specific class-section (AJAX)
     */
    public function getSubjects($classId, $sectionId)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this class-section
        $teachesClass = Timetable::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->exists();
        
        if (!$teachesClass) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $subjects = Timetable::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values()
            ->map(function($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'type' => $subject->type,
                ];
            });
        
        return response()->json($subjects);
    }
}