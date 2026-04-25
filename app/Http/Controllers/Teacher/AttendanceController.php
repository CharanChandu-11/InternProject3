<?php
// app/Http/Controllers/Teacher/AttendanceController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Section;
use App\Models\ClassSubject;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance dashboard with class selection
     */
    public function index()
    {
        $teacher = Auth::user();
        
        // Get unique class-section combinations from timetable
        $classSections = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section'])
            ->get()
            ->unique(function($item) {
                return $item->class_id . '_' . $item->section_id;
            })
            ->map(function($item) {
                $students = Student::where('class_id', $item->class_id)
                    ->where('section_id', $item->section_id)
                    ->count();
                    
                $todayMarked = Attendance::whereDate('attendance_date', Carbon::today())
                    ->whereHas('attendable', function($q) use ($item) {
                        $q->where('class_id', $item->class_id)
                          ->where('section_id', $item->section_id);
                    })
                    ->exists();
                
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name,
                    'section_id' => $item->section_id,
                    'section_name' => $item->section->name,
                    'students_count' => $students,
                    'attendance_marked_today' => $todayMarked,
                ];
            })
            ->values();
        
        return view('teacher.attendance.index', compact('classSections'));
    }
    
    /**
     * Show attendance marking form for a specific class-section
     */
    public function mark($classId, $sectionId, Request $request)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this class-section
        $teachesClass = Timetable::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->exists();
        
        if (!$teachesClass) {
            return redirect()->route('teacher.attendance.index')
                ->with('error', 'You are not authorized to mark attendance for this class.');
        }
        
        $class = Classes::findOrFail($classId);
        $section = Section::findOrFail($sectionId);
        
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        
        // Get students
        $students = Student::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->with('user')
            ->orderBy('roll_number')
            ->get();
        
        // Check if attendance already marked for this date
        $existingAttendance = Attendance::where('attendable_type', Student::class)
            ->whereIn('attendable_id', $students->pluck('id'))
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('attendable_id');
        
        $alreadyMarked = $existingAttendance->isNotEmpty();
        
        return view('teacher.attendance.mark', compact('class', 'section', 'students', 'existingAttendance', 'date', 'alreadyMarked'));
    }
    
    /**
     * Store attendance records
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,half_day',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);
        
        $teacher = Auth::user();
        
        // Verify teacher teaches this class-section
        $teachesClass = Timetable::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->exists();
        
        if (!$teachesClass) {
            return redirect()->route('teacher.attendance.index')
                ->with('error', 'You are not authorized to mark attendance for this class.');
        }
        
        DB::beginTransaction();
        
        try {
            $saved = 0;
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
                    ]
                );
                $saved++;
            }
            
            DB::commit();
            
            return redirect()->route('teacher.attendance.index')
                ->with('success', "Attendance marked successfully for {$saved} students on " . Carbon::parse($request->date)->format('d-m-Y'));
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to mark attendance: ' . $e->getMessage());
        }
    }
    
    /**
     * View attendance history with filters
     */
    public function history(Request $request)
    {
        $teacher = Auth::user();
        
        // Get all class-sections taught by this teacher
        $classSections = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section'])
            ->get()
            ->unique(function($item) {
                return $item->class_id . '_' . $item->section_id;
            })
            ->map(function($item) {
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name,
                    'section_id' => $item->section_id,
                    'section_name' => $item->section->name,
                ];
            })
            ->values();
        
        // Build query for attendance records
        $query = Attendance::where('attendable_type', Student::class)
            ->whereHas('attendable', function($q) use ($teacher) {
                $q->whereIn('class_id', function($cq) use ($teacher) {
                    $cq->select('class_id')
                        ->from('timetables')
                        ->where('teacher_id', $teacher->id);
                });
            })
            ->with(['attendable.user', 'attendable.class', 'attendable.section', 'markedByUser']);
        
        // Apply filters
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
        
        if ($request->filled('student_id')) {
            $query->where('attendable_id', $request->student_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('attendance_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('attendance_date', '<=', $request->date_to);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate(30)
            ->appends($request->query());
        
        // Get students for the selected class
        $students = collect();
        if ($request->filled('class_id')) {
            $students = Student::where('class_id', $request->class_id)
                ->with('user')
                ->orderBy('roll_number')
                ->get();
        }
        
        return view('teacher.attendance.history', compact('attendances', 'classSections', 'students'));
    }
    
    /**
     * View attendance summary report
     */
    public function summary(Request $request)
    {
        $teacher = Auth::user();
        
        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;
        
        // Get all class-sections taught by this teacher
        $classSections = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section'])
            ->get()
            ->unique(function($item) {
                return $item->class_id . '_' . $item->section_id;
            })
            ->map(function($item) {
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name,
                    'section_id' => $item->section_id,
                    'section_name' => $item->section->name,
                ];
            })
            ->values();
        
        // Build query for students
        $studentQuery = Student::with(['user', 'class', 'section']);
        
        if ($request->filled('class_id')) {
            $studentQuery->where('class_id', $request->class_id);
        }
        
        if ($request->filled('section_id')) {
            $studentQuery->where('section_id', $request->section_id);
        }
        
        $students = $studentQuery->orderBy('class_id')->orderBy('roll_number')->get();
        
        // Get attendance data for the selected month
        $attendanceData = [];
        $summaryStats = [
            'total_students' => $students->count(),
            'total_days' => 0,
            'overall_present' => 0,
            'overall_absent' => 0,
            'overall_late' => 0,
            'overall_half_day' => 0,
        ];
        
        // Get all attendance records for the month
        $attendances = Attendance::where('attendable_type', Student::class)
            ->whereIn('attendable_id', $students->pluck('id'))
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->groupBy('attendable_id');
        
        // Calculate working days (excluding Sundays)
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $workingDays = $this->getWorkingDays($year, $month);
        $summaryStats['total_days'] = $workingDays;
        
        // Prepare student-wise data
        foreach ($students as $student) {
            $studentAttendances = $attendances[$student->id] ?? collect();
            $present = $studentAttendances->where('status', 'present')->count();
            $absent = $studentAttendances->where('status', 'absent')->count();
            $late = $studentAttendances->where('status', 'late')->count();
            $halfDay = $studentAttendances->where('status', 'half_day')->count();
            
            $attendanceData[] = [
                'student' => $student,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'half_day' => $halfDay,
                'percentage' => $workingDays > 0 ? round(($present / $workingDays) * 100, 2) : 0,
                'records' => $studentAttendances->keyBy(function($item) {
                    return $item->attendance_date->format('Y-m-d');
                }),
            ];
            
            $summaryStats['overall_present'] += $present;
            $summaryStats['overall_absent'] += $absent;
            $summaryStats['overall_late'] += $late;
            $summaryStats['overall_half_day'] += $halfDay;
        }
        
        // Calculate overall percentage
        $totalPossible = $workingDays * $students->count();
        $summaryStats['overall_percentage'] = $totalPossible > 0 
            ? round(($summaryStats['overall_present'] / $totalPossible) * 100, 2) 
            : 0;
        
        // Get calendar days for the month
        $calendarDays = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $calendarDays[] = [
                'date' => $date,
                'day_name' => $date->format('D'),
                'is_working_day' => !$date->isSunday(),
            ];
        }
        
        return view('teacher.attendance.summary', compact('classSections', 'attendanceData', 'summaryStats', 'calendarDays', 'month', 'year'));
    }
    
    /**
     * Get working days in a month (excluding Sundays)
     */
    private function getWorkingDays($year, $month)
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $workingDays = 0;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            if (!$date->isSunday()) {
                $workingDays++;
            }
        }
        
        return $workingDays;
    }
}