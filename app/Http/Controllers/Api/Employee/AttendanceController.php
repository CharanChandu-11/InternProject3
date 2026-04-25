<?php
// app/Http/Controllers/Api/Employee/AttendanceController.php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends BaseController
{
    /**
     * Get employee attendance records
     */
    public function index(Request $request)
    {
        $employee = Auth::user()->employee;
        
        $query = Attendance::where('attendable_type', 'App\Models\Employee')
            ->where('attendable_id', $employee->id);
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }
        
        // Filter by month
        if ($request->has('month')) {
            $query->whereMonth('attendance_date', $request->month);
        }
        
        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('attendance_date', $request->year);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate($request->per_page ?? 30);
        
        // Summary
        $monthlyAttendances = Attendance::where('attendable_type', 'App\Models\Employee')
            ->where('attendable_id', $employee->id)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->get();
        
        $summary = [
            'today' => Attendance::where('attendable_type', 'App\Models\Employee')
                ->where('attendable_id', $employee->id)
                ->whereDate('attendance_date', today())
                ->first(),
            'monthly' => [
                'total_days' => $monthlyAttendances->count(),
                'present' => $monthlyAttendances->where('status', 'present')->count(),
                'absent' => $monthlyAttendances->where('status', 'absent')->count(),
                'late' => $monthlyAttendances->where('status', 'late')->count(),
                'percentage' => $monthlyAttendances->count() > 0 
                    ? round(($monthlyAttendances->where('status', 'present')->count() / $monthlyAttendances->count()) * 100, 2) 
                    : 0,
            ],
            'yearly' => [
                'percentage' => $this->getYearlyAttendancePercentage($employee),
            ],
        ];
        
        return $this->sendResponse([
            'summary' => $summary,
            'attendances' => AttendanceResource::collection($attendances),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ], 'Attendance records retrieved successfully');
    }
    
    /**
     * Mark attendance
     */
    public function mark(Request $request)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,half_day',
            'remarks' => 'nullable|string|max:255',
        ]);
        
        $employee = Auth::user()->employee;
        
        // Check if already marked for today
        $existingAttendance = Attendance::where('attendable_type', 'App\Models\Employee')
            ->where('attendable_id', $employee->id)
            ->whereDate('attendance_date', today())
            ->first();
        
        if ($existingAttendance) {
            return $this->sendError('Attendance already marked for today', [], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $attendance = Attendance::create([
                'attendable_type' => 'App\Models\Employee',
                'attendable_id' => $employee->id,
                'attendance_date' => today(),
                'status' => $request->status,
                'check_in_time' => $request->status == 'present' ? now() : null,
                'remarks' => $request->remarks,
                'marked_by' => Auth::id(),
            ]);
            
            DB::commit();
            
            return $this->sendResponse(
                new AttendanceResource($attendance),
                'Attendance marked successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to mark attendance: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Get attendance summary
     */
    public function summary(Request $request)
    {
        $employee = Auth::user()->employee;
        
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;
        
        // Monthly calendar
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $calendar = [];
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $attendance = Attendance::where('attendable_type', 'App\Models\Employee')
                ->where('attendable_id', $employee->id)
                ->whereDate('attendance_date', $date)
                ->first();
            
            $calendar[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'day_name' => $date->format('l'),
                'status' => $attendance?->status ?? 'not_marked',
                'status_text' => $attendance ? ucfirst($attendance->status) : 'Not Marked',
                'is_weekend' => $date->isWeekend(),
            ];
        }
        
        // Monthly summary
        $monthlyAttendances = Attendance::where('attendable_type', 'App\Models\Employee')
            ->where('attendable_id', $employee->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get();
        
        // Yearly summary
        $yearlySummary = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly = Attendance::where('attendable_type', 'App\Models\Employee')
                ->where('attendable_id', $employee->id)
                ->whereMonth('attendance_date', $m)
                ->whereYear('attendance_date', $year)
                ->get();
            
            $total = $monthly->count();
            $present = $monthly->where('status', 'present')->count();
            
            $yearlySummary[] = [
                'month' => Carbon::createFromDate($year, $m, 1)->format('M'),
                'total_days' => $total,
                'present' => $present,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ];
        }
        
        return $this->sendResponse([
            'monthly' => [
                'year' => $year,
                'month' => Carbon::createFromDate($year, $month, 1)->format('F'),
                'total_days' => $monthlyAttendances->count(),
                'present' => $monthlyAttendances->where('status', 'present')->count(),
                'absent' => $monthlyAttendances->where('status', 'absent')->count(),
                'late' => $monthlyAttendances->where('status', 'late')->count(),
                'percentage' => $monthlyAttendances->count() > 0 
                    ? round(($monthlyAttendances->where('status', 'present')->count() / $monthlyAttendances->count()) * 100, 2) 
                    : 0,
                'calendar' => $calendar,
            ],
            'yearly' => $yearlySummary,
            'overall_percentage' => $this->getYearlyAttendancePercentage($employee),
        ], 'Attendance summary retrieved successfully');
    }
    
    private function getYearlyAttendancePercentage($employee)
    {
        $yearlyAttendances = Attendance::where('attendable_type', 'App\Models\Employee')
            ->where('attendable_id', $employee->id)
            ->whereYear('attendance_date', now()->year)
            ->get();
        
        $totalDays = $yearlyAttendances->count();
        $presentDays = $yearlyAttendances->where('status', 'present')->count();
        
        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;
    }
}