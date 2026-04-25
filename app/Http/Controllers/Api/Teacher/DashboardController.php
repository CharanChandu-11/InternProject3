<?php
// app/Http/Controllers/Api/Teacher/DashboardController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\ClassSubject;
use App\Models\Timetable;
use App\Models\Homework;
use App\Models\ExamSchedule;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    public function index()
    {
        $teacher = Auth::user();
        
        // Get classes taught
        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        $classes = ClassSubject::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->get()
            ->groupBy('class_id')
            ->map(function($items) {
                return [
                    'class_id' => $items->first()->class_id,
                    'class_name' => $items->first()->class->name,
                    'subjects' => $items->pluck('subject.name'),
                    'sections' => $items->first()->class->sections->pluck('name'),
                ];
            })
            ->values();
        
        // Today's timetable
        $today = strtolower(Carbon::now()->format('l'));
        $currentTime = Carbon::now()->format('H:i:s');
        
        $todayTimetable = Timetable::where('teacher_id', $teacher->id)
            ->where('day_of_week', $today)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->orderBy('time_slot_id')
            ->get()
            ->map(function($item) use ($currentTime) {
                $isCurrent = $item->timeSlot->start_time <= $currentTime && $item->timeSlot->end_time >= $currentTime;
                return [
                    'id' => $item->id,
                    'class' => $item->class->name,
                    'section' => $item->section->name,
                    'subject' => $item->subject->name,
                    'time' => $item->timeSlot->time_range,
                    'room' => $item->room_number,
                    'is_current' => $isCurrent,
                ];
            });
        
        // Pending homework to grade
        $pendingHomework = Homework::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['class', 'section', 'subject'])
            ->withCount(['submissions' => function($q) {
                $q->where('status', 'submitted');
            }])
            ->get()
            ->map(function($homework) {
                return [
                    'id' => $homework->id,
                    'title' => $homework->title,
                    'class' => $homework->class->name,
                    'section' => $homework->section->name,
                    'subject' => $homework->subject->name,
                    'submission_date' => $homework->submission_date->toDateString(),
                    'pending_count' => $homework->submissions_count,
                ];
            });
        
        // Upcoming exams
        $upcomingExams = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('exam_date', '>=', Carbon::today())
            ->with(['exam', 'class', 'section', 'subject'])
            ->orderBy('exam_date')
            ->limit(5)
            ->get()
            ->map(function($exam) {
                return [
                    'id' => $exam->id,
                    'exam_name' => $exam->exam->name,
                    'subject' => $exam->subject->name,
                    'class' => $exam->class->name,
                    'section' => $exam->section->name,
                    'date' => $exam->exam_date->toDateString(),
                    'time' => Carbon::parse($exam->start_time)->format('h:i A') . ' - ' . Carbon::parse($exam->end_time)->format('h:i A'),
                    'room' => $exam->room_number,
                ];
            });
        
        // Today's attendance summary
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');
        $todayAttendance = Attendance::whereDate('attendance_date', Carbon::today())
            ->whereIn('attendable_id', $studentIds)
            ->where('attendable_type', Student::class)
            ->get();
        
        $attendanceStats = [
            'total_students' => $studentIds->count(),
            'present' => $todayAttendance->where('status', 'present')->count(),
            'absent' => $todayAttendance->where('status', 'absent')->count(),
            'late' => $todayAttendance->where('status', 'late')->count(),
            'percentage' => $studentIds->count() > 0 
                ? round(($todayAttendance->where('status', 'present')->count() / $studentIds->count()) * 100, 2) 
                : 0,
        ];
        
        // Recent activities
        $recentActivities = $this->getRecentActivities($teacher);
        
        // Statistics
        $stats = [
            'total_classes' => $classIds->count(),
            'total_students' => $studentIds->count(),
            'pending_homework' => $pendingHomework->sum('pending_count'),
            'upcoming_exams' => $upcomingExams->count(),
            'pending_leaves' => LeaveApplication::where('user_id', $teacher->id)
                ->where('status', 'pending')
                ->count(),
        ];
        
        return $this->sendResponse([
            'classes' => $classes,
            'today_timetable' => $todayTimetable,
            'pending_homework' => $pendingHomework,
            'upcoming_exams' => $upcomingExams,
            'attendance_stats' => $attendanceStats,
            'recent_activities' => $recentActivities,
            'stats' => $stats,
        ], 'Dashboard data retrieved');
    }
    
    public function stats()
    {
        $teacher = Auth::user();
        
        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');
        
        $monthlyAttendance = Attendance::whereMonth('attendance_date', Carbon::now()->month)
            ->whereIn('attendable_id', $studentIds)
            ->where('attendable_type', Student::class)
            ->get();
        
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $attendances = Attendance::whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->whereIn('attendable_id', $studentIds)
                ->where('attendable_type', Student::class)
                ->get();
            
            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'present' => $attendances->where('status', 'present')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
            ];
        }
        
        $subjectPerformance = [];
        $subjects = ClassSubject::where('teacher_id', $teacher->id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id');
        
        foreach ($subjects as $subject) {
            $results = \App\Models\ExamResult::whereHas('examSchedule', function($q) use ($subject, $teacher) {
                $q->where('subject_id', $subject->id)
                  ->whereHas('classSubject', function($cq) use ($teacher) {
                      $cq->where('teacher_id', $teacher->id);
                  });
            })->get();
            
            $subjectPerformance[] = [
                'subject' => $subject->name,
                'average_marks' => round($results->avg('total_marks_obtained'), 2),
                'average_percentage' => round($results->avg('percentage'), 2),
                'total_students' => $results->groupBy('student_id')->count(),
            ];
        }
        
        return $this->sendResponse([
            'monthly_attendance' => $monthlyData,
            'subject_performance' => $subjectPerformance,
            'total_students' => $studentIds->count(),
            'total_homework' => Homework::where('teacher_id', $teacher->id)->count(),
            'total_exams' => ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->count(),
        ], 'Statistics retrieved');
    }
    
    private function getRecentActivities($teacher)
    {
        $activities = [];
        
        // Recent homework submissions
        $recentSubmissions = \App\Models\HomeworkSubmission::whereHas('homework', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->with(['student.user', 'homework'])
        ->latest()
        ->limit(5)
        ->get();
        
        foreach ($recentSubmissions as $submission) {
            $activities[] = [
                'type' => 'homework',
                'title' => 'Homework Submitted',
                'description' => $submission->student->user->name . ' submitted ' . $submission->homework->title,
                'time_ago' => $submission->created_at->diffForHumans(),
            ];
        }
        
        // Recent exam marks entered
        $recentMarks = \App\Models\ExamResult::whereHas('examSchedule', function($q) use ($teacher) {
            $q->whereHas('classSubject', function($cq) use ($teacher) {
                $cq->where('teacher_id', $teacher->id);
            });
        })
        ->with(['student.user', 'examSchedule.subject'])
        ->latest()
        ->limit(5)
        ->get();
        
        foreach ($recentMarks as $mark) {
            $activities[] = [
                'type' => 'exam',
                'title' => 'Marks Entered',
                'description' => 'Marks entered for ' . $mark->student->user->name . ' in ' . $mark->examSchedule->subject->name,
                'time_ago' => $mark->created_at->diffForHumans(),
            ];
        }
        
        return $activities;
    }
}