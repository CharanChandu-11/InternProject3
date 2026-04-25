<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => User::where('user_type', 'teacher')->count(),
            'total_staff' => Employee::count(),
            'total_parents' => User::where('user_type', 'parent')->count(),
            
            'today_present' => Attendance::whereDate('attendance_date', today())
                ->where('status', 'present')
                ->where('attendable_type', Student::class)
                ->count(),
            
            'today_absent' => Attendance::whereDate('attendance_date', today())
                ->where('status', 'absent')
                ->where('attendable_type', Student::class)
                ->count(),
            
            'monthly_revenue' => Payment::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            
            'pending_fees' => DB::table('student_fees')
                ->whereIn('status', ['pending', 'partial'])
                ->sum('due_amount'),
        ];
        
        $recentStudents = Student::with('user')->latest()->take(5)->get();
        $recentPayments = Payment::with('student.user')->latest()->take(5)->get();
        $upcomingEvents = \App\Models\Event::where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(5)
            ->get();
        
        return view('admin.dashboard', compact('stats', 'recentStudents', 'recentPayments', 'upcomingEvents'));
    }
}