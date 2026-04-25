<?php
// app/Http/Controllers/Api/Student/DashboardController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\StudentDashboardResource;
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
use Illuminate\Support\Str;

class DashboardController extends BaseController
{
    public function index()
    {
        $student = Auth::user()->student;
        $student->load(['user', 'class', 'section']);

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

        // Current and next class
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

        $homeworkSubmissions = $student->homeworkSubmissions()->get()->keyBy('homework_id');
        $pendingHomeworkData = $pendingHomework->map(function ($homework) use ($homeworkSubmissions) {
            $submission = $homeworkSubmissions[$homework->id] ?? null;
            return [
                'homework' => $homework,
                'submitted' => !is_null($submission),
                'submitted_at' => $submission?->submitted_at,
                'status' => $submission?->status,
            ];
        });

        // Upcoming exams
        $upcomingExams = ExamSchedule::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->whereHas('exam', fn($q) => $q->where('start_date', '>=', today()))
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
        $totalDue = StudentFee::where('student_id', $student->id)->whereIn('status', ['pending', 'partial'])->sum('due_amount');

        // Library books
        $libraryBooks = BookIssue::where('issuable_type', 'App\Models\Student')
            ->where('issuable_id', $student->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->with('book')
            ->get();

        // Transport
        $transport = StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->with(['route', 'stop'])
            ->first();

        // Hostel
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
        $recentNotifications = Notification::latest()->take(5)->get();

        // Announcements
        $announcements = Announcement::where('is_published', true)
            ->where('publish_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
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

        // Statistics
        $attendanceStats = $this->getAttendanceStats($student);
        $performanceStats = $this->getPerformanceStats($student);

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

        $progressData = $this->getAcademicProgressData($student);

        $data = [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name,
                'email' => $student->user->email,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'class' => $student->class?->name,
                'section' => $student->section?->name,
                'profile_photo' => $student->user->profile_photo_url,
            ],
            'today_attendance' => $todayAttendance ? [
                'status' => $todayAttendance->status,
                'check_in' => $todayAttendance->check_in_time?->format('h:i A'),
                'check_out' => $todayAttendance->check_out_time?->format('h:i A'),
            ] : ['status' => 'not_marked'],
            'today_timetable' => $todayTimetable->map(fn($t) => [
                'time' => $t->timeSlot->time_range,
                'subject' => $t->subject->name,
                'teacher' => $t->teacher->name,
                'room' => $t->room_number,
            ]),
            'current_class' => $currentClass ? [
                'subject' => $currentClass->subject->name,
                'teacher' => $currentClass->teacher->name,
                'room' => $currentClass->room_number,
                'ends_at' => Carbon::parse($currentClass->timeSlot->end_time)->format('h:i A'),
            ] : null,
            'next_class' => $nextClass ? [
                'subject' => $nextClass->subject->name,
                'teacher' => $nextClass->teacher->name,
                'starts_at' => Carbon::parse($nextClass->timeSlot->start_time)->format('h:i A'),
            ] : null,
            'pending_homework' => $pendingHomeworkData,
            'upcoming_exams' => $upcomingExams->map(fn($e) => [
                'id' => $e->id,
                'exam_name' => $e->exam->name,
                'subject' => $e->subject->name,
                'date' => $e->exam_date->toDateString(),
                'start_time' => Carbon::parse($e->start_time)->format('h:i A'),
                'end_time' => Carbon::parse($e->end_time)->format('h:i A'),
                'room' => $e->room_number,
                'total_marks' => $e->total_marks + ($e->practical_marks ?? 0),
                'days_left' => Carbon::today()->diffInDays($e->exam_date, false),
            ]),
            'recent_results' => $recentResults->map(fn($r) => [
                'exam' => $r->examSchedule->exam->name,
                'subject' => $r->examSchedule->subject->name,
                'marks' => $r->total_marks_obtained,
                'max_marks' => $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0),
                'percentage' => $r->percentage,
                'grade' => $r->grade,
            ]),
            'fee_status' => $feeStatus->map(fn($f) => [
                'id' => $f->id,
                'category' => $f->feeStructure->feeCategory->name,
                'total_amount' => $f->total_amount,
                'paid_amount' => $f->paid_amount,
                'due_amount' => $f->due_amount,
                'due_date' => $f->due_date->toDateString(),
                'status' => $f->status,
            ]),
            'total_due' => $totalDue,
            'total_due_formatted' => '₹ ' . number_format($totalDue, 2),
            'library_books' => $libraryBooks->map(fn($b) => [
                'id' => $b->id,
                'title' => $b->book->title,
                'author' => $b->book->author,
                'issue_date' => $b->issue_date->toDateString(),
                'due_date' => $b->due_date?->toDateString(),
                'status' => $b->status,
            ]),
            'transport' => $transport ? [
                'route_name' => $transport->route->route_name,
                'route_number' => $transport->route->route_number,
                'stop_name' => $transport->stop->stop_name,
                'pickup_time' => Carbon::parse($transport->stop->pickup_time)->format('h:i A'),
                'drop_time' => Carbon::parse($transport->stop->drop_time)->format('h:i A'),
                'monthly_fee' => $transport->stop->fee,
            ] : null,
            'hostel' => $hostel ? [
                'hostel_name' => $hostel->room->hostel->name,
                'room_number' => $hostel->room->room_number,
                'room_type' => ucfirst(str_replace('_', ' ', $hostel->room->room_type)),
                'fee_per_month' => $hostel->room->fee_per_month,
                'allocation_date' => $hostel->allocation_date->toDateString(),
                'warden_name' => $hostel->room->hostel->warden_name,
                'warden_phone' => $hostel->room->hostel->warden_phone,
            ] : null,
            'upcoming_events' => $upcomingEvents->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'date' => $e->start_date->toDateString(),
                'venue' => $e->venue,
                'days_left' => Carbon::today()->diffInDays($e->start_date, false),
            ]),
            'recent_notifications' => $recentNotifications->map(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
            'announcements' => $announcements->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'excerpt' => Str::limit(strip_tags($a->content), 100),
                'published_at' => $a->publish_date->diffForHumans(),
            ]),
            'attendance_stats' => $attendanceStats,
            'performance_stats' => $performanceStats,
            'quick_stats' => $quickStats,
            'progress_chart' => $progressData,
        ];

        return $this->sendResponse($data, 'Dashboard data retrieved successfully');
    }

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
                'status' => $student->attendances()->whereDate('attendance_date', today())->first()?->status ?? 'not_marked',
            ],
            'monthly' => [
                'total' => $monthlyAttendances->count(),
                'present' => $monthlyAttendances->where('status', 'present')->count(),
                'absent' => $monthlyAttendances->where('status', 'absent')->count(),
                'late' => $monthlyAttendances->where('status', 'late')->count(),
                'percentage' => $monthlyAttendances->count() > 0 ? round(($monthlyAttendances->where('status', 'present')->count() / $monthlyAttendances->count()) * 100, 2) : 0,
            ],
            'yearly' => [
                'total' => $yearlyAttendances->count(),
                'present' => $yearlyAttendances->where('status', 'present')->count(),
                'absent' => $yearlyAttendances->where('status', 'absent')->count(),
                'percentage' => $yearlyAttendances->count() > 0 ? round(($yearlyAttendances->where('status', 'present')->count() / $yearlyAttendances->count()) * 100, 2) : 0,
            ],
            'overall_percentage' => $student->attendance_percentage,
        ];
    }

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

        $subjectCount = count($subjectPerformance);
        return [
            'total_exams' => $results->groupBy('examSchedule.exam_id')->count(),
            'total_subjects' => $subjectCount,
            'average_percentage' => $subjectCount > 0 ? round($totalPercentage / $subjectCount, 2) : 0,
            'subject_wise' => $subjectPerformance,
            'exam_trend' => $examPerformance,
            'best_subject' => collect($subjectPerformance)->sortByDesc('average')->first(),
            'worst_subject' => collect($subjectPerformance)->sortBy('average')->first(),
        ];
    }

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

        return ['labels' => $labels, 'data' => $data];
    }

    private function calculateGrade($percentage)
    {
        return match(true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F',
        };
    }
}