<?php
// app/Http/Controllers/Api/Parent/ChildController.php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Api\BaseController;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\Homework;
use App\Models\Exam;
use App\Models\Timetable;
use App\Models\StudentFee;
use App\Models\HomeworkSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChildController extends BaseController
{
    public function index()
    {
        $parent = Auth::user()->parent;
        $children = $parent->children()->with(['user', 'class', 'section'])->get();
        
        return $this->sendResponse($children, 'Children list retrieved');
    }
    
    public function show(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $student->load(['user', 'class', 'section']);
        
        return $this->sendResponse($student, 'Student details retrieved');
    }
    
    public function attendance(Student $student, Request $request)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $query = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id);
        
        if ($request->filled('month')) {
            $query->whereMonth('attendance_date', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->whereYear('attendance_date', $request->year);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate($request->per_page ?? 30);
        
        $summary = [
            'today' => Attendance::where('attendable_type', 'App\Models\Student')
                ->where('attendable_id', $student->id)
                ->whereDate('attendance_date', today())
                ->first(),
            'overall_percentage' => $student->attendance_percentage,
        ];
        
        return $this->sendResponse([
            'attendances' => $attendances->items(),
            'summary' => $summary,
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ], 'Attendance records retrieved');
    }
    
    public function monthlyAttendance(Student $student, Request $request)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;
        
        $attendances = Attendance::where('attendable_type', 'App\Models\Student')
            ->where('attendable_id', $student->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->keyBy(function($item) {
                return $item->attendance_date->format('Y-m-d');
            });
        
        $calendar = $this->generateCalendar($year, $month, $attendances);
        $stats = $this->getMonthlyStats($attendances, $year, $month);
        
        return $this->sendResponse([
            'calendar' => $calendar,
            'statistics' => $stats,
            'month' => $month,
            'year' => $year,
        ], 'Monthly attendance retrieved');
    }
    
    public function results(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $results = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('examSchedule.exam_id')
            ->map(function($items, $examId) {
                $exam = $items->first()->examSchedule->exam;
                return [
                    'exam_id' => $examId,
                    'exam_name' => $exam->name,
                    'exam_date' => $exam->start_date->toDateString(),
                    'subjects' => $items->map(function($result) {
                        return [
                            'subject' => $result->examSchedule->subject->name,
                            'marks' => $result->total_marks_obtained,
                            'max_marks' => $result->examSchedule->total_marks + ($result->examSchedule->practical_marks ?? 0),
                            'percentage' => $result->percentage,
                            'grade' => $result->grade,
                        ];
                    }),
                ];
            })->values();
        
        return $this->sendResponse($results, 'Exam results retrieved');
    }
    
    public function fees(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $fees = StudentFee::where('student_id', $student->id)
            ->with(['feeStructure.feeCategory'])
            ->orderBy('due_date')
            ->get();
        
        $summary = [
            'total_fees' => $fees->sum('total_amount'),
            'total_paid' => $fees->sum('paid_amount'),
            'total_due' => $fees->sum('due_amount'),
        ];
        
        return $this->sendResponse([
            'fees' => $fees,
            'summary' => $summary,
        ], 'Fee details retrieved');
    }
    
    public function homework(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $homeworks = Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('status', 'active')
            ->with(['subject', 'teacher'])
            ->orderBy('submission_date')
            ->get()
            ->map(function($homework) use ($student) {
                $submission = $student->homeworkSubmissions()
                    ->where('homework_id', $homework->id)
                    ->first();
                
                return [
                    'id' => $homework->id,
                    'title' => $homework->title,
                    'description' => $homework->description,
                    'subject' => $homework->subject->name,
                    'submission_date' => $homework->submission_date->toDateString(),
                    'is_submitted' => !is_null($submission),
                    'submitted_at' => $submission?->submitted_at,
                    'status' => $submission?->status,
                    'obtained_marks' => $submission?->obtained_marks,
                    'feedback' => $submission?->feedback,
                ];
            });
        
        return $this->sendResponse($homeworks, 'Homework list retrieved');
    }
    
    public function timetable(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $timetable = Timetable::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with(['subject', 'teacher', 'timeSlot'])
            ->get()
            ->groupBy('day_of_week');
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $timeSlots = \App\Models\TimeSlot::orderBy('start_time')->get();
        
        $formatted = [];
        foreach ($days as $day) {
            $dayEntries = [];
            foreach ($timeSlots as $slot) {
                $entry = $timetable[$day] ?? collect();
                $class = $entry->firstWhere('time_slot_id', $slot->id);
                
                $dayEntries[] = [
                    'time' => $slot->time_range,
                    'subject' => $class ? $class->subject->name : null,
                    'teacher' => $class ? $class->teacher->name : null,
                    'room' => $class ? $class->room_number : null,
                    'is_break' => $slot->is_break,
                ];
            }
            $formatted[$day] = $dayEntries;
        }
        
        return $this->sendResponse($formatted, 'Timetable retrieved');
    }
    
    private function generateCalendar($year, $month, $attendances)
    {
        $firstDay = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startDayOfWeek = $firstDay->dayOfWeek;
        
        $calendar = [];
        $week = [];
        
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $week[] = null;
        }
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $attendance = $attendances[$date->format('Y-m-d')] ?? null;
            
            $week[] = [
                'day' => $day,
                'date' => $date->toDateString(),
                'status' => $attendance?->status,
                'status_text' => $attendance ? ucfirst($attendance->status) : 'Not Marked',
            ];
            
            if ($date->dayOfWeek == 6 || $day == $daysInMonth) {
                $calendar[] = $week;
                $week = [];
            }
        }
        
        return $calendar;
    }
    
    private function getMonthlyStats($attendances, $year, $month)
    {
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        
        $workingDays = $this->getWorkingDays($year, $month);
        
        return [
            'present' => $present,
            'absent' => $total - $present,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            'attendance_rate' => $workingDays > 0 ? round(($present / $workingDays) * 100, 2) : 0,
        ];
    }
    
    private function getWorkingDays($year, $month)
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $workingDays = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            if (!$date->isSunday()) {
                $workingDays++;
            }
        }
        return $workingDays;
    }

    public function attendanceSummary(Student $student)
    {
        $parent = Auth::user()->parent;
        
        // Verify this child belongs to the parent
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized to access this student', [], 403);
        }
        
        // Get current date
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentYear = $today->year;
        
        // Today's attendance
        $todayAttendance = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereDate('attendance_date', $today)
            ->first();
        
        // Current month attendance
        $monthlyAttendances = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereMonth('attendance_date', $currentMonth)
            ->whereYear('attendance_date', $currentYear)
            ->get();
        
        $monthlyStats = [
            'total_days' => $monthlyAttendances->count(),
            'present' => $monthlyAttendances->where('status', 'present')->count(),
            'absent' => $monthlyAttendances->where('status', 'absent')->count(),
            'late' => $monthlyAttendances->where('status', 'late')->count(),
            'half_day' => $monthlyAttendances->where('status', 'half_day')->count(),
            'percentage' => $monthlyAttendances->count() > 0 
                ? round(($monthlyAttendances->where('status', 'present')->count() / $monthlyAttendances->count()) * 100, 2) 
                : 0,
        ];
        
        // Current year attendance
        $yearlyAttendances = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereYear('attendance_date', $currentYear)
            ->get();
        
        $yearlyStats = [
            'total_days' => $yearlyAttendances->count(),
            'present' => $yearlyAttendances->where('status', 'present')->count(),
            'absent' => $yearlyAttendances->where('status', 'absent')->count(),
            'percentage' => $yearlyAttendances->count() > 0 
                ? round(($yearlyAttendances->where('status', 'present')->count() / $yearlyAttendances->count()) * 100, 2) 
                : 0,
        ];
        
        // Overall attendance (all time)
        $allAttendances = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->get();
        
        $overallStats = [
            'total_days' => $allAttendances->count(),
            'present' => $allAttendances->where('status', 'present')->count(),
            'absent' => $allAttendances->where('status', 'absent')->count(),
            'late' => $allAttendances->where('status', 'late')->count(),
            'half_day' => $allAttendances->where('status', 'half_day')->count(),
            'percentage' => $allAttendances->count() > 0 
                ? round(($allAttendances->where('status', 'present')->count() / $allAttendances->count()) * 100, 2) 
                : 0,
        ];
        
        // Monthly breakdown for last 6 months
        $monthlyBreakdown = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthAttendances = Attendance::where('attendable_type', Student::class)
                ->where('attendable_id', $student->id)
                ->whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->get();
            
            $monthlyBreakdown[] = [
                'month' => $month->format('M Y'),
                'month_name' => $month->format('F'),
                'year' => $month->year,
                'total_days' => $monthAttendances->count(),
                'present' => $monthAttendances->where('status', 'present')->count(),
                'absent' => $monthAttendances->where('status', 'absent')->count(),
                'percentage' => $monthAttendances->count() > 0 
                    ? round(($monthAttendances->where('status', 'present')->count() / $monthAttendances->count()) * 100, 2) 
                    : 0,
            ];
        }
        
        // Weekly breakdown for last 4 weeks
        $weeklyBreakdown = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            
            $weekAttendances = Attendance::where('attendable_type', Student::class)
                ->where('attendable_id', $student->id)
                ->whereBetween('attendance_date', [$weekStart, $weekEnd])
                ->get();
            
            $weeklyBreakdown[] = [
                'week' => 'Week ' . (4 - $i),
                'start_date' => $weekStart->format('d M'),
                'end_date' => $weekEnd->format('d M'),
                'total_days' => $weekAttendances->count(),
                'present' => $weekAttendances->where('status', 'present')->count(),
                'absent' => $weekAttendances->where('status', 'absent')->count(),
                'percentage' => $weekAttendances->count() > 0 
                    ? round(($weekAttendances->where('status', 'present')->count() / $weekAttendances->count()) * 100, 2) 
                    : 0,
            ];
        }
        
        // Academic year attendance (April-March)
        $currentMonth = Carbon::now()->month;
        $academicStartYear = $currentMonth >= 4 ? Carbon::now()->year : Carbon::now()->year - 1;
        $academicEndYear = $academicStartYear + 1;
        
        $academicYearAttendances = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereBetween('attendance_date', [
                Carbon::createFromDate($academicStartYear, 4, 1),
                Carbon::createFromDate($academicEndYear, 3, 31)
            ])
            ->get();
        
        $academicYearStats = [
            'academic_year' => $academicStartYear . '-' . $academicEndYear,
            'total_days' => $academicYearAttendances->count(),
            'present' => $academicYearAttendances->where('status', 'present')->count(),
            'absent' => $academicYearAttendances->where('status', 'absent')->count(),
            'percentage' => $academicYearAttendances->count() > 0 
                ? round(($academicYearAttendances->where('status', 'present')->count() / $academicYearAttendances->count()) * 100, 2) 
                : 0,
        ];
        
        // Performance metrics (attendance vs class average if available)
        $classAverage = $this->getClassAverageAttendance($student);
        
        // Get working days in current month
        $workingDaysInMonth = $this->getWorkingDays($currentYear, $currentMonth);
        $remainingDays = max(0, $workingDaysInMonth - $monthlyStats['total_days']);
        
        // Projected attendance if student attends all remaining days
        $projectedPresent = $monthlyStats['present'] + $remainingDays;
        $projectedPercentage = $workingDaysInMonth > 0 
            ? round(($projectedPresent / $workingDaysInMonth) * 100, 2) 
            : 0;
        
        return $this->sendResponse([
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name,
                'admission_number' => $student->admission_number,
                'class' => $student->class->name,
                'section' => $student->section->name,
                'roll_number' => $student->roll_number,
            ],
            'today' => [
                'status' => $todayAttendance?->status ?? 'not_marked',
                'status_text' => $todayAttendance ? ucfirst($todayAttendance->status) : 'Not Marked',
                'check_in' => $todayAttendance?->check_in_time?->format('h:i A'),
                'check_out' => $todayAttendance?->check_out_time?->format('h:i A'),
                'remarks' => $todayAttendance?->remarks,
            ],
            'current_month' => $monthlyStats,
            'current_year' => $yearlyStats,
            'overall' => $overallStats,
            'academic_year' => $academicYearStats,
            'monthly_breakdown' => $monthlyBreakdown,
            'weekly_breakdown' => $weeklyBreakdown,
            'projections' => [
                'working_days_in_month' => $workingDaysInMonth,
                'attendance_recorded' => $monthlyStats['total_days'],
                'days_remaining' => $remainingDays,
                'current_percentage' => $monthlyStats['percentage'],
                'projected_percentage' => $projectedPercentage,
                'needs_to_improve' => $projectedPercentage < 75,
            ],
            'comparison' => [
                'student_percentage' => $overallStats['percentage'],
                'class_average' => $classAverage,
                'difference' => round($overallStats['percentage'] - $classAverage, 2),
                'is_above_average' => $overallStats['percentage'] > $classAverage,
            ],
            'trend' => $this->getAttendanceTrend($student),
            'recommendations' => $this->getAttendanceRecommendations($monthlyStats['percentage']),
        ], 'Attendance summary retrieved successfully');
    }

    /**
     * Get class average attendance
     */
    private function getClassAverageAttendance($student)
    {
        $classStudents = Student::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->pluck('id');
        
        $totalAttendance = 0;
        $studentCount = $classStudents->count();
        
        foreach ($classStudents as $studentId) {
            $attendance = Attendance::where('attendable_type', Student::class)
                ->where('attendable_id', $studentId)
                ->get();
            
            $total = $attendance->count();
            $present = $attendance->where('status', 'present')->count();
            $percentage = $total > 0 ? ($present / $total) * 100 : 0;
            $totalAttendance += $percentage;
        }
        
        return $studentCount > 0 ? round($totalAttendance / $studentCount, 2) : 0;
    }
    
    /**
     * Get attendance trend (improving/declining/stable)
     */
    private function getAttendanceTrend($student)
    {
        $trend = [];
        $months = [];
        
        for ($i = 2; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthAttendances = Attendance::where('attendable_type', Student::class)
                ->where('attendable_id', $student->id)
                ->whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->get();
            
            $percentage = $monthAttendances->count() > 0 
                ? round(($monthAttendances->where('status', 'present')->count() / $monthAttendances->count()) * 100, 2) 
                : 0;
            
            $months[] = $month->format('M');
            $trend[] = $percentage;
        }
        
        // Determine trend direction
        if (count($trend) >= 2) {
            $first = $trend[0];
            $last = $trend[count($trend) - 1];
            
            if ($last > $first + 5) {
                $direction = 'improving';
                $message = 'Attendance is showing positive improvement!';
            } elseif ($last < $first - 5) {
                $direction = 'declining';
                $message = 'Attendance has been declining. Please ensure regular attendance.';
            } else {
                $direction = 'stable';
                $message = 'Attendance is stable. Keep it up!';
            }
        } else {
            $direction = 'insufficient_data';
            $message = 'Not enough data to determine trend.';
        }
        
        return [
            'direction' => $direction,
            'message' => $message,
            'data_points' => $trend,
            'months' => $months,
        ];
    }

     /**
     * Get attendance recommendations based on percentage
     */
    private function getAttendanceRecommendations($percentage)
    {
        if ($percentage >= 90) {
            return [
                'level' => 'excellent',
                'message' => 'Excellent attendance! Keep up the great work.',
                'color' => 'success',
                'suggestions' => [
                    'Continue maintaining regular attendance',
                    'Serve as a role model for other students',
                ],
            ];
        } elseif ($percentage >= 75) {
            return [
                'level' => 'good',
                'message' => 'Good attendance. Aim for 90%+ for excellence.',
                'color' => 'info',
                'suggestions' => [
                    'Try to avoid unnecessary absences',
                    'Plan vacations during school holidays',
                ],
            ];
        } elseif ($percentage >= 60) {
            return [
                'level' => 'needs_improvement',
                'message' => 'Attendance needs improvement. Regular attendance is crucial for academic success.',
                'color' => 'warning',
                'suggestions' => [
                    'Ensure regular attendance unless unwell',
                    'Communicate with teachers about missed work',
                    'Set a routine for waking up and reaching school on time',
                ],
            ];
        } else {
            return [
                'level' => 'critical',
                'message' => 'Attendance is critically low. Immediate attention required.',
                'color' => 'danger',
                'suggestions' => [
                    'Schedule a meeting with the class teacher',
                    'Discuss any challenges affecting attendance',
                    'Create a daily attendance plan',
                    'Monitor attendance weekly',
                ],
            ];
        }
    }

        public function resultDetail(Student $student, $examId)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $exam = Exam::with('examType')->find($examId);
        if (!$exam) {
            return $this->sendError('Exam not found', [], 404);
        }
        
        $results = ExamResult::where('student_id', $student->id)
            ->whereHas('examSchedule', function($q) use ($examId) {
                $q->where('exam_id', $examId);
            })
            ->with(['examSchedule.subject'])
            ->get();
        
        if ($results->isEmpty()) {
            return $this->sendError('No results found for this exam', [], 404);
        }
        
        // Calculate totals
        $totalObtained = $results->sum('total_marks_obtained');
        $totalMaxMarks = $results->sum(function($r) {
            return $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0);
        });
        $overallPercentage = $totalMaxMarks > 0 ? round(($totalObtained / $totalMaxMarks) * 100, 2) : 0;
        
        // Subject-wise performance
        $subjectPerformance = $results->map(function($result) {
            return [
                'subject_id' => $result->examSchedule->subject_id,
                'subject_name' => $result->examSchedule->subject->name,
                'subject_code' => $result->examSchedule->subject->code,
                'theory_marks' => $result->theory_marks_obtained,
                'practical_marks' => $result->practical_marks_obtained,
                'total_obtained' => $result->total_marks_obtained,
                'max_marks' => $result->examSchedule->total_marks + ($result->examSchedule->practical_marks ?? 0),
                'percentage' => $result->percentage,
                'grade' => $result->grade,
                'remarks' => $result->remarks,
            ];
        });
        
        // Get rank if available
        $rank = $this->getStudentRank($exam, $student);
        
        // Get class average
        $classAverage = $this->getClassAverage($exam, $student);
        
        // Get topper information
        $topper = $this->getExamTopper($exam, $student);
        
        return $this->sendResponse([
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'type' => $exam->examType->name ?? null,
                'description' => $exam->description,
                'start_date' => $exam->start_date->toDateString(),
                'end_date' => $exam->end_date->toDateString(),
                'status' => $exam->status,
            ],
            'summary' => [
                'total_obtained' => $totalObtained,
                'total_max' => $totalMaxMarks,
                'percentage' => $overallPercentage,
                'grade' => $this->calculateGrade($overallPercentage),
                'rank' => $rank,
                'class_average' => $classAverage,
                'topper_percentage' => $topper ? $topper['percentage'] : null,
            ],
            'subjects' => $subjectPerformance,
            'topper_info' => $topper,
        ], 'Exam result details retrieved successfully');
    }

    /**
     * Get student rank in exam
     */
    private function getStudentRank($exam, $student)
    {
        $allResults = ExamResult::whereHas('examSchedule', function($q) use ($exam) {
            $q->where('exam_id', $exam->id);
        })->get();
        
        $studentPercentages = [];
        foreach ($allResults->groupBy('student_id') as $studentId => $results) {
            $totalObtained = $results->sum('total_marks_obtained');
            $totalMax = $results->sum(function($r) {
                return $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0);
            });
            $studentPercentages[$studentId] = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
        }
        
        arsort($studentPercentages);
        $rank = 1;
        foreach ($studentPercentages as $studentId => $percentage) {
            if ($studentId == $student->id) {
                return $rank;
            }
            $rank++;
        }
        return null;
    }
    
    /**
     * Get class average for exam
     */
    private function getClassAverage($exam, $student)
    {
        $allResults = ExamResult::whereHas('examSchedule', function($q) use ($exam) {
            $q->where('exam_id', $exam->id);
        })->get();
        
        $totalPercentage = $allResults->sum('percentage');
        $count = $allResults->count();
        return $count > 0 ? round($totalPercentage / $count, 2) : 0;
    }
    
    /**
     * Get exam topper information
     */
    private function getExamTopper($exam, $student)
    {
        $allResults = ExamResult::whereHas('examSchedule', function($q) use ($exam) {
            $q->where('exam_id', $exam->id);
        })->get();
        
        $studentPercentages = [];
        foreach ($allResults->groupBy('student_id') as $studentId => $results) {
            $totalObtained = $results->sum('total_marks_obtained');
            $totalMax = $results->sum(function($r) {
                return $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0);
            });
            $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
            $studentPercentages[$studentId] = $percentage;
        }
        
        arsort($studentPercentages);
        $topperId = key($studentPercentages);
        $topperPercentage = reset($studentPercentages);
        
        if ($topperId) {
            $topperStudent = Student::with('user')->find($topperId);
            return [
                'name' => $topperStudent->user->name,
                'percentage' => round($topperPercentage, 2),
                'admission_number' => $topperStudent->admission_number,
            ];
        }
        
        return null;
    }
    
    /**
     * Get subject performance trend
     */
    private function getSubjectTrend($subjectResults)
    {
        $trend = [];
        foreach ($subjectResults->sortBy('examSchedule.exam_date') as $result) {
            $trend[] = [
                'exam_name' => $result->examSchedule->exam->name,
                'percentage' => $result->percentage,
                'grade' => $result->grade,
            ];
        }
        return $trend;
    }
    
    /**
     * Generate recommendations based on performance
     */
    private function generateRecommendations($subjectPerformance)
    {
        $recommendations = [];
        
        // Find weak subjects (below 60%)
        $weakSubjects = array_filter($subjectPerformance, function($subject) {
            return $subject['average_percentage'] < 60;
        });
        
        if (!empty($weakSubjects)) {
            $subjectNames = array_column($weakSubjects, 'subject_name');
            $recommendations[] = [
                'type' => 'weak_subjects',
                'message' => 'Focus on improving in: ' . implode(', ', array_slice($subjectNames, 0, 3)),
                'subjects' => array_slice($weakSubjects, 0, 3),
            ];
        }
        
        // Find strong subjects (above 80%)
        $strongSubjects = array_filter($subjectPerformance, function($subject) {
            return $subject['average_percentage'] >= 80;
        });
        
        if (!empty($strongSubjects)) {
            $subjectNames = array_column($strongSubjects, 'subject_name');
            $recommendations[] = [
                'type' => 'strong_subjects',
                'message' => 'Excellent performance in: ' . implode(', ', array_slice($subjectNames, 0, 3)),
                'subjects' => array_slice($strongSubjects, 0, 3),
            ];
        }
        
        // Check for consistent improvement
        $improvingSubjects = array_filter($subjectPerformance, function($subject) {
            if (count($subject['performance_trend']) >= 2) {
                $trend = array_column($subject['performance_trend'], 'percentage');
                return end($trend) > reset($trend);
            }
            return false;
        });
        
        if (!empty($improvingSubjects)) {
            $recommendations[] = [
                'type' => 'improving',
                'message' => 'Showing improvement! Keep up the good work.',
            ];
        }
        
        // General recommendations
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'general',
                'message' => 'Consistent performance. Keep maintaining your study routine.',
            ];
        }
        
        return $recommendations;
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

    /**
     * Get performance summary with analytics
     */
    public function resultSummary(Student $student, Request $request)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $year = $request->year ?? Carbon::now()->year;
        
        // Get all results
        $allResults = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get();
        
        // Subject-wise performance
        $subjectPerformance = [];
        foreach ($allResults->groupBy('examSchedule.subject_id') as $subjectId => $subjectResults) {
            $subject = $subjectResults->first()->examSchedule->subject;
            $subjectPerformance[] = [
                'subject_id' => $subjectId,
                'subject_name' => $subject->name,
                'subject_code' => $subject->code,
                'average_percentage' => round($subjectResults->avg('percentage'), 2),
                'best_percentage' => round($subjectResults->max('percentage'), 2),
                'worst_percentage' => round($subjectResults->min('percentage'), 2),
                'exams_count' => $subjectResults->count(),
                'grade' => $this->calculateGrade($subjectResults->avg('percentage')),
                'performance_trend' => $this->getSubjectTrend($subjectResults),
            ];
        }
        
        // Sort by average percentage
        usort($subjectPerformance, function($a, $b) {
            return $b['average_percentage'] - $a['average_percentage'];
        });
        
        // Exam-wise trend
        $examTrend = [];
        foreach ($allResults->groupBy('examSchedule.exam_id') as $examId => $examResults) {
            $exam = $examResults->first()->examSchedule->exam;
            $examTrend[] = [
                'exam_id' => $examId,
                'exam_name' => $exam->name,
                'exam_date' => $exam->start_date->format('M Y'),
                'average_percentage' => round($examResults->avg('percentage'), 2),
                'subjects_count' => $examResults->count(),
            ];
        }
        
        // Sort by exam date
        usort($examTrend, function($a, $b) {
            return strtotime($a['exam_date']) - strtotime($b['exam_date']);
        });
        
        // Monthly performance
        $monthlyPerformance = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthResults = $allResults->filter(function($result) use ($month, $year) {
                return $result->examSchedule->exam->start_date->month == $month && 
                       $result->examSchedule->exam->start_date->year == $year;
            });
            
            if ($monthResults->isNotEmpty()) {
                $monthlyPerformance[] = [
                    'month' => Carbon::createFromDate($year, $month, 1)->format('F'),
                    'average_percentage' => round($monthResults->avg('percentage'), 2),
                    'exams_count' => $monthResults->groupBy('examSchedule.exam_id')->count(),
                ];
            }
        }
        
        // Overall statistics
        $totalSubjects = $allResults->count();
        $stats = [
            'total_exams' => $allResults->groupBy('examSchedule.exam_id')->count(),
            'total_subjects' => $totalSubjects,
            'overall_percentage' => round($allResults->avg('percentage'), 2),
            'overall_grade' => $this->calculateGrade($allResults->avg('percentage')),
            'best_subject' => !empty($subjectPerformance) ? $subjectPerformance[0] : null,
            'needs_improvement' => !empty($subjectPerformance) ? end($subjectPerformance) : null,
            'pass_percentage' => $totalSubjects > 0 
                ? round(($allResults->filter(function($r) { return $r->percentage >= 40; })->count() / $totalSubjects) * 100, 2) 
                : 0,
            'grade_distribution' => [
                'A+' => $allResults->filter(fn($r) => $r->percentage >= 90)->count(),
                'A' => $allResults->filter(fn($r) => $r->percentage >= 80 && $r->percentage < 90)->count(),
                'B+' => $allResults->filter(fn($r) => $r->percentage >= 70 && $r->percentage < 80)->count(),
                'B' => $allResults->filter(fn($r) => $r->percentage >= 60 && $r->percentage < 70)->count(),
                'C' => $allResults->filter(fn($r) => $r->percentage >= 50 && $r->percentage < 60)->count(),
                'D' => $allResults->filter(fn($r) => $r->percentage >= 40 && $r->percentage < 50)->count(),
                'F' => $allResults->filter(fn($r) => $r->percentage < 40)->count(),
            ],
        ];
        
        // Chart data for progress
        $chartData = [
            'labels' => collect($examTrend)->pluck('exam_name')->toArray(),
            'percentages' => collect($examTrend)->pluck('average_percentage')->toArray(),
        ];
        
        // Available years
        $years = $allResults->pluck('examSchedule.exam.start_date')
            ->filter()
            ->map(fn($date) => $date->year)
            ->unique()
            ->sort()
            ->values();
        
        // Recommendations based on performance
        $recommendations = $this->generateRecommendations($subjectPerformance);
        
        return $this->sendResponse([
            'overall_stats' => $stats,
            'subject_wise' => $subjectPerformance,
            'exam_trend' => $examTrend,
            'monthly_performance' => $monthlyPerformance,
            'chart_data' => $chartData,
            'available_years' => $years,
            'recommendations' => $recommendations,
        ], 'Performance summary retrieved successfully');
    }

    public function homeworkDetail(Student $student, Homework $homework)
    {
        $parent = Auth::user()->parent;
        
        // Verify this child belongs to the parent
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized access to this student', [], 403);
        }
        
        // Verify homework belongs to student's class and section
        if ($homework->class_id != $student->class_id || $homework->section_id != $student->section_id) {
            return $this->sendError('Homework not found for this student', [], 404);
        }
        
        // Get student's submission if exists
        $submission = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();
        
        // Get attachments
        $homeworkAttachments = json_decode($homework->attachments, true) ?? [];
        $submissionAttachments = $submission ? $submission->attachments : [];
        
        // Calculate days remaining or overdue status
        $today = Carbon::today();
        $dueDate = Carbon::parse($homework->submission_date);
        $isOverdue = $dueDate->isPast() && !$submission;
        $daysRemaining = $today->diffInDays($dueDate, false);
        
        // Get submission status text
        $submissionStatus = null;
        $submissionStatusColor = null;
        if ($submission) {
            switch ($submission->status) {
                case 'submitted':
                    $submissionStatus = 'Submitted (Pending Grading)';
                    $submissionStatusColor = 'info';
                    break;
                case 'late':
                    $submissionStatus = 'Submitted (Late)';
                    $submissionStatusColor = 'warning';
                    break;
                case 'graded':
                    $submissionStatus = 'Graded';
                    $submissionStatusColor = 'success';
                    break;
                default:
                    $submissionStatus = ucfirst($submission->status);
                    $submissionStatusColor = 'secondary';
            }
        }
        
        // Calculate percentage if graded
        $percentage = null;
        if ($submission && $submission->status == 'graded' && $homework->total_marks > 0) {
            $percentage = round(($submission->obtained_marks / $homework->total_marks) * 100, 2);
        }
        
        // Get teacher information
        $teacher = $homework->teacher;
        
        return $this->sendResponse([
            'homework' => [
                'id' => $homework->id,
                'title' => $homework->title,
                'description' => $homework->description,
                'subject' => [
                    'id' => $homework->subject->id,
                    'name' => $homework->subject->name,
                    'code' => $homework->subject->code,
                ],
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'profile_photo' => $teacher->profile_photo_url,
                ],
                'submission_date' => $dueDate->toDateString(),
                'submission_date_formatted' => $dueDate->format('l, F j, Y'),
                'total_marks' => $homework->total_marks,
                'attachments' => array_map(function($attachment) {
                    return [
                        'name' => $attachment['name'],
                        'url' => asset('storage/' . $attachment['path']),
                        'size' => isset($attachment['size']) ? $this->formatBytes($attachment['size']) : null,
                    ];
                }, $homeworkAttachments),
                'status' => $homework->status,
                'is_overdue' => $isOverdue,
                'days_remaining' => $daysRemaining,
            ],
            'submission' => $submission ? [
                'id' => $submission->id,
                'submitted_at' => $submission->submitted_at->toDateTimeString(),
                'submitted_at_formatted' => $submission->submitted_at->format('F j, Y \a\t h:i A'),
                'is_late' => $submission->is_late,
                'status' => $submission->status,
                'status_text' => $submissionStatus,
                'status_color' => $submissionStatusColor,
                'submission_text' => $submission->submission_text,
                'attachments' => is_array($submissionAttachments) ? array_map(function($attachment) {
                        return [
                            'name' => $attachment['name'],
                            'url' => asset('storage/' . $attachment['path']),
                            'size' => isset($attachment['size']) ? $this->formatBytes($attachment['size']) : null,
                        ];
                    }, $submissionAttachments) : [],
                'obtained_marks' => $submission->obtained_marks,
                'percentage' => $percentage,
                'percentage_formatted' => $percentage ? $percentage . '%' : null,
                'feedback' => $submission->feedback,
                'grade' => $this->getGradeLetter($percentage ?? 0),
            ] : null,
        ], 'Homework details retrieved successfully');
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Get grade letter based on percentage
     */
    private function getGradeLetter($percentage)
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C';
        if ($percentage >= 40) return 'D';
        return 'F';
    }
}