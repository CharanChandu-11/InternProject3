<?php
// app/Http/Controllers/Api/DashboardController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\EventResource;
use App\Http\Resources\NotificationResource;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    /**
     * Get dashboard statistics based on user type
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $stats = [];

        switch ($user->user_type) {
            case 'super_admin':
                $stats = $this->getSuperAdminStats();
                break;
            case 'admin':
                $stats = $this->getAdminStats();
                break;
            case 'teacher':
                $stats = $this->getTeacherStats();
                break;
            case 'student':
                $stats = $this->getStudentStats();
                break;
            case 'parent':
                $stats = $this->getParentStats();
                break;
            case 'employee':
                $stats = $this->getEmployeeStats();
                break;
        }

        return $this->sendResponse($stats, 'Dashboard statistics retrieved successfully');
    }

    /**
     * Get notifications for the current user
     */
    public function notifications(Request $request)
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->where('user_id', $user->id)
            ->orWhere('user_id', null) // Broadcast notifications
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return $this->sendPaginatedResponse(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully'
        );
    }

    /**
     * Get upcoming events
     */
    public function upcomingEvents(Request $request)
    {
        $user = Auth::user();
        
        $query = Event::where('start_date', '>=', now())
            ->orderBy('start_date');

        // Filter based on user type
        switch ($user->user_type) {
            case 'student':
                $query->whereIn('audience', ['all', 'students']);
                break;
            case 'teacher':
                $query->whereIn('audience', ['all', 'teachers']);
                break;
            case 'parent':
                $query->whereIn('audience', ['all', 'parents']);
                break;
            case 'employee':
                $query->whereIn('audience', ['all', 'employees']);
                break;
            default:
                $query->where('audience', 'all');
        }

        $events = $query->take($request->limit ?? 10)->get();

        return $this->sendResponse(
            EventResource::collection($events),
            'Upcoming events retrieved successfully'
        );
    }

    /**
     * Super Admin Statistics
     */
    private function getSuperAdminStats()
    {
        return [
            'total_students' => Student::count(),
            'total_teachers' => User::where('user_type', 'teacher')->count(),
            'total_employees' => \App\Models\Employee::count(),
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
            
            'recent_activities' => \App\Models\ActivityLog::with('user')
                ->latest()
                ->take(10)
                ->get()
                ->map(function($log) {
                    return [
                        'user' => $log->user?->name,
                        'action' => $log->action,
                        'module' => $log->module,
                        'description' => $log->description,
                        'time_ago' => $log->created_at->diffForHumans(),
                    ];
                }),
        ];
    }

    /**
     * Admin Statistics
     */
    private function getAdminStats()
    {
        return [
            'total_students' => Student::count(),
            'total_teachers' => User::where('user_type', 'teacher')->count(),
            'total_staff' => \App\Models\Employee::count(),
            
            'today_attendance' => [
                'present' => Attendance::whereDate('attendance_date', today())
                    ->where('status', 'present')
                    ->where('attendable_type', Student::class)
                    ->count(),
                'absent' => Attendance::whereDate('attendance_date', today())
                    ->where('status', 'absent')
                    ->where('attendable_type', Student::class)
                    ->count(),
                'percentage' => $this->calculateTodayAttendancePercentage(),
            ],
            
            'financial' => [
                'monthly_collection' => Payment::whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount'),
                'pending_fees' => DB::table('student_fees')
                    ->whereIn('status', ['pending', 'partial'])
                    ->sum('due_amount'),
            ],
            
            'library' => [
                'total_books' => \App\Models\Book::count(),
                'books_issued' => \App\Models\BookIssue::where('status', 'issued')->count(),
                'books_available' => \App\Models\Book::sum('available_quantity'),
            ],
        ];
    }

    /**
     * Teacher Statistics
     */
    private function getTeacherStats()
    {
        $teacher = Auth::user();
        $classIds = \App\Models\ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');
        
        return [
            'my_classes' => \App\Models\ClassSubject::where('teacher_id', $teacher->id)
                ->with('class', 'subject')
                ->get()
                ->groupBy('class_id')
                ->map(function($items) {
                    return [
                        'class_name' => $items->first()->class->name,
                        'subjects' => $items->pluck('subject.name'),
                        'students_count' => Student::where('class_id', $items->first()->class_id)->count(),
                    ];
                })
                ->values(),
            
            'today_attendance' => [
                'total' => $studentIds->count(),
                'present' => Attendance::whereDate('attendance_date', today())
                    ->whereIn('attendable_id', $studentIds)
                    ->where('attendable_type', Student::class)
                    ->where('status', 'present')
                    ->count(),
                'absent' => Attendance::whereDate('attendance_date', today())
                    ->whereIn('attendable_id', $studentIds)
                    ->where('attendable_type', Student::class)
                    ->where('status', 'absent')
                    ->count(),
                'percentage' => $this->calculateTeacherAttendancePercentage($studentIds),
            ],
            
            'pending_homework' => \App\Models\Homework::where('teacher_id', $teacher->id)
                ->where('submission_date', '>=', now())
                ->where('status', 'active')
                ->count(),
            
            'pending_homework_to_check' => \App\Models\HomeworkSubmission::whereHas('homework', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('status', 'submitted')
            ->count(),
            
            'upcoming_exams' => \App\Models\ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereHas('exam', function($q) {
                $q->where('start_date', '>=', now());
            })
            ->count(),
            
            'total_students' => $studentIds->count(),
        ];
    }

    /**
     * Student Statistics
     */
    private function getStudentStats()
    {
        $student = Auth::user()->student;
        
        $monthlyAttendance = $student->attendances()
            ->whereMonth('attendance_date', now()->month)
            ->get();
        
        $pendingHomework = \App\Models\Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('submission_date', '>=', now())
            ->where('status', 'active')
            ->count();
        
        $overdueHomework = \App\Models\Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('submission_date', '<', now())
            ->whereDoesntHave('submissions', function($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->count();
        
        $upcomingExams = \App\Models\ExamSchedule::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->whereHas('exam', function($q) {
                $q->where('start_date', '>=', now());
            })
            ->count();
        
        $pendingFees = $student->fees()
            ->whereIn('status', ['pending', 'partial'])
            ->sum('due_amount');
        
        return [
            'attendance' => [
                'today' => $student->attendances()
                    ->whereDate('attendance_date', today())
                    ->first()?->status ?? 'not_marked',
                'monthly_percentage' => $this->calculateStudentMonthlyAttendance($student),
                'total_present' => $monthlyAttendance->where('status', 'present')->count(),
                'total_absent' => $monthlyAttendance->where('status', 'absent')->count(),
                'total_late' => $monthlyAttendance->where('status', 'late')->count(),
            ],
            
            'homework' => [
                'pending' => $pendingHomework,
                'overdue' => $overdueHomework,
                'submitted' => $student->homeworkSubmissions()->where('status', 'submitted')->count(),
                'graded' => $student->homeworkSubmissions()->where('status', 'graded')->count(),
            ],
            
            'exams' => [
                'upcoming' => $upcomingExams,
                'completed' => $student->examResults()->count(),
                'average_percentage' => $student->average_percentage,
                'rank' => $student->current_rank,
            ],
            
            'fees' => [
                'total_due' => $pendingFees,
                'total_due_formatted' => '₹ ' . number_format($pendingFees, 2),
                'last_payment' => $student->payments()->latest()->first(),
            ],
            
            'library' => [
                'books_issued' => $student->bookIssues()->where('status', 'issued')->count(),
                'books_overdue' => $student->bookIssues()->where('status', 'overdue')->count(),
            ],
        ];
    }

    /**
     * Parent Statistics
     */
    private function getParentStats()
    {
        $parent = Auth::user()->parent;
        $children = $parent->children;
        
        $totalChildren = $children->count();
        $presentToday = 0;
        $totalDue = 0;
        
        foreach ($children as $child) {
            $today = $child->attendances()->whereDate('attendance_date', today())->first();
            if ($today && $today->status == 'present') {
                $presentToday++;
            }
            $totalDue += $child->due_fees;
        }
        
        return [
            'total_children' => $totalChildren,
            'children' => $children->map(function($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->full_name,
                    'class' => $child->class_name,
                    'section' => $child->section_name,
                    'attendance_percentage' => $child->attendance_percentage,
                    'pending_fees' => $child->due_fees,
                    'pending_fees_formatted' => '₹ ' . number_format($child->due_fees, 2),
                    'average_marks' => $child->average_percentage . '%',
                ];
            }),
            'attendance_summary' => [
                'present_today' => $presentToday,
                'absent_today' => $totalChildren - $presentToday,
                'average_attendance' => $totalChildren > 0 
                    ? round($children->avg('attendance_percentage'), 2) 
                    : 0,
            ],
            'fee_summary' => [
                'total_due' => $totalDue,
                'total_due_formatted' => '₹ ' . number_format($totalDue, 2),
            ],
            'upcoming_events' => Event::where('start_date', '>=', now())
                ->whereIn('audience', ['all', 'parents'])
                ->count(),
            'unread_messages' => \App\Models\Message::where('receiver_id', $parent->user_id)
                ->where('is_read', false)
                ->count(),
        ];
    }

    /**
     * Employee Statistics
     */
    private function getEmployeeStats()
    {
        $employee = Auth::user()->employee;
        
        $monthlyAttendance = $employee->attendances()
            ->whereMonth('attendance_date', now()->month)
            ->get();
        
        $pendingLeaves = $employee->leaveApplications()
            ->where('status', 'pending')
            ->count();
        
        $approvedLeaves = $employee->leaveApplications()
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');
        
        return [
            'attendance' => [
                'today' => $employee->attendances()
                    ->whereDate('attendance_date', today())
                    ->first()?->status ?? 'not_marked',
                'monthly_percentage' => $this->calculateEmployeeMonthlyAttendance($employee),
                'total_present' => $monthlyAttendance->where('status', 'present')->count(),
                'total_absent' => $monthlyAttendance->where('status', 'absent')->count(),
                'total_late' => $monthlyAttendance->where('status', 'late')->count(),
            ],
            
            'leave' => [
                'pending' => $pendingLeaves,
                'approved_this_year' => $approvedLeaves,
                'remaining_leaves' => 20 - $approvedLeaves, // Assuming 20 total leaves
            ],
            
            'salary' => [
                'current_month' => $employee->salaryPayments()
                    ->where('payment_month', now()->format('Y-m'))
                    ->first(),
                'total_earned' => $employee->salaryPayments()
                    ->where('status', 'paid')
                    ->sum('net_salary'),
            ],
            
            'tasks' => Task::where('assigned_to', $employee->user_id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
        ];
    }

    /**
     * Helper methods for calculations
     */
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

    private function calculateTeacherAttendancePercentage($studentIds)
    {
        $total = Attendance::whereDate('attendance_date', today())
            ->whereIn('attendable_id', $studentIds)
            ->where('attendable_type', Student::class)
            ->count();
        
        if ($total == 0) return 0;
        
        $present = Attendance::whereDate('attendance_date', today())
            ->whereIn('attendable_id', $studentIds)
            ->where('attendable_type', Student::class)
            ->where('status', 'present')
            ->count();
        
        return round(($present / $total) * 100, 2);
    }

    private function calculateStudentMonthlyAttendance($student)
    {
        $total = $student->attendances()
            ->whereMonth('attendance_date', now()->month)
            ->count();
        
        if ($total == 0) return 0;
        
        $present = $student->attendances()
            ->whereMonth('attendance_date', now()->month)
            ->where('status', 'present')
            ->count();
        
        return round(($present / $total) * 100, 2);
    }

    private function calculateEmployeeMonthlyAttendance($employee)
    {
        $total = $employee->attendances()
            ->whereMonth('attendance_date', now()->month)
            ->count();
        
        if ($total == 0) return 0;
        
        $present = $employee->attendances()
            ->whereMonth('attendance_date', now()->month)
            ->where('status', 'present')
            ->count();
        
        return round(($present / $total) * 100, 2);
    }
}