<?php
// app/Http/Controllers/Employee/DashboardController.php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\Task;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        
        // Today's attendance
        $todayAttendance = Attendance::where('attendable_type', 'App\Models\Employee')
                                     ->where('attendable_id', $employee->id)
                                     ->whereDate('attendance_date', today())
                                     ->first();
        
        // Leave balance
        $leaveBalance = [
            'total' => 20, // Configure based on employee type
            'used' => LeaveApplication::where('user_id', Auth::id())
                                      ->where('status', 'approved')
                                      ->whereYear('start_date', now()->year)
                                      ->sum('total_days'),
            'remaining' => 20 - LeaveApplication::where('user_id', Auth::id())
                                               ->where('status', 'approved')
                                               ->whereYear('start_date', now()->year)
                                               ->sum('total_days')
        ];
        
        // Pending tasks
        $pendingTasks = Task::where('assigned_to', Auth::id())
                            ->where('status', 'pending')
                            ->latest()
                            ->take(5)
                            ->get();
        
        // Recent notifications
        $notifications = Notification::where('user_id', Auth::id())
                                     ->latest()
                                     ->take(10)
                                     ->get();
        
        // Monthly attendance
        $monthlyAttendance = Attendance::where('attendable_type', 'App\Models\Employee')
                                       ->where('attendable_id', $employee->id)
                                       ->whereMonth('attendance_date', now()->month)
                                       ->whereYear('attendance_date', now()->year)
                                       ->get();
        
        $attendanceStats = [
            'present' => $monthlyAttendance->where('status', 'present')->count(),
            'absent' => $monthlyAttendance->where('status', 'absent')->count(),
            'late' => $monthlyAttendance->where('status', 'late')->count(),
            'half_day' => $monthlyAttendance->where('status', 'half_day')->count()
        ];
        
        // Upcoming leaves
        $upcomingLeaves = LeaveApplication::where('user_id', Auth::id())
                                          ->where('start_date', '>', today())
                                          ->where('status', 'approved')
                                          ->latest()
                                          ->get();
        
        return view('employee.dashboard', compact(
            'todayAttendance', 'leaveBalance', 'pendingTasks',
            'notifications', 'attendanceStats', 'upcomingLeaves'
        ));
    }
    
    // Mark attendance
    public function markAttendance(Request $request)
    {
        $employee = Auth::user()->employee;
        
        $attendance = Attendance::updateOrCreate(
            [
                'attendable_type' => 'App\Models\Employee',
                'attendable_id' => $employee->id,
                'attendance_date' => today()
            ],
            [
                'status' => $request->status ?? 'present',
                'check_in_time' => now(),
                'marked_by' => Auth::id()
            ]
        );
        
        return response()->json(['success' => true, 'attendance' => $attendance]);
    }
}