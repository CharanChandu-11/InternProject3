<?php
// app/Http/Controllers/Student/AttendanceController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance records with filters
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id);
        
        // Apply filters
        if ($request->filled('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }
        
        if ($request->filled('month')) {
            $query->whereMonth('attendance_date', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->whereYear('attendance_date', $request->year);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate(30)
            ->appends($request->query());
        
        // Get summary statistics
        $summary = $this->getAttendanceSummary($student, $request);
        
        // Get available years for filter
        $years = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->selectRaw('DISTINCT YEAR(attendance_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return view('student.attendance.index', compact('attendances', 'summary', 'years'));
    }
    
    /**
     * Display monthly attendance calendar view
     */
    public function monthly(Request $request)
    {
        $student = Auth::user()->student;
        
        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;
        
        // Get all attendance records for the selected month
        $attendances = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->keyBy(function($item) {
                return $item->attendance_date->format('Y-m-d');
            });
        
        // Generate calendar data
        $calendar = $this->generateMonthlyCalendar($year, $month, $attendances);
        
        // Calculate statistics
        $stats = $this->getMonthlyStats($attendances, $year, $month);
        
        // Get available years for filter
        $years = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->selectRaw('DISTINCT YEAR(attendance_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return view('student.attendance.monthly', compact('calendar', 'stats', 'month', 'year', 'years'));
    }
    
    /**
     * Display yearly attendance summary
     */
    public function yearly(Request $request)
    {
        $student = Auth::user()->student;
        
        $academicYear = $request->academic_year ?? $this->getCurrentAcademicYear();
        
        // Parse academic year (e.g., "2024-2025")
        list($startYear, $endYear) = explode('-', $academicYear);
        $startDate = Carbon::createFromDate($startYear, 4, 1); // April 1st
        $endDate = Carbon::createFromDate($endYear, 3, 31);    // March 31st
        
        // Get monthly attendance data for the academic year (April to March)
        $monthlyData = [];
        $totalPresent = 0;
        $totalDays = 0;
        
        // Months from April to March
        $months = [
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
            1 => 'January',
            2 => 'February',
            3 => 'March',
        ];
        
        foreach ($months as $monthNum => $monthName) {
            $yearToUse = ($monthNum >= 4) ? $startYear : $endYear;
            
            $attendances = Attendance::where('attendable_type', 'App\Models\Student')
                ->where('attendable_id', $student->id)
                ->whereMonth('attendance_date', $monthNum)
                ->whereYear('attendance_date', $yearToUse)
                ->get();
            
            $total = $attendances->count();
            $present = $attendances->where('status', 'present')->count();
            $absent = $attendances->where('status', 'absent')->count();
            $late = $attendances->where('status', 'late')->count();
            $halfDay = $attendances->where('status', 'half_day')->count();
            
            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
            
            $monthlyData[] = [
                'month' => $monthName,
                'month_number' => $monthNum,
                'year' => $yearToUse,
                'total_days' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'half_day' => $halfDay,
                'percentage' => $percentage,
            ];
            
            $totalPresent += $present;
            $totalDays += $total;
        }
        
        $yearlyStats = [
            'academic_year' => $academicYear,
            'start_date' => $startDate->format('F j, Y'),
            'end_date' => $endDate->format('F j, Y'),
            'total_days' => $totalDays,
            'total_present' => $totalPresent,
            'total_absent' => $totalDays - $totalPresent,
            'overall_percentage' => $totalDays > 0 ? round(($totalPresent / $totalDays) * 100, 2) : 0,
            'best_month' => collect($monthlyData)->sortByDesc('percentage')->first(),
            'worst_month' => collect($monthlyData)->sortBy('percentage')->first(),
        ];
        
        // Get available academic years for filter
        $academicYears = $this->getAvailableAcademicYears($student);
        
        // Get chart data
        $chartData = [
            'labels' => collect($monthlyData)->pluck('month')->toArray(),
            'data' => collect($monthlyData)->pluck('percentage')->toArray(),
        ];
        
        return view('student.attendance.yearly', compact('monthlyData', 'yearlyStats', 'academicYear', 'academicYears', 'chartData'));
    }
        
    /**
     * Get attendance summary statistics
     */
    private function getAttendanceSummary($student, $request)
    {
        // Current month stats
        $currentMonth = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->whereMonth('attendance_date', Carbon::now()->month)
            ->whereYear('attendance_date', Carbon::now()->year)
            ->get();
        
        $currentMonthTotal = $currentMonth->count();
        $currentMonthPresent = $currentMonth->where('status', 'present')->count();
        
        // Overall stats
        $allAttendances = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->get();
        
        $totalDays = $allAttendances->count();
        $totalPresent = $allAttendances->where('status', 'present')->count();
        
        // Today's attendance
        $today = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();
        
        return [
            'today' => [
                'status' => $today?->status ?? 'not_marked',
                'status_text' => $today ? ucfirst($today->status) : 'Not Marked',
                'check_in' => $today?->check_in_time?->format('h:i A'),
                'check_out' => $today?->check_out_time?->format('h:i A'),
            ],
            'current_month' => [
                'total' => $currentMonthTotal,
                'present' => $currentMonthPresent,
                'percentage' => $currentMonthTotal > 0 ? round(($currentMonthPresent / $currentMonthTotal) * 100, 2) : 0,
            ],
            'overall' => [
                'total' => $totalDays,
                'present' => $totalPresent,
                'percentage' => $totalDays > 0 ? round(($totalPresent / $totalDays) * 100, 2) : 0,
            ],
        ];
    }
    
    /**
     * Generate monthly calendar view
     */
    private function generateMonthlyCalendar($year, $month, $attendances)
    {
        $firstDay = Carbon::createFromDate($year, $month, 1);
        $lastDay = Carbon::createFromDate($year, $month, $firstDay->daysInMonth);
        
        $startDayOfWeek = $firstDay->dayOfWeek; // 0 = Sunday, 6 = Saturday
        $daysInMonth = $firstDay->daysInMonth;
        
        $calendar = [];
        $week = [];
        
        // Add empty cells for days before the first day of month
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $week[] = null;
        }
        
        // Fill in the days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $dateString = $date->format('Y-m-d');
            $attendance = $attendances[$dateString] ?? null;
            
            $week[] = [
                'day' => $day,
                'date' => $date,
                'is_weekend' => $date->isSunday(),
                'status' => $attendance?->status,
                'status_text' => $attendance ? ucfirst($attendance->status) : null,
                'check_in' => $attendance?->check_in_time?->format('h:i A'),
                'check_out' => $attendance?->check_out_time?->format('h:i A'),
                'remarks' => $attendance?->remarks,
            ];
            
            // End of week (Saturday) or end of month
            if ($date->dayOfWeek == 6 || $day == $daysInMonth) {
                $calendar[] = $week;
                $week = [];
            }
        }
        
        return $calendar;
    }
    
    /**
     * Get monthly statistics
     */
    private function getMonthlyStats($attendances, $year, $month)
    {
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $late = $attendances->where('status', 'late')->count();
        $halfDay = $attendances->where('status', 'half_day')->count();
        
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $workingDays = $this->getWorkingDays($year, $month);
        
        return [
            'total_records' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'half_day' => $halfDay,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            'expected_days' => $workingDays,
            'remaining_days' => max(0, $workingDays - $total),
            'attendance_rate' => $workingDays > 0 ? round(($present / $workingDays) * 100, 2) : 0,
        ];
    }
    
    /**
     * Get working days (excluding Sundays)
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

    private function getCurrentAcademicYear()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    private function getAvailableAcademicYears($student)
    {
        $attendanceYears = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->selectRaw('DISTINCT YEAR(attendance_date) as year')
            ->orderBy('year', 'asc')
            ->pluck('year');
        
        if ($attendanceYears->isEmpty()) {
            return collect([$this->getCurrentAcademicYear()]);
        }
        
        $academicYears = collect();
        $minYear = $attendanceYears->min();
        $maxYear = $attendanceYears->max();
        
        for ($year = $minYear; $year <= $maxYear; $year++) {
            $academicYears->push($year . '-' . ($year + 1));
        }
        
        return $academicYears->unique()->sort()->values();
    }
}