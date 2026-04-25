<?php
// app/Http/Controllers/Api/SuperAdmin/DashboardController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Student;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\ExamResult;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    /**
     * Get super admin dashboard statistics
     */
    public function index()
    {
        // Statistics Cards
        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => User::where('user_type', 'teacher')->count(),
            'total_staff' => Employee::count(),
            'total_parents' => User::where('user_type', 'parent')->count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            
            'today_attendance' => [
                'present' => Attendance::whereDate('attendance_date', today())
                    ->where('status', 'present')
                    ->where('attendable_type', Student::class)
                    ->count(),
                'absent' => Attendance::whereDate('attendance_date', today())
                    ->where('status', 'absent')
                    ->where('attendable_type', Student::class)
                    ->count(),
                'late' => Attendance::whereDate('attendance_date', today())
                    ->where('status', 'late')
                    ->where('attendable_type', Student::class)
                    ->count(),
                'percentage' => $this->calculateTodayAttendancePercentage(),
            ],
            
            'financial' => [
                'monthly_revenue' => Payment::whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount'),
                'yearly_revenue' => Payment::whereYear('payment_date', now()->year)
                    ->sum('amount'),
                'pending_fees' => DB::table('student_fees')
                    ->whereIn('status', ['pending', 'partial', 'overdue'])
                    ->sum('due_amount'),
                'total_collected' => Payment::sum('amount'),
            ],
            
            'academic' => [
                'total_classes' => \App\Models\Classes::count(),
                'total_sections' => \App\Models\Section::count(),
                'total_subjects' => \App\Models\Subject::count(),
                'total_exams' => \App\Models\Exam::count(),
            ],
        ];
        
        // Charts Data
        $attendanceChart = $this->getAttendanceChartData();
        $revenueChart = $this->getRevenueChartData();
        $admissionChart = $this->getAdmissionChartData();
        
        // Recent Activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'user' => $log->user?->name,
                    'action' => $log->action,
                    'module' => $log->module,
                    'description' => $log->description,
                    'ip_address' => $log->ip_address,
                    'time_ago' => $log->created_at->diffForHumans(),
                ];
            });
        
        // Upcoming Events
        $upcomingEvents = \App\Models\Event::where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(5)
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_date->format('F j, Y'),
                    'venue' => $event->venue,
                    'audience' => $event->audience,
                ];
            });
        
        // Recent Payments
        $recentPayments = Payment::with(['student.user', 'receivedBy'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'student' => $payment->student->full_name,
                    'amount' => $payment->amount,
                    'amount_formatted' => '₹ ' . number_format($payment->amount, 2),
                    'payment_date' => $payment->payment_date->format('F j, Y'),
                    'received_by' => $payment->receivedBy?->name,
                ];
            });
        
        // System Health
        $systemHealth = [
            'storage_usage' => $this->getStorageUsage(),
            'database_size' => $this->getDatabaseSize(),
            'backup_status' => $this->getBackupStatus(),
            'pending_updates' => 0,
        ];
        
        return $this->sendResponse([
            'stats' => $stats,
            'charts' => [
                'attendance' => $attendanceChart,
                'revenue' => $revenueChart,
                'admissions' => $admissionChart,
            ],
            'recent_activities' => $recentActivities,
            'upcoming_events' => $upcomingEvents,
            'recent_payments' => $recentPayments,
            'system_health' => $systemHealth,
        ], 'Dashboard data retrieved successfully');
    }
    
    private function calculateTodayAttendancePercentage()
    {
        $total = Attendance::whereDate('attendance_date', today())
            ->where('attendable_type', Student::class)
            ->count();
        
        if ($total == 0) return 0;
        
        $present = Attendance::whereDate('attendance_date', today())
            ->where('attendable_type', Student::class)
            ->where('status', 'present')
            ->count();
        
        return round(($present / $total) * 100, 2);
    }
    
    private function getAttendanceChartData()
    {
        $dates = [];
        $presentData = [];
        $absentData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dates[] = $date->format('D');
            
            $present = Attendance::whereDate('attendance_date', $date)
                ->where('status', 'present')
                ->where('attendable_type', Student::class)
                ->count();
            $absent = Attendance::whereDate('attendance_date', $date)
                ->where('status', 'absent')
                ->where('attendable_type', Student::class)
                ->count();
            
            $presentData[] = $present;
            $absentData[] = $absent;
        }
        
        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $presentData,
                    'backgroundColor' => '#28a745',
                    'borderColor' => '#28a745',
                ],
                [
                    'label' => 'Absent',
                    'data' => $absentData,
                    'backgroundColor' => '#dc3545',
                    'borderColor' => '#dc3545',
                ],
            ],
        ];
    }
    
    private function getRevenueChartData()
    {
        $months = [];
        $revenueData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            $revenue = Payment::whereMonth('payment_date', $month->month)
                ->whereYear('payment_date', $month->year)
                ->sum('amount');
            
            $revenueData[] = $revenue;
        }
        
        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenueData,
                    'borderColor' => '#007bff',
                    'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                    'fill' => true,
                ],
            ],
        ];
    }
    
    private function getAdmissionChartData()
    {
        $years = [];
        $admissionData = [];
        
        for ($i = 4; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            $years[] = $year;
            
            $admissions = Student::whereYear('admission_date', $year)->count();
            $admissionData[] = $admissions;
        }
        
        return [
            'labels' => $years,
            'datasets' => [
                [
                    'label' => 'Admissions',
                    'data' => $admissionData,
                    'backgroundColor' => '#17a2b8',
                    'borderColor' => '#17a2b8',
                ],
            ],
        ];
    }
    
    private function getStorageUsage()
    {
        $path = storage_path('app');
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;
        
        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => round(($used / $total) * 100, 2),
        ];
    }
    
    private function getDatabaseSize()
    {
        $databasePath = database_path('database.sqlite');
        if (file_exists($databasePath)) {
            $size = filesize($databasePath);
            return $this->formatBytes($size);
        }
        return 'N/A';
    }
    
    private function getBackupStatus()
    {
        $backupPath = storage_path('app/backups');
        if (!file_exists($backupPath)) {
            return ['status' => 'no_backups', 'message' => 'No backups found'];
        }
        
        $backups = glob($backupPath . '/*.sql');
        $latestBackup = !empty($backups) ? max($backups) : null;
        
        if ($latestBackup) {
            $lastBackupTime = filemtime($latestBackup);
            $daysSince = round((time() - $lastBackupTime) / (60 * 60 * 24));
            
            return [
                'status' => $daysSince > 7 ? 'stale' : 'good',
                'last_backup' => date('F j, Y', $lastBackupTime),
                'days_ago' => $daysSince,
            ];
        }
        
        return ['status' => 'no_backups', 'message' => 'No backups found'];
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}