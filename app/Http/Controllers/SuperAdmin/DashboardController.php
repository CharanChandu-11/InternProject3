<?php
// app/Http/Controllers/SuperAdmin/DashboardController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\StudentFee;
use App\Models\Classes;
use App\Models\Exam;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\ActivityLog;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics Cards
        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => User::where('user_type', 'teacher')->count(),
            'total_employees' => Employee::count(),
            'total_parents' => User::where('user_type', 'parent')->count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_classes' => Classes::count(),
            'total_exams' => Exam::count(),
            'total_books' => Book::count(),
            'books_issued' => BookIssue::where('status', 'issued')->count(),
        ];
        
        // Financial Statistics
        $financial = [
            'monthly_revenue' => Payment::whereMonth('payment_date', Carbon::now()->month)
                ->whereYear('payment_date', Carbon::now()->year)
                ->sum('amount'),
            'yearly_revenue' => Payment::whereYear('payment_date', Carbon::now()->year)->sum('amount'),
            'pending_fees' => StudentFee::whereIn('status', ['pending', 'partial', 'overdue'])->sum('due_amount'),
            'total_collected' => Payment::sum('amount'),
        ];
        
        // Today's Attendance
        $todayAttendance = [
            'present' => Attendance::whereDate('attendance_date', Carbon::today())
                ->where('status', 'present')
                ->where('attendable_type', Student::class)
                ->count(),
            'absent' => Attendance::whereDate('attendance_date', Carbon::today())
                ->where('status', 'absent')
                ->where('attendable_type', Student::class)
                ->count(),
            'late' => Attendance::whereDate('attendance_date', Carbon::today())
                ->where('status', 'late')
                ->where('attendable_type', Student::class)
                ->count(),
            'total' => Attendance::whereDate('attendance_date', Carbon::today())
                ->where('attendable_type', Student::class)
                ->count(),
        ];
        
        if ($todayAttendance['total'] > 0) {
            $todayAttendance['present_percent'] = round(($todayAttendance['present'] / $todayAttendance['total']) * 100, 2);
            $todayAttendance['absent_percent'] = round(($todayAttendance['absent'] / $todayAttendance['total']) * 100, 2);
        } else {
            $todayAttendance['present_percent'] = 0;
            $todayAttendance['absent_percent'] = 0;
        }
        
        // Chart Data - Monthly Revenue
        $monthlyRevenue = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $revenue = Payment::whereMonth('payment_date', $month->month)
                ->whereYear('payment_date', $month->year)
                ->sum('amount');
            $monthlyRevenue[] = $revenue;
        }
        
        // Chart Data - Monthly Attendance
        $monthlyAttendance = [];
        $attendanceLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $attendanceLabels[] = $month->format('M Y');
            $present = Attendance::whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->where('status', 'present')
                ->where('attendable_type', Student::class)
                ->count();
            $absent = Attendance::whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->where('status', 'absent')
                ->where('attendable_type', Student::class)
                ->count();
            $monthlyAttendance[] = [
                'present' => $present,
                'absent' => $absent,
            ];
        }
        
        // Chart Data - Student Enrollment by Class
        $classEnrollment = [];
        $classLabels = [];
        $classes = Classes::withCount('students')->get();
        foreach ($classes as $class) {
            $classLabels[] = $class->name;
            $classEnrollment[] = $class->students_count;
        }
        
        // Recent Activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($log) {
                return [
                    'user' => $log->user?->name ?? 'System',
                    'action' => $log->action,
                    'module' => $log->module,
                    'description' => $log->description,
                    'time_ago' => $log->created_at->diffForHumans(),
                    'created_at' => $log->created_at->format('h:i A'),
                ];
            });
        
        // Recent Payments
        $recentPayments = Payment::with(['student.user', 'receivedBy'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($payment) {
                return [
                    'payment_number' => $payment->payment_number,
                    'student' => $payment->student->user->name,
                    'admission_number' => $payment->student->admission_number,
                    'amount' => $payment->amount,
                    'amount_formatted' => '₹ ' . number_format($payment->amount, 2),
                    'payment_date' => $payment->payment_date->format('d-m-Y'),
                    'received_by' => $payment->receivedBy?->name ?? 'System',
                ];
            });
        
        // Upcoming Events
        $upcomingEvents = Event::where('start_date', '>=', Carbon::today())
            ->orderBy('start_date')
            ->take(5)
            ->get()
            ->map(function($event) {
                return [
                    'title' => $event->title,
                    'date' => $event->start_date->format('d-m-Y'),
                    'venue' => $event->venue,
                    'days_left' => Carbon::today()->diffInDays($event->start_date),
                ];
            });
        
        // Recent Students
        $recentStudents = Student::with(['user', 'class'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($student) {
                return [
                    'name' => $student->user->name,
                    'admission_number' => $student->admission_number,
                    'class' => $student->class->name,
                    'admission_date' => $student->admission_date->format('d-m-Y'),
                ];
            });
        
        // System Health
        $systemHealth = $this->getSystemHealth();
        
        return view('super-admin.dashboard', compact(
            'stats',
            'financial',
            'todayAttendance',
            'monthlyLabels',
            'monthlyRevenue',
            'attendanceLabels',
            'monthlyAttendance',
            'classLabels',
            'classEnrollment',
            'recentActivities',
            'recentPayments',
            'upcomingEvents',
            'recentStudents',
            'systemHealth'
        ));
    }
    
    private function getSystemHealth()
    {
        // Database size
        $databasePath = database_path('database.sqlite');
        $databaseSize = file_exists($databasePath) ? $this->formatBytes(filesize($databasePath)) : 'N/A';
        
        // Storage usage
        $storagePath = storage_path('app');
        $total = disk_total_space($storagePath);
        $free = disk_free_space($storagePath);
        $used = $total - $free;
        $storageUsage = [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percent' => round(($used / $total) * 100, 2),
        ];
        
        // Cache status
        $cacheDrivers = [
            'config' => app()->configurationIsCached(),
            'route' => app()->routesAreCached(),
        ];
        
        // Backup status
        $backupPath = storage_path('app/backups');
        $backupExists = file_exists($backupPath) && count(glob($backupPath . '/*.sql')) > 0;
        $lastBackup = null;
        if ($backupExists) {
            $backups = glob($backupPath . '/*.sql');
            $latestBackup = max($backups);
            $lastBackup = date('d-m-Y H:i:s', filemtime($latestBackup));
        }
        
        return [
            'database_size' => $databaseSize,
            'storage_usage' => $storageUsage,
            'cache_status' => $cacheDrivers,
            'backup_exists' => $backupExists,
            'last_backup' => $lastBackup,
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
        ];
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