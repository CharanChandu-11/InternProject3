<?php
// app/Http/Controllers/Api/Teacher/AttendanceController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends BaseController
{
    public function classes()
    {
        $teacher = Auth::user();
        
        $classes = ClassSubject::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->get()
            ->groupBy('class_id')
            ->map(function($items) {
                $class = $items->first()->class;
                return [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'sections' => $class->sections->map(function($section) {
                        return [
                            'id' => $section->id,
                            'name' => $section->name,
                            'students_count' => $section->students()->count(),
                            'attendance_marked_today' => Attendance::whereDate('attendance_date', Carbon::today())
                                ->whereHas('attendable', function($q) use ($section) {
                                    $q->where('section_id', $section->id);
                                })
                                ->exists(),
                        ];
                    }),
                    'subjects' => $items->pluck('subject.name'),
                ];
            })
            ->values();
        
        return $this->sendResponse($classes, 'Classes retrieved');
    }
    
    public function students($classId, $sectionId)
    {
        $teacher = Auth::user();
        
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $students = Student::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->with('user')
            ->orderBy('roll_number')
            ->get();
        
        $existingAttendance = Attendance::whereDate('attendance_date', Carbon::today())
            ->whereIn('attendable_id', $students->pluck('id'))
            ->where('attendable_type', Student::class)
            ->get()
            ->keyBy('attendable_id');
        
        $studentsData = $students->map(function($student) use ($existingAttendance) {
            $attendance = $existingAttendance[$student->id] ?? null;
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'profile_photo' => $student->user->profile_photo_url,
                'status' => $attendance?->status ?? 'not_marked',
                'remarks' => $attendance?->remarks,
            ];
        });
        
        return $this->sendResponse([
            'students' => $studentsData,
            'date' => Carbon::today()->toDateString(),
            'total_students' => $students->count(),
            'attendance_marked' => $existingAttendance->isNotEmpty(),
        ], 'Students retrieved');
    }
    
    public function mark(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,half_day',
            'attendance.*.remarks' => 'nullable|string',
        ]);
        
        $teacher = Auth::user();
        
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        DB::beginTransaction();
        
        try {
            foreach ($request->attendance as $data) {
                Attendance::updateOrCreate(
                    [
                        'attendable_type' => Student::class,
                        'attendable_id' => $data['student_id'],
                        'attendance_date' => $request->date,
                    ],
                    [
                        'status' => $data['status'],
                        'remarks' => $data['remarks'] ?? null,
                        'marked_by' => $teacher->id,
                        'check_in_time' => $data['status'] == 'present' ? now() : null,
                    ]
                );
            }
            
            DB::commit();
            
            return $this->sendResponse([
                'marked_count' => count($request->attendance),
                'date' => $request->date,
            ], 'Attendance marked successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to mark attendance: ' . $e->getMessage(), [], 500);
        }
    }
    
    public function history(Request $request)
    {
        $teacher = Auth::user();
        
        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');
        
        $query = Attendance::whereIn('attendable_id', $studentIds)
            ->where('attendable_type', Student::class)
            ->with(['attendable.user', 'attendable.class', 'attendable.section']);
        
        if ($request->filled('class_id')) {
            $query->whereHas('attendable', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }
        
        if ($request->filled('section_id')) {
            $query->whereHas('attendable', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }
        
        if ($request->filled('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate($request->per_page ?? 50);
        
        return $this->sendPaginatedResponse($attendances, 'Attendance history retrieved');
    }
    
    public function summary(Request $request)
    {
        $teacher = Auth::user();
        
        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');
        
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;
        
        $attendances = Attendance::whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->whereIn('attendable_id', $studentIds)
            ->where('attendable_type', Student::class)
            ->get();
        
        $dailyBreakdown = [];
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $dayAttendances = $attendances->filter(function($attendance) use ($date) {
                return $attendance->attendance_date->isSameDay($date);
            });
            
            $dailyBreakdown[] = [
                'date' => $date->toDateString(),
                'day' => $date->format('D'),
                'present' => $dayAttendances->where('status', 'present')->count(),
                'absent' => $dayAttendances->where('status', 'absent')->count(),
                'percentage' => $dayAttendances->count() > 0 
                    ? round(($dayAttendances->where('status', 'present')->count() / $dayAttendances->count()) * 100, 2) 
                    : 0,
            ];
        }
        
        $summary = [
            'month' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
            'total_days' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'overall_percentage' => $attendances->count() > 0 
                ? round(($attendances->where('status', 'present')->count() / $attendances->count()) * 100, 2) 
                : 0,
            'daily_breakdown' => $dailyBreakdown,
        ];
        
        return $this->sendResponse($summary, 'Attendance summary retrieved');
    }
    
    public function report(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);
        
        $students = Student::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->with('user')
            ->orderBy('roll_number')
            ->get();
        
        $attendances = Attendance::whereMonth('attendance_date', $request->month)
            ->whereYear('attendance_date', $request->year)
            ->whereIn('attendable_id', $students->pluck('id'))
            ->where('attendable_type', Student::class)
            ->get()
            ->groupBy('attendable_id');
        
        $report = [];
        foreach ($students as $student) {
            $studentAttendances = $attendances[$student->id] ?? collect();
            $report[] = [
                'student_id' => $student->id,
                'student_name' => $student->user->name,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'present' => $studentAttendances->where('status', 'present')->count(),
                'absent' => $studentAttendances->where('status', 'absent')->count(),
                'late' => $studentAttendances->where('status', 'late')->count(),
                'percentage' => $studentAttendances->count() > 0 
                    ? round(($studentAttendances->where('status', 'present')->count() / $studentAttendances->count()) * 100, 2) 
                    : 0,
            ];
        }
        
        return $this->sendResponse($report, 'Attendance report generated');
    }
}