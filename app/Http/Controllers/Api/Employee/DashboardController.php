<?php
// app/Http/Controllers/Api/Employee/DashboardController.php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\Task;
use App\Models\SalaryPayment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    /**
     * Get employee dashboard data
     */
    public function index()
    {
        $employee = Auth::user()->employee;
        $user = Auth::user();
        
        // Today's attendance
        $todayAttendance = Attendance::where('attendable_type', 'App\Models\Employee')
            ->where('attendable_id', $employee->id)
            ->whereDate('attendance_date', today())
            ->first();
        
        // Monthly attendance summary
        $monthlyAttendances = Attendance::where('attendable_type', 'App\Models\Employee')
            ->where('attendable_id', $employee->id)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->get();
        
        $attendanceStats = [
            'today' => [
                'status' => $todayAttendance?->status ?? 'not_marked',
                'status_text' => $todayAttendance ? ucfirst($todayAttendance->status) : 'Not Marked',
                'check_in' => $todayAttendance?->check_in_time?->format('h:i A'),
                'check_out' => $todayAttendance?->check_out_time?->format('h:i A'),
            ],
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
        
        // Leave balance
        $leaveBalance = $this->calculateLeaveBalance($employee);
        
        // Pending tasks
        $pendingTasks = Task::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(function($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'priority' => $task->priority,
                    'priority_color' => $this->getPriorityColor($task->priority),
                    'due_date' => $task->due_date?->format('F j, Y'),
                    'due_date_remaining' => $task->due_date ? now()->diffInDays($task->due_date, false) : null,
                    'status' => $task->status,
                    'status_text' => ucfirst(str_replace('_', ' ', $task->status)),
                ];
            });
        
        // Recent notifications
        $recentNotifications = Notification::where('user_id', $user->id)
            ->orWhereNull('user_id')
            ->latest()
            ->take(5)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });
        
        // Recent salary payment
        $lastSalary = SalaryPayment::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->latest()
            ->first();
        
        // Upcoming leave requests
        $upcomingLeaves = LeaveApplication::where('user_id', $user->id)
            ->where('start_date', '>=', today())
            ->where('status', 'approved')
            ->orderBy('start_date')
            ->take(3)
            ->get()
            ->map(function($leave) {
                return [
                    'id' => $leave->id,
                    'type' => $leave->leaveType->name,
                    'start_date' => $leave->start_date->format('F j, Y'),
                    'end_date' => $leave->end_date->format('F j, Y'),
                    'days' => $leave->total_days,
                    'status' => $leave->status,
                ];
            });
        
        // Quick stats
        $quickStats = [
            'total_present_this_month' => $monthlyAttendances->where('status', 'present')->count(),
            'total_absent_this_month' => $monthlyAttendances->where('status', 'absent')->count(),
            'total_late_this_month' => $monthlyAttendances->where('status', 'late')->count(),
            'pending_tasks' => Task::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->count(),
            'approved_leaves' => LeaveApplication::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('total_days'),
            'pending_leaves' => LeaveApplication::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'unread_notifications' => Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count(),
        ];
        
        // Recent activities
        $recentActivities = $this->getRecentActivities($employee);
        
        $data = [
            'employee' => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_photo' => $user->profile_photo_url,
                'department' => $employee->department,
                'designation' => $employee->designation,
                'joining_date' => $employee->joining_date->format('F j, Y'),
                'employment_type' => $employee->employment_type,
                'employment_type_text' => ucfirst(str_replace('_', ' ', $employee->employment_type)),
                'years_of_service' => $employee->joining_date->diffInYears(now()),
            ],
            'attendance_stats' => $attendanceStats,
            'leave_balance' => $leaveBalance,
            'pending_tasks' => $pendingTasks,
            'recent_notifications' => $recentNotifications,
            'last_salary' => $lastSalary ? [
                'month' => $lastSalary->payment_month,
                'amount' => $lastSalary->net_salary,
                'amount_formatted' => '₹ ' . number_format($lastSalary->net_salary, 2),
                'payment_date' => $lastSalary->payment_date->format('F j, Y'),
            ] : null,
            'upcoming_leaves' => $upcomingLeaves,
            'quick_stats' => $quickStats,
            'recent_activities' => $recentActivities,
        ];
        
        return $this->sendResponse($data, 'Dashboard data retrieved successfully');
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
    
    private function calculateLeaveBalance($employee)
    {
        $totalLeaves = 20; // Annual leaves
        $carryForward = 0; // Can be configured
        
        $usedLeaves = LeaveApplication::where('user_id', $employee->user_id)
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');
        
        $pendingLeaves = LeaveApplication::where('user_id', $employee->user_id)
            ->where('status', 'pending')
            ->sum('total_days');
        
        return [
            'total' => $totalLeaves + $carryForward,
            'used' => $usedLeaves,
            'pending' => $pendingLeaves,
            'remaining' => ($totalLeaves + $carryForward) - $usedLeaves,
            'used_percentage' => $totalLeaves > 0 ? round(($usedLeaves / $totalLeaves) * 100, 2) : 0,
        ];
    }
    
    private function getPriorityColor($priority)
    {
        return match($priority) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            default => 'secondary'
        };
    }
    
    private function getRecentActivities($employee)
    {
        $activities = [];
        
        // Recent attendances
        $recentAttendances = Attendance::where('attendable_type', 'App\Models\Employee')
            ->where('attendable_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();
        
        foreach ($recentAttendances as $attendance) {
            $activities[] = [
                'type' => 'attendance',
                'title' => 'Attendance Marked',
                'description' => "You were marked " . ucfirst($attendance->status) . " on " . $attendance->attendance_date->format('F j, Y'),
                'time_ago' => $attendance->created_at->diffForHumans(),
            ];
        }
        
        // Recent leave applications
        $recentLeaves = LeaveApplication::where('user_id', $employee->user_id)
            ->latest()
            ->take(5)
            ->get();
        
        foreach ($recentLeaves as $leave) {
            $activities[] = [
                'type' => 'leave',
                'title' => 'Leave Application',
                'description' => ucfirst($leave->status) . " leave application for " . $leave->total_days . " days",
                'time_ago' => $leave->created_at->diffForHumans(),
            ];
        }
        
        // Recent tasks
        $recentTasks = Task::where('assigned_to', $employee->user_id)
            ->latest()
            ->take(5)
            ->get();
        
        foreach ($recentTasks as $task) {
            $activities[] = [
                'type' => 'task',
                'title' => 'Task ' . ucfirst($task->status),
                'description' => $task->title . " - " . ucfirst(str_replace('_', ' ', $task->status)),
                'time_ago' => $task->updated_at->diffForHumans(),
            ];
        }
        
        // Sort by time
        usort($activities, function($a, $b) {
            return strtotime($b['time_ago']) - strtotime($a['time_ago']);
        });
        
        return array_slice($activities, 0, 10);
    }
}