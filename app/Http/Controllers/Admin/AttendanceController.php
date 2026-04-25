<?php
// app/Http/Controllers/Admin/AttendanceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Section;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance records with filters
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['attendable.user', 'attendable.class', 'attendable.section'])
            ->where('attendable_type', Student::class);

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('attendable', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Filter by section
        if ($request->filled('section_id')) {
            $query->whereHas('attendable', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by student name/admission number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('attendable', function($q) use ($search) {
                $q->where('admission_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate(20);

        $classes = Classes::all();
        $sections = Section::all();

        return view('admin.attendance.index', compact('attendances', 'classes', 'sections'));
    }

    /**
     * Show form to mark attendance for a specific class/section
     */
    public function mark(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'nullable|date',
        ]);

        $class = Classes::findOrFail($request->class_id);
        $section = Section::findOrFail($request->section_id);
        $date = $request->date ?? today();

        // Get students in this class/section
        $students = Student::where('class_id', $class->id)
            ->where('section_id', $section->id)
            ->with('user')
            ->orderBy('roll_number')
            ->get();

        // Get existing attendance for this date
        $existingAttendance = Attendance::whereDate('attendance_date', $date)
            ->whereIn('attendable_id', $students->pluck('id'))
            ->where('attendable_type', Student::class)
            ->get()
            ->keyBy('attendable_id');

        $classes = Classes::all();

        return view('admin.attendance.mark', compact('class', 'section', 'students', 'existingAttendance', 'date', 'classes'));
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
                        'marked_by' => auth()->id(),
                        'check_in_time' => $data['status'] == 'present' ? now() : null,
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('admin.attendance.index', [
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                ])
                ->with('success', 'Attendance marked successfully for ' . Carbon::parse($request->date)->format('F j, Y'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark attendance: ' . $e->getMessage());
        }
    }

    /**
     * Show attendance summary report
     */
    public function summary(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000',
        ]);

        $classId = $request->class_id;
        $sectionId = $request->section_id;
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $query = Student::with(['user', 'class', 'section']);

        if ($classId) {
            $query->where('class_id', $classId);
        }
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $students = $query->orderBy('roll_number')->get();

        // Get attendance data for each student
        $attendanceData = [];
        $totalDays = 0;

        foreach ($students as $student) {
            $attendances = Attendance::where('attendable_type', Student::class)
                ->where('attendable_id', $student->id)
                ->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year)
                ->get();

            $present = $attendances->where('status', 'present')->count();
            $absent = $attendances->where('status', 'absent')->count();
            $late = $attendances->where('status', 'late')->count();
            $halfDay = $attendances->where('status', 'half_day')->count();
            $total = $attendances->count();

            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            $attendanceData[] = [
                'student' => $student,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'half_day' => $halfDay,
                'total_days' => $total,
                'percentage' => $percentage,
            ];

            if ($totalDays < $total) $totalDays = $total;
        }

        // Calculate class summary
        $classSummary = [
            'total_students' => $students->count(),
            'total_present' => collect($attendanceData)->sum('present'),
            'total_absent' => collect($attendanceData)->sum('absent'),
            'total_late' => collect($attendanceData)->sum('late'),
            'total_half_day' => collect($attendanceData)->sum('half_day'),
            'average_percentage' => $students->count() > 0 
                ? round(collect($attendanceData)->avg('percentage'), 2) 
                : 0,
        ];

        $classes = Classes::all();
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return view('admin.attendance.summary', compact(
            'attendanceData', 'classSummary', 'classes', 'classId', 'sectionId',
            'month', 'year', 'months', 'totalDays'
        ));
    }

    /**
     * Edit attendance for a specific student and date
     */
    public function edit(Attendance $attendance)
    {
        $attendance->load(['attendable.user', 'attendable.class', 'attendable.section']);
        
        $statuses = ['present', 'absent', 'late', 'half_day'];
        
        return view('admin.attendance.edit', compact('attendance', 'statuses'));
    }

    /**
     * Update attendance record
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,half_day',
            'remarks' => 'nullable|string|max:255',
        ]);

        $attendance->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
            'marked_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.attendance.index', [
                'class_id' => $attendance->attendable->class_id,
                'section_id' => $attendance->attendable->section_id,
            ])
            ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Delete attendance record
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()
            ->back()
            ->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * Export attendance report
     */
    public function export(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $query = Attendance::with(['attendable.user', 'attendable.class', 'attendable.section'])
            ->where('attendable_type', Student::class)
            ->whereBetween('attendance_date', [$request->from_date, $request->to_date]);

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

        $attendances = $query->orderBy('attendance_date')->get();

        // Prepare export data
        $exportData = $attendances->map(function($attendance) {
            return [
                'Date' => $attendance->attendance_date->format('Y-m-d'),
                'Student Name' => $attendance->attendable->user->name,
                'Admission No' => $attendance->attendable->admission_number,
                'Class' => $attendance->attendable->class->name,
                'Section' => $attendance->attendable->section->name,
                'Status' => ucfirst($attendance->status),
                'Check In' => $attendance->check_in_time?->format('h:i A'),
                'Remarks' => $attendance->remarks,
            ];
        });

        // You can use Laravel Excel or simply return JSON
        return response()->json([
            'data' => $exportData,
            'total_records' => $exportData->count(),
        ]);
    }
}