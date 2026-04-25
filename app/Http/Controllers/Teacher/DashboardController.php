<?php
// app/Http/Controllers/Teacher/DashboardController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Timetable;
use App\Models\Homework;
use App\Models\ExamSchedule;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
use App\Models\ExamResult;
use App\Models\HomeworkSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        
        // Get unique class-section combinations taught by this teacher
        $classSections = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section'])
            ->get()
            ->unique(function($item) {
                return $item->class_id . '_' . $item->section_id;
            });
        
        $classIds = $classSections->pluck('class_id')->unique();
        
        // Get all classes taught
        $classes = Classes::whereIn('id', $classIds)->with('sections')->get();
        
        // Get all students taught by this teacher
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');
        $totalStudents = $studentIds->count();
        
        // Today's timetable (current day)
        $today = strtolower(Carbon::now()->format('l'));
        $now = Carbon::now();
        
        $todayTimetable = Timetable::where('teacher_id', $teacher->id)
            ->where('day_of_week', $today)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->orderBy('time_slot_id')
            ->get();
        
        // Current class (based on current time)
        $currentClass = null;
        $nextClass = null;
        foreach ($todayTimetable as $entry) {
            $start = Carbon::parse($entry->timeSlot->start_time);
            $end = Carbon::parse($entry->timeSlot->end_time);
            if ($now->between($start, $end)) {
                $currentClass = $entry;
                break;
            } elseif ($start > $now && !$nextClass) {
                $nextClass = $entry;
            }
        }
        
        // Today's attendance summary
        $todayAttendance = Attendance::whereDate('attendance_date', Carbon::today())
            ->whereIn('attendable_id', $studentIds)
            ->where('attendable_type', Student::class)
            ->get();
        
        $attendanceStats = [
            'total' => $totalStudents,
            'present' => $todayAttendance->where('status', 'present')->count(),
            'absent' => $todayAttendance->where('status', 'absent')->count(),
            'late' => $todayAttendance->where('status', 'late')->count(),
            'half_day' => $todayAttendance->where('status', 'half_day')->count(),
            'percentage' => $totalStudents > 0 
                ? round(($todayAttendance->where('status', 'present')->count() / $totalStudents) * 100, 2) 
                : 0,
        ];
        
        // Pending homework to check
        $pendingHomework = Homework::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->where('submission_date', '>=', Carbon::today())
            ->with(['class', 'section', 'subject'])
            ->withCount(['submissions' => function($q) {
                $q->where('status', 'submitted');
            }])
            ->get();
        
        $pendingHomeworkCount = $pendingHomework->count();
        $totalSubmissionsToGrade = HomeworkSubmission::whereHas('homework', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('status', 'submitted')
            ->count();
        
        // Upcoming exams
        $upcomingExams = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereHas('exam', function($q) {
                $q->where('start_date', '>=', Carbon::today());
            })
            ->with(['exam', 'class', 'section', 'subject'])
            ->orderBy('exam_date')
            ->limit(5)
            ->get();
        
        $upcomingExamsCount = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereHas('exam', function($q) {
                $q->where('start_date', '>=', Carbon::today());
            })
            ->count();
        
        // Recent exam results entered
        $recentResults = ExamResult::whereHas('examSchedule', function($q) use ($teacher) {
                $q->whereHas('classSubject', function($cq) use ($teacher) {
                    $cq->where('teacher_id', $teacher->id);
                });
            })
            ->with(['student.user', 'examSchedule.exam', 'examSchedule.subject'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Monthly attendance chart data
        $monthlyAttendance = $this->getMonthlyAttendanceData($studentIds);
        
        // Weekly homework chart data
        $weeklyHomework = $this->getWeeklyHomeworkData($teacher);
        
        // Subject-wise performance (fixed query)
        $subjectPerformance = $this->getSubjectPerformanceData($teacher);
        
        // Class-wise statistics
        $classStats = [];
        foreach ($classes as $class) {
            $classStudentIds = Student::where('class_id', $class->id)->pluck('id');
            $classAttendance = Attendance::whereDate('attendance_date', Carbon::today())
                ->whereIn('attendable_id', $classStudentIds)
                ->where('attendable_type', Student::class)
                ->get();
            
            $classStats[] = [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'students_count' => $classStudentIds->count(),
                'present_today' => $classAttendance->where('status', 'present')->count(),
                'attendance_percentage' => $classStudentIds->count() > 0 
                    ? round(($classAttendance->where('status', 'present')->count() / $classStudentIds->count()) * 100, 2) 
                    : 0,
                'homework_pending' => Homework::where('teacher_id', $teacher->id)
                    ->where('class_id', $class->id)
                    ->where('status', 'active')
                    ->count(),
                'upcoming_exams' => ExamSchedule::whereHas('classSubject', function($q) use ($teacher, $class) {
                        $q->where('teacher_id', $teacher->id)
                          ->where('class_id', $class->id);
                    })
                    ->whereHas('exam', function($q) {
                        $q->where('start_date', '>=', Carbon::today());
                    })
                    ->count(),
            ];
        }
        
        // Recent activities
        $recentActivities = $this->getRecentActivities($teacher);
        
        // Quick stats cards
        $quickStats = [
            'total_classes' => $classes->count(),
            'total_sections' => $classSections->count(),
            'total_students' => $totalStudents,
            'pending_homework' => $pendingHomeworkCount,
            'submissions_to_grade' => $totalSubmissionsToGrade,
            'upcoming_exams' => $upcomingExamsCount,
            'today_attendance_rate' => $attendanceStats['percentage'],
        ];
        
        return view('teacher.dashboard', compact(
            'todayTimetable',
            'currentClass',
            'nextClass',
            'attendanceStats',
            'pendingHomework',
            'upcomingExams',
            'recentResults',
            'classStats',
            'recentActivities',
            'quickStats',
            'monthlyAttendance',
            'weeklyHomework',
            'subjectPerformance'
        ));
    }
    
    /**
     * Get monthly attendance data for chart
     */
    private function getMonthlyAttendanceData($studentIds)
    {
        $months = [];
        $presentData = [];
        $absentData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M');
            
            $attendances = Attendance::whereIn('attendable_id', $studentIds)
                ->where('attendable_type', Student::class)
                ->whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->get();
            
            $present = $attendances->where('status', 'present')->count();
            $absent = $attendances->where('status', 'absent')->count();
            
            $presentData[] = $present;
            $absentData[] = $absent;
        }
        
        return [
            'labels' => $months,
            'present' => $presentData,
            'absent' => $absentData,
        ];
    }
    
    /**
     * Get weekly homework data for chart
     */
    private function getWeeklyHomeworkData($teacher)
    {
        $days = [];
        $assignedData = [];
        $submittedData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('D');
            
            $assigned = Homework::where('teacher_id', $teacher->id)
                ->whereDate('created_at', $date)
                ->count();
            
            $submitted = HomeworkSubmission::whereHas('homework', function($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })
                ->whereDate('submitted_at', $date)
                ->count();
            
            $assignedData[] = $assigned;
            $submittedData[] = $submitted;
        }
        
        return [
            'labels' => $days,
            'assigned' => $assignedData,
            'submitted' => $submittedData,
        ];
    }
    
    /**
     * Get subject-wise performance data - FIXED VERSION
     */
    private function getSubjectPerformanceData($teacher)
    {
        // Get all subjects taught by this teacher from class_subjects table
        $subjects = ClassSubject::where('teacher_id', $teacher->id)
            ->with('subject')
            ->get()
            ->unique('subject_id')
            ->map(function($item) use ($teacher) {
                // Get exam results for this subject across all classes
                $examResults = ExamResult::whereHas('examSchedule', function($q) use ($teacher, $item) {
                        $q->whereHas('classSubject', function($cq) use ($teacher, $item) {
                            $cq->where('teacher_id', $teacher->id)
                               ->where('subject_id', $item->subject_id);
                        });
                    })
                    ->get();
                
                $averageMarks = $examResults->avg('total_marks_obtained');
                $averagePercentage = $examResults->avg('percentage');
                $totalStudents = $examResults->groupBy('student_id')->count();
                
                return [
                    'subject_id' => $item->subject_id,
                    'subject_name' => $item->subject->name,
                    'subject_code' => $item->subject->code,
                    'average_marks' => round($averageMarks, 2),
                    'average_percentage' => round($averagePercentage, 2),
                    'total_exams' => $examResults->groupBy('exam_schedule_id')->count(),
                    'total_students' => $totalStudents,
                ];
            })
            ->values();
        
        return $subjects;
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities($teacher)
    {
        $activities = [];
        
        // Recent homework assignments
        $recentHomework = Homework::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject'])
            ->latest()
            ->take(3)
            ->get();
        
        foreach ($recentHomework as $homework) {
            $activities[] = [
                'type' => 'homework',
                'icon' => 'fa-book-open',
                'color' => 'primary',
                'title' => 'Homework Assigned',
                'description' => "Assigned '{$homework->title}' to {$homework->class->name} - Section {$homework->section->name}",
                'time_ago' => $homework->created_at->diffForHumans(),
                'created_at' => $homework->created_at,
            ];
        }
        
        // Recent exam results entered
        $recentResults = ExamResult::whereHas('examSchedule', function($q) use ($teacher) {
                $q->whereHas('classSubject', function($cq) use ($teacher) {
                    $cq->where('teacher_id', $teacher->id);
                });
            })
            ->with(['student.user', 'examSchedule.subject'])
            ->latest()
            ->take(3)
            ->get();
        
        foreach ($recentResults as $result) {
            $activities[] = [
                'type' => 'exam',
                'icon' => 'fa-file-alt',
                'color' => 'success',
                'title' => 'Marks Entered',
                'description' => "Entered marks for {$result->student->user->name} in {$result->examSchedule->subject->name}",
                'time_ago' => $result->created_at->diffForHumans(),
                'created_at' => $result->created_at,
            ];
        }
        
        // Recent homework submissions graded
        $recentGraded = HomeworkSubmission::whereHas('homework', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('status', 'graded')
            ->with(['student.user', 'homework'])
            ->latest()
            ->take(3)
            ->get();
        
        foreach ($recentGraded as $submission) {
            $activities[] = [
                'type' => 'graded',
                'icon' => 'fa-check-circle',
                'color' => 'info',
                'title' => 'Homework Graded',
                'description' => "Graded {$submission->student->user->name}'s submission for '{$submission->homework->title}'",
                'time_ago' => $submission->updated_at->diffForHumans(),
                'created_at' => $submission->updated_at,
            ];
        }
        
        // Sort by created_at descending
        $activities = collect($activities)->sortByDesc('created_at')->take(10)->values();
        
        return $activities;
    }
    
    /**
     * Get upcoming schedule for the week (AJAX)
     */
    public function weeklySchedule()
    {
        $teacher = Auth::user();
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        $schedule = [];
        foreach ($days as $day) {
            $timetable = Timetable::where('teacher_id', $teacher->id)
                ->where('day_of_week', $day)
                ->with(['class', 'section', 'subject', 'timeSlot'])
                ->orderBy('time_slot_id')
                ->get();
            
            $schedule[$day] = $timetable;
        }
        
        return response()->json($schedule);
    }
    
    /**
     * Get student performance data (AJAX)
     */
    public function studentPerformance(Request $request)
    {
        $teacher = Auth::user();
        $classId = $request->class_id;
        $subjectId = $request->subject_id;
        
        $query = Student::whereIn('class_id', function($q) use ($teacher) {
            $q->select('class_id')
              ->from('class_subjects')
              ->where('teacher_id', $teacher->id);
        });
        
        if ($classId) {
            $query->where('class_id', $classId);
        }
        
        $students = $query->with('user')->get();
        
        $performance = [];
        foreach ($students as $student) {
            $examResults = ExamResult::where('student_id', $student->id)
                ->whereHas('examSchedule', function($q) use ($teacher, $subjectId) {
                    $q->whereHas('classSubject', function($cq) use ($teacher) {
                        $cq->where('teacher_id', $teacher->id);
                    });
                    if ($subjectId) {
                        $q->where('subject_id', $subjectId);
                    }
                })
                ->get();
            
            $performance[] = [
                'student_id' => $student->id,
                'student_name' => $student->user->name,
                'admission_number' => $student->admission_number,
                'average_percentage' => round($examResults->avg('percentage'), 2),
                'total_exams' => $examResults->count(),
                'attendance_percentage' => $student->attendance_percentage,
            ];
        }
        
        return response()->json($performance);
    }
}