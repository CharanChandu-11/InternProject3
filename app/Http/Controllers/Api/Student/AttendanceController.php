<?php
// app/Http/Controllers/Api/Student/AttendanceController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends BaseController
{
    /**
     * Display attendance records with filters (paginated)
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id);
        
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
            ->paginate($request->per_page ?? 30);
        
        $summary = $this->getAttendanceSummary($student);
        
        $years = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->selectRaw('DISTINCT YEAR(attendance_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return $this->sendResponse([
            'attendances' => $attendances->items(),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
            'summary' => $summary,
            'available_years' => $years,
        ], 'Attendance records retrieved');
    }
    
    /**
     * Monthly attendance calendar and statistics
     */
    public function monthly(Request $request)
    {
        $student = Auth::user()->student;
        
        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;
        
        $attendances = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->keyBy(fn($item) => $item->attendance_date->format('Y-m-d'));
        
        $calendar = $this->generateMonthlyCalendar($year, $month, $attendances);
        $stats = $this->getMonthlyStats($attendances, $year, $month);
        
        $firstDay = Carbon::createFromDate($year, $month, 1);
        $lastDay = Carbon::createFromDate($year, $month, $firstDay->daysInMonth);
        
        $years = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->selectRaw('DISTINCT YEAR(attendance_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        if ($years->isEmpty()) {
            $years = collect([Carbon::now()->year]);
        }
        
        return $this->sendResponse([
            'month' => $month,
            'month_name' => Carbon::createFromDate($year, $month, 1)->format('F'),
            'year' => $year,
            'date_range' => [
                'start' => $firstDay->toDateString(),
                'end' => $lastDay->toDateString(),
            ],
            'calendar' => $calendar,
            'statistics' => $stats,
            'available_years' => $years,
        ], 'Monthly attendance data retrieved');
    }
    
    /**
     * Yearly attendance summary (academic year April-March)
     */
    public function yearly(Request $request)
    {
        $student = Auth::user()->student;
        
        $academicYear = $request->academic_year ?? $this->getCurrentAcademicYear();
        list($startYear, $endYear) = explode('-', $academicYear);
        
        $months = [
            4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September',
            10 => 'October', 11 => 'November', 12 => 'December',
            1 => 'January', 2 => 'February', 3 => 'March',
        ];
        
        $monthlyData = [];
        $totalPresent = 0;
        $totalDays = 0;
        
        foreach ($months as $monthNum => $monthName) {
            $yearToUse = ($monthNum >= 4) ? $startYear : $endYear;
            
            $attendances = Attendance::where('attendable_type', Student::class)
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
            'start_date' => Carbon::createFromDate($startYear, 4, 1)->format('Y-m-d'),
            'end_date' => Carbon::createFromDate($endYear, 3, 31)->format('Y-m-d'),
            'total_days' => $totalDays,
            'total_present' => $totalPresent,
            'total_absent' => $totalDays - $totalPresent,
            'overall_percentage' => $totalDays > 0 ? round(($totalPresent / $totalDays) * 100, 2) : 0,
            'best_month' => collect($monthlyData)->sortByDesc('percentage')->first(),
            'worst_month' => collect($monthlyData)->sortBy('percentage')->first(),
        ];
        
        $academicYears = $this->getAvailableAcademicYears($student);
        
        $chartData = [
            'labels' => collect($monthlyData)->pluck('month')->toArray(),
            'data' => collect($monthlyData)->pluck('percentage')->toArray(),
        ];
        
        return $this->sendResponse([
            'academic_year' => $academicYear,
            'yearly_stats' => $yearlyStats,
            'monthly_breakdown' => $monthlyData,
            'available_academic_years' => $academicYears,
            'chart_data' => $chartData,
        ], 'Yearly attendance summary');
    }
    
    // ---------- Helper Methods ----------
    
    private function getAttendanceSummary($student)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $monthlyAttendances = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereMonth('attendance_date', $currentMonth)
            ->whereYear('attendance_date', $currentYear)
            ->get();
        
        $allAttendances = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->get();
        
        $today = Attendance::where('attendable_type', Student::class)
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
                'total' => $monthlyAttendances->count(),
                'present' => $monthlyAttendances->where('status', 'present')->count(),
                'percentage' => $monthlyAttendances->count() > 0 
                    ? round(($monthlyAttendances->where('status', 'present')->count() / $monthlyAttendances->count()) * 100, 2) 
                    : 0,
            ],
            'overall' => [
                'total' => $allAttendances->count(),
                'present' => $allAttendances->where('status', 'present')->count(),
                'percentage' => $allAttendances->count() > 0 
                    ? round(($allAttendances->where('status', 'present')->count() / $allAttendances->count()) * 100, 2) 
                    : 0,
            ],
        ];
    }
    
    private function generateMonthlyCalendar($year, $month, $attendances)
    {
        $firstDay = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startDayOfWeek = $firstDay->dayOfWeek;
        
        $calendar = [];
        $week = [];
        $weekNumber = 1;
        
        // Add empty cells for days before the first day of month
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $week[] = null;
        }
        
        // Fill in the days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $attendance = $attendances[$date->format('Y-m-d')] ?? null;
            
            $week[] = [
                'day' => $day,
                'date' => $date->toDateString(),
                'day_name' => $date->format('D'),
                'is_weekend' => $date->isSunday(),
                'status' => $attendance?->status,
                'status_text' => $attendance ? ucfirst($attendance->status) : 'Not Marked',
                'status_color' => $this->getStatusColor($attendance?->status),
                'check_in' => $attendance?->check_in_time?->format('h:i A'),
                'check_out' => $attendance?->check_out_time?->format('h:i A'),
                'remarks' => $attendance?->remarks,
            ];
            
            // End of week (Saturday) or end of month
            if ($date->dayOfWeek == 6 || $day == $daysInMonth) {
                // Pad the last week with nulls if needed
                while (count($week) < 7) {
                    $week[] = null;
                }
                $calendar[] = [
                    'week_number' => $weekNumber,
                    'days' => $week,
                ];
                $week = [];
                $weekNumber++;
            }
        }
        
        return $calendar;
    }
    
    private function getMonthlyStats($attendances, $year, $month)
    {
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $late = $attendances->where('status', 'late')->count();
        $halfDay = $attendances->where('status', 'half_day')->count();
        
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
        return $currentMonth >= 4 ? "{$currentYear}-" . ($currentYear + 1) : ($currentYear - 1) . "-{$currentYear}";
    }
    
    private function getAvailableAcademicYears($student)
    {
        $years = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->selectRaw('DISTINCT YEAR(attendance_date) as year')
            ->orderBy('year', 'asc')
            ->pluck('year');
        
        if ($years->isEmpty()) {
            return collect([$this->getCurrentAcademicYear()]);
        }
        
        $academicYears = collect();
        $minYear = $years->min();
        $maxYear = $years->max();
        for ($year = $minYear; $year <= $maxYear; $year++) {
            $academicYears->push($year . '-' . ($year + 1));
        }
        return $academicYears->unique()->sort()->values();
    }
    
    private function getStatusColor($status)
    {
        return match($status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'half_day' => 'info',
            default => 'secondary',
        };
    }
}