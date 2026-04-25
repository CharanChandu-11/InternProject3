<?php
// app/Http/Controllers/Student/DashboardController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Homework;
use App\Models\ExamSchedule;
use App\Models\ExamResult;
use App\Models\Event;
use App\Models\StudentFee;
use App\Models\BookIssue;
use App\Models\Timetable;
use App\Models\StudentTransport;
use App\Models\HostelAllocation;
use App\Models\Announcement;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display student dashboard
     */
    public function index()
    {
        $student = Auth::user()->student;
        
        // Today's attendance
        $todayAttendance = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->whereDate('attendance_date', today())
            ->first();
        
        // Today's timetable
        $today = strtolower(now()->format('l'));
        $todayTimetable = Timetable::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('day_of_week', $today)
            ->with(['subject', 'teacher', 'timeSlot'])
            ->orderBy('time_slot_id')
            ->get();
        
        // Current class (based on current time)
        $currentTime = now()->format('H:i:s');
        $currentClass = null;
        $nextClass = null;
        
        foreach ($todayTimetable as $class) {
            if ($class->timeSlot->start_time <= $currentTime && $class->timeSlot->end_time >= $currentTime) {
                $currentClass = $class;
            } elseif ($class->timeSlot->start_time > $currentTime && !$nextClass) {
                $nextClass = $class;
                break;
            }
        }
        
        // Pending homework
        $pendingHomework = Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('submission_date', '>=', today())
            ->where('status', 'active')
            ->with(['subject', 'teacher'])
            ->orderBy('submission_date')
            ->get();
        
        // Homework submission status
        $homeworkSubmissions = $student->homeworkSubmissions()
            ->get()
            ->keyBy('homework_id');
        
        $pendingHomeworkData = [];
        foreach ($pendingHomework as $homework) {
            $submission = $homeworkSubmissions[$homework->id] ?? null;
            $pendingHomeworkData[] = [
                'homework' => $homework,
                'submitted' => !is_null($submission),
                'submitted_at' => $submission?->submitted_at,
                'status' => $submission?->status,
            ];
        }
        
        // Upcoming exams
        $upcomingExams = ExamSchedule::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->whereHas('exam', function($q) {
                $q->where('start_date', '>=', today());
            })
            ->with(['exam', 'subject'])
            ->orderBy('exam_date')
            ->take(5)
            ->get();
        
        // Recent exam results
        $recentResults = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->latest()
            ->take(5)
            ->get();
        
        // Fee status
        $feeStatus = StudentFee::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->with(['feeStructure.feeCategory'])
            ->get();
        
        $totalDue = $student->fees()->whereIn('status', ['pending', 'partial'])->sum('due_amount');
        
        // Library status
        $libraryBooks = BookIssue::where('issuable_type', 'App\Models\Student')
            ->where('issuable_id', $student->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->with('book')
            ->get();
        
        // Transport details
        $transport = StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->with(['route', 'stop'])
            ->first();
        
        // Hostel details
        $hostel = HostelAllocation::where('student_id', $student->id)
            ->where('status', 'active')
            ->with(['room.hostel'])
            ->first();
        
        // Upcoming events
        $upcomingEvents = Event::where('start_date', '>=', today())
            ->whereIn('audience', ['all', 'students'])
            ->orderBy('start_date')
            ->take(5)
            ->get();
        
        // Recent notifications
        $recentNotifications = Notification::latest()
            ->take(5)
            ->get();

        // Announcements (published, not expired, relevant to students)
        $announcements = Announcement::where('is_published', true)
            ->where('publish_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            })
            ->where(function($q) use ($student) {
                $q->where('audience', 'all')
                  ->orWhere('audience', 'students')
                  ->orWhere(function($sq) use ($student) {
                      $sq->where('audience', 'specific_classes')
                         ->whereJsonContains('specific_classes', (string)$student->class_id);
                  });
            })
            ->orderBy('publish_date', 'desc')
            ->take(5)
            ->get();
        
        // Attendance statistics
        $attendanceStats = $this->getAttendanceStats($student);
        
        // Performance statistics
        $performanceStats = $this->getPerformanceStats($student);
        
        // Quick stats
        $quickStats = [
            'total_present' => $attendanceStats['monthly']['present'],
            'total_absent' => $attendanceStats['monthly']['absent'],
            'attendance_percentage' => $attendanceStats['overall_percentage'],
            'average_marks' => $performanceStats['average_percentage'],
            'pending_fees' => $totalDue,
            'pending_fees_formatted' => '₹ ' . number_format($totalDue, 2),
            'books_issued' => $libraryBooks->count(),
            'pending_homework' => $pendingHomework->count(),
            'upcoming_exams' => $upcomingExams->count(),
        ];
        
        // Academic progress chart data
        $progressData = $this->getAcademicProgressData($student);
        
        return view('student.dashboard', compact(
            'student',
            'todayAttendance',
            'todayTimetable',
            'currentClass',
            'nextClass',
            'pendingHomeworkData',
            'upcomingExams',
            'recentResults',
            'feeStatus',
            'totalDue',
            'libraryBooks',
            'transport',
            'hostel',
            'upcomingEvents',
            'recentNotifications',
            'announcements', 
            'attendanceStats',
            'performanceStats',
            'quickStats',
            'progressData'
        ));
    }
    
    /**
     * Get attendance statistics
     */
    private function getAttendanceStats($student)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $monthlyAttendances = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->whereMonth('attendance_date', $currentMonth)
            ->whereYear('attendance_date', $currentYear)
            ->get();
        
        $yearlyAttendances = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->whereYear('attendance_date', $currentYear)
            ->get();
        
        return [
            'today' => [
                'status' => $student->attendances()
                    ->whereDate('attendance_date', today())
                    ->first()?->status ?? 'not_marked',
            ],
            'monthly' => [
                'total' => $monthlyAttendances->count(),
                'present' => $monthlyAttendances->where('status', 'present')->count(),
                'absent' => $monthlyAttendances->where('status', 'absent')->count(),
                'late' => $monthlyAttendances->where('status', 'late')->count(),
                'percentage' => $monthlyAttendances->count() > 0 
                    ? round(($monthlyAttendances->where('status', 'present')->count() / $monthlyAttendances->count()) * 100, 2) 
                    : 0,
            ],
            'yearly' => [
                'total' => $yearlyAttendances->count(),
                'present' => $yearlyAttendances->where('status', 'present')->count(),
                'absent' => $yearlyAttendances->where('status', 'absent')->count(),
                'percentage' => $yearlyAttendances->count() > 0 
                    ? round(($yearlyAttendances->where('status', 'present')->count() / $yearlyAttendances->count()) * 100, 2) 
                    : 0,
            ],
            'overall_percentage' => $student->attendance_percentage,
        ];
    }
    
    /**
     * Get performance statistics
     */
    private function getPerformanceStats($student)
    {
        $results = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get();
        
        $subjectPerformance = [];
        $totalPercentage = 0;
        
        foreach ($results->groupBy('examSchedule.subject_id') as $subjectId => $subjectResults) {
            $subject = $subjectResults->first()->examSchedule->subject;
            $avgPercentage = $subjectResults->avg('percentage');
            $subjectPerformance[] = [
                'subject' => $subject->name,
                'average' => round($avgPercentage, 2),
                'grade' => $this->calculateGrade($avgPercentage),
            ];
            $totalPercentage += $avgPercentage;
        }
        
        $examPerformance = [];
        foreach ($results->groupBy('examSchedule.exam_id') as $examId => $examResults) {
            $exam = $examResults->first()->examSchedule->exam;
            $avgPercentage = $examResults->avg('percentage');
            $examPerformance[] = [
                'exam' => $exam->name,
                'average' => round($avgPercentage, 2),
                'date' => $exam->start_date->format('M Y'),
            ];
        }
        
        $subjectPerformanceCollection = collect($subjectPerformance);
        return [
            'total_exams' => $results->groupBy('examSchedule.exam_id')->count(),
            'total_subjects' => $subjectPerformanceCollection->count(),
            'average_percentage' => ($subjectPerformanceCollection->count() > 0) 
                ? round($totalPercentage / $subjectPerformanceCollection->count(), 2) 
                : 0,
            'subject_wise' => $subjectPerformance,
            'exam_trend' => $examPerformance,
            'best_subject' => $subjectPerformanceCollection->sortByDesc('average')->first(),
            'worst_subject' => $subjectPerformanceCollection->sortBy('average')->first(),
            ];
    }
    
    /**
     * Get academic progress chart data
     */
    private function getAcademicProgressData($student)
    {
        $results = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get()
            ->groupBy('examSchedule.exam_id');
        
        $labels = [];
        $data = [];
        
        foreach ($results as $examId => $examResults) {
            $exam = $examResults->first()->examSchedule->exam;
            $labels[] = $exam->name;
            $data[] = round($examResults->avg('percentage'), 2);
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
    
    /**
     * Calculate grade based on percentage
     */
    private function calculateGrade($percentage)
    {
        return match(true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F'
        };
    }
}