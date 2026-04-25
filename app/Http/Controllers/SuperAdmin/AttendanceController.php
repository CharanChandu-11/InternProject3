<?php
// app/Http/Controllers/SuperAdmin/AttendanceController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Section;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['attendable.user', 'attendable.class', 'attendable.section', 'markedByUser'])
            ->where('attendable_type', Student::class);
        
        // Filters
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
            ->paginate(50)
            ->appends($request->query());
        
        $classes = Classes::with('sections')->orderBy('numeric_name')->get();
        $students = $request->class_id ? Student::where('class_id', $request->class_id)->get() : collect();
        
        return view('super-admin.attendance.index', compact('attendances', 'classes', 'students'));
    }
    
    /**
     * Show attendance summary report
     */
    public function summary(Request $request)
    {
        $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);
        
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        
        // Get classes for filter
        $classes = Classes::with('sections')->orderBy('numeric_name')->get();
        
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
        
        // Calculate days in month (excluding Sundays if configured)
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
                'is_working_day' => !$date->isSunday(), // Exclude Sundays
            ];
        }
        
        return view('super-admin.attendance.summary', compact('classes', 'students', 'attendanceData', 'summaryStats', 'calendarDays', 'month', 'year'));
    }
    
    /**
     * Show attendance marking form
     */
    public function mark($classId, $sectionId, Request $request)
    {
        $class = Classes::findOrFail($classId);
        $section = Section::findOrFail($sectionId);
        
        $date = $request->date ?? today()->format('Y-m-d');
        
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
        
        return view('super-admin.attendance.mark', compact('class', 'section', 'students', 'existingAttendance', 'date', 'alreadyMarked'));
    }
    
    /**
     * Store attendance records
     */
    public function store(Request $request, $classId, $sectionId)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,half_day',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);
        
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
                        'marked_by' => Auth::id(),
                    ]
                );
                $saved++;
            }
            
            DB::commit();
            
            $class = Classes::find($classId);
            $section = Section::find($sectionId);
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'marked',
                'module' => 'attendance',
                'description' => "Marked attendance for {$class->name} - Section {$section->name} on {$request->date}",
            ]);
            
            return redirect()->route('super-admin.attendance.index')
                ->with('success', "Attendance marked successfully for {$saved} students.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to mark attendance: ' . $e->getMessage());
        }
    }
    
    /**
     * Edit a single attendance record
     */
    public function edit(Attendance $attendance)
    {
        $attendance->load(['attendable.user', 'attendable.class', 'attendable.section']);
        return view('super-admin.attendance.edit', compact('attendance'));
    }
    
    /**
     * Update a single attendance record
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,half_day',
            'remarks' => 'nullable|string|max:255',
        ]);
        
        $oldStatus = $attendance->status;
        $attendance->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
            'marked_by' => Auth::id(),
        ]);
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'module' => 'attendance',
            'description' => "Updated attendance for student ID {$attendance->attendable_id} from {$oldStatus} to {$request->status}",
        ]);
        
        return redirect()->route('super-admin.attendance.index')
            ->with('success', 'Attendance record updated successfully.');
    }
    
    /**
     * Delete an attendance record
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'attendance',
            'description' => "Deleted attendance record for student ID {$attendance->attendable_id} on {$attendance->attendance_date->format('Y-m-d')}",
        ]);
        
        return redirect()->route('super-admin.attendance.index')
            ->with('success', 'Attendance record deleted successfully.');
    }
    
    /**
     * Bulk delete attendance records
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:attendances,id',
        ]);
        
        $deleted = Attendance::whereIn('id', $request->ids)->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'bulk_deleted',
            'module' => 'attendance',
            'description' => "Bulk deleted {$deleted} attendance records",
        ]);
        
        return redirect()->route('super-admin.attendance.index')
            ->with('success', "{$deleted} attendance records deleted successfully.");
    }
    
    /**
     * Get student attendance report for a specific student
     */
    public function studentReport($studentId, Request $request)
    {
        $student = Student::with(['user', 'class', 'section'])->findOrFail($studentId);
        
        $year = $request->year ?? now()->year;
        
        $attendances = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $studentId)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->get()
            ->groupBy(function($item) {
                return $item->attendance_date->format('F Y');
            });
        
        $monthlySummary = [];
        foreach ($attendances as $month => $monthlyRecords) {
            $total = $monthlyRecords->count();
            $present = $monthlyRecords->where('status', 'present')->count();
            $monthlySummary[] = [
                'month' => $month,
                'total_days' => $total,
                'present' => $present,
                'absent' => $total - $present,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ];
        }
        
        $overallPercentage = $student->attendance_percentage;
        
        return view('super-admin.attendance.student-report', compact('student', 'monthlySummary', 'overallPercentage', 'year'));
    }
    
    /**
     * Export attendance report to Excel
     */
    public function export(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000',
        ]);
        
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        
        // Get students
        $studentQuery = Student::with(['user', 'class', 'section']);
        
        if ($request->filled('class_id')) {
            $studentQuery->where('class_id', $request->class_id);
        }
        
        if ($request->filled('section_id')) {
            $studentQuery->where('section_id', $request->section_id);
        }
        
        $students = $studentQuery->orderBy('class_id')->orderBy('roll_number')->get();
        
        // Get attendance records
        $attendances = Attendance::where('attendable_type', Student::class)
            ->whereIn('attendable_id', $students->pluck('id'))
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->groupBy('attendable_id');
        
        // Prepare export data
        $exportData = [];
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        
        foreach ($students as $student) {
            $row = [
                'Student Name' => $student->user->name,
                'Admission No' => $student->admission_number,
                'Class' => $student->class->name,
                'Section' => $student->section->name,
                'Roll No' => $student->roll_number,
            ];
            
            $studentAttendances = $attendances[$student->id] ?? collect();
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromDate($year, $month, $day);
                $attendance = $studentAttendances->firstWhere('attendance_date', $date);
                $row[$date->format('d M')] = $attendance ? ucfirst($attendance->status) : '—';
            }
            
            $totalDays = $studentAttendances->count();
            $present = $studentAttendances->where('status', 'present')->count();
            $row['Total Present'] = $present;
            $row['Total Absent'] = $totalDays - $present;
            $row['Percentage'] = $totalDays > 0 ? round(($present / $totalDays) * 100, 2) . '%' : '0%';
            
            $exportData[] = $row;
        }
        
        // Generate Excel file
        $excel = \Maatwebsite\Excel\Facades\Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
            public function headings(): array { return !empty($this->data) ? array_keys($this->data[0]) : []; }
        }, "attendance_{$month}_{$year}.xlsx");
        
        return $excel;
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