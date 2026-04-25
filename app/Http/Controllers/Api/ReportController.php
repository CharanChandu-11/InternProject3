<?php
// app/Http/Controllers/Api/ReportController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Payment;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Classes;
use App\Models\Section;
use App\Models\FeeStructure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends BaseController
{
    /**
     * Attendance Report
     */
    public function attendance(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'student_id' => 'nullable|exists:students,id',
            'format' => 'nullable|in:summary,detailed,export',
        ]);

        $user = Auth::user();
        $format = $request->format ?? 'summary';

        // Check permissions based on user type
        $this->validateAttendanceReportAccess($user, $request);

        switch ($format) {
            case 'summary':
                return $this->attendanceSummaryReport($request);
            case 'detailed':
                return $this->attendanceDetailedReport($request);
            case 'export':
                return $this->attendanceExportReport($request);
            default:
                return $this->attendanceSummaryReport($request);
        }
    }

    /**
     * Fees Report
     */
    public function fees(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'student_id' => 'nullable|exists:students,id',
            'status' => 'nullable|in:paid,pending,partial,overdue,all',
            'format' => 'nullable|in:summary,detailed,collection,outstanding',
        ]);

        $user = Auth::user();
        $format = $request->format ?? 'summary';

        // Check permissions
        $this->validateFeesReportAccess($user, $request);

        switch ($format) {
            case 'summary':
                return $this->feesSummaryReport($request);
            case 'detailed':
                return $this->feesDetailedReport($request);
            case 'collection':
                return $this->feesCollectionReport($request);
            case 'outstanding':
                return $this->feesOutstandingReport($request);
            default:
                return $this->feesSummaryReport($request);
        }
    }

    /**
     * Exam Results Report
     */
    public function examResults(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'student_id' => 'nullable|exists:students,id',
            'format' => 'nullable|in:summary,detailed,rankings,subject-wise',
        ]);

        $user = Auth::user();
        $format = $request->format ?? 'summary';

        // Check permissions
        $this->validateExamReportAccess($user, $request);

        switch ($format) {
            case 'summary':
                return $this->examSummaryReport($request);
            case 'detailed':
                return $this->examDetailedReport($request);
            case 'rankings':
                return $this->examRankingsReport($request);
            case 'subject-wise':
                return $this->examSubjectWiseReport($request);
            default:
                return $this->examSummaryReport($request);
        }
    }

    /**
     * Student Performance Report
     */
    public function studentPerformance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'format' => 'nullable|in:overall,term-wise,subject-wise',
        ]);

        $student = Student::with(['user', 'class', 'section'])->findOrFail($request->student_id);
        $format = $request->format ?? 'overall';

        switch ($format) {
            case 'overall':
                return $this->studentOverallPerformance($student, $request);
            case 'term-wise':
                return $this->studentTermWisePerformance($student, $request);
            case 'subject-wise':
                return $this->studentSubjectWisePerformance($student, $request);
            default:
                return $this->studentOverallPerformance($student, $request);
        }
    }

    /**
     * Teacher Performance Report
     */
    public function teacherPerformance(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $teacher = User::with(['employee', 'teachingSubjects'])->findOrFail($request->teacher_id);
        
        return $this->sendResponse($this->getTeacherPerformanceData($teacher, $request), 'Teacher performance report retrieved');
    }

    /**
     * Financial Report
     */
    public function financial(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'type' => 'nullable|in:revenue,expenses,profit-loss',
        ]);

        $type = $request->type ?? 'revenue';

        switch ($type) {
            case 'revenue':
                return $this->financialRevenueReport($request);
            case 'expenses':
                return $this->financialExpensesReport($request);
            case 'profit-loss':
                return $this->financialProfitLossReport($request);
            default:
                return $this->financialRevenueReport($request);
        }
    }

    /**
     * Attendance Overview Report
     */
    public function attendanceOverview(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000',
        ]);

        return $this->sendResponse($this->getAttendanceOverviewData($request), 'Attendance overview retrieved');
    }

    // ==================== ATTENDANCE REPORT METHODS ====================

    private function attendanceSummaryReport($request)
    {
        $query = Attendance::with(['attendable.user', 'attendable.class', 'attendable.section'])
            ->whereBetween('attendance_date', [$request->from_date, $request->to_date]);

        // Apply filters
        $this->applyAttendanceFilters($query, $request);

        $attendances = $query->get();

        $summary = [
            'period' => [
                'from' => $request->from_date,
                'to' => $request->to_date,
            ],
            'total_records' => $attendances->count(),
            'summary' => [
                'present' => $attendances->where('status', 'present')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'half_day' => $attendances->where('status', 'half_day')->count(),
            ],
            'percentage' => [
                'present' => $attendances->count() > 0 ? round(($attendances->where('status', 'present')->count() / $attendances->count()) * 100, 2) : 0,
                'absent' => $attendances->count() > 0 ? round(($attendances->where('status', 'absent')->count() / $attendances->count()) * 100, 2) : 0,
                'late' => $attendances->count() > 0 ? round(($attendances->where('status', 'late')->count() / $attendances->count()) * 100, 2) : 0,
            ],
            'daily_breakdown' => $this->getDailyAttendanceBreakdown($attendances),
            'class_wise' => $this->getClassWiseAttendance($attendances),
        ];

        return $this->sendResponse($summary, 'Attendance summary report generated');
    }

    private function attendanceDetailedReport($request)
    {
        $query = Attendance::with(['attendable.user', 'attendable.class', 'attendable.section'])
            ->whereBetween('attendance_date', [$request->from_date, $request->to_date]);

        $this->applyAttendanceFilters($query, $request);

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('attendable_id')
            ->paginate($request->per_page ?? 50);

        return $this->sendPaginatedResponse($attendances, 'Detailed attendance report');
    }

    private function attendanceExportReport($request)
    {
        $query = Attendance::with(['attendable.user', 'attendable.class', 'attendable.section'])
            ->whereBetween('attendance_date', [$request->from_date, $request->to_date]);

        $this->applyAttendanceFilters($query, $request);

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('attendable_id')
            ->get();

        $exportData = $attendances->map(function($attendance) {
            return [
                'Student Name' => $attendance->attendable->user->name,
                'Admission Number' => $attendance->attendable->admission_number,
                'Class' => $attendance->attendable->class->name,
                'Section' => $attendance->attendable->section->name,
                'Date' => $attendance->attendance_date->format('Y-m-d'),
                'Status' => ucfirst($attendance->status),
                'Check In' => $attendance->check_in_time?->format('H:i:s'),
                'Check Out' => $attendance->check_out_time?->format('H:i:s'),
                'Remarks' => $attendance->remarks,
            ];
        });

        return $this->sendResponse([
            'data' => $exportData,
            'total_records' => $exportData->count(),
        ], 'Attendance export data prepared');
    }

    // ==================== FEES REPORT METHODS ====================

    private function feesSummaryReport($request)
    {
        $query = Payment::with(['student.user', 'student.class', 'student.section', 'studentFee.feeStructure.feeCategory'])
            ->whereBetween('payment_date', [$request->from_date, $request->to_date]);

        $this->applyFeesFilters($query, $request);

        $payments = $query->get();

        $summary = [
            'period' => [
                'from' => $request->from_date,
                'to' => $request->to_date,
            ],
            'total_collection' => $payments->sum('amount'),
            'total_collection_formatted' => '₹ ' . number_format($payments->sum('amount'), 2),
            'payment_methods' => $payments->groupBy('payment_method')->map(function($items) {
                return [
                    'count' => $items->count(),
                    'amount' => $items->sum('amount'),
                    'amount_formatted' => '₹ ' . number_format($items->sum('amount'), 2),
                ];
            }),
            'daily_collection' => $this->getDailyCollection($payments),
            'class_wise' => $this->getClassWiseCollection($payments),
            'fee_category_wise' => $this->getFeeCategoryWiseCollection($payments),
        ];

        return $this->sendResponse($summary, 'Fees summary report generated');
    }

    private function feesDetailedReport($request)
    {
        $query = Payment::with(['student.user', 'student.class', 'student.section', 'studentFee.feeStructure.feeCategory'])
            ->whereBetween('payment_date', [$request->from_date, $request->to_date]);

        $this->applyFeesFilters($query, $request);

        $payments = $query->orderBy('payment_date', 'desc')
            ->paginate($request->per_page ?? 50);

        return $this->sendPaginatedResponse($payments, 'Detailed fees report');
    }

    private function feesCollectionReport($request)
    {
        $query = Payment::with(['student.user', 'student.class', 'student.section'])
            ->whereBetween('payment_date', [$request->from_date, $request->to_date]);

        $this->applyFeesFilters($query, $request);

        $payments = $query->get();

        $monthlyCollection = [];
        for ($i = 0; $i < 12; $i++) {
            $month = Carbon::parse($request->from_date)->addMonths($i);
            if ($month > Carbon::parse($request->to_date)) break;

            $monthlyPayments = $payments->filter(function($payment) use ($month) {
                return Carbon::parse($payment->payment_date)->format('Y-m') == $month->format('Y-m');
            });

            $monthlyCollection[] = [
                'month' => $month->format('F Y'),
                'amount' => $monthlyPayments->sum('amount'),
                'amount_formatted' => '₹ ' . number_format($monthlyPayments->sum('amount'), 2),
                'count' => $monthlyPayments->count(),
            ];
        }

        return $this->sendResponse([
            'collection' => $monthlyCollection,
            'total' => $payments->sum('amount'),
            'total_formatted' => '₹ ' . number_format($payments->sum('amount'), 2),
        ], 'Collection report generated');
    }

    private function feesOutstandingReport($request)
    {
        $query = DB::table('student_fees')
            ->join('students', 'student_fees.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->join('sections', 'students.section_id', '=', 'sections.id')
            ->join('fee_structures', 'student_fees.fee_structure_id', '=', 'fee_structures.id')
            ->join('fee_categories', 'fee_structures.fee_category_id', '=', 'fee_categories.id')
            ->select(
                'students.id as student_id',
                'users.name as student_name',
                'students.admission_number',
                'classes.name as class_name',
                'sections.name as section_name',
                'fee_categories.name as fee_category',
                'student_fees.total_amount',
                'student_fees.paid_amount',
                'student_fees.due_amount',
                'student_fees.due_date',
                'student_fees.status'
            )
            ->where('student_fees.due_amount', '>', 0);

        // Apply filters
        if ($request->has('class_id')) {
            $query->where('students.class_id', $request->class_id);
        }
        if ($request->has('section_id')) {
            $query->where('students.section_id', $request->section_id);
        }
        if ($request->has('student_id')) {
            $query->where('students.id', $request->student_id);
        }

        $outstanding = $query->orderBy('student_fees.due_date')
            ->get()
            ->groupBy('student_id');

        $formattedOutstanding = [];
        foreach ($outstanding as $studentId => $fees) {
            $student = $fees->first();
            $formattedOutstanding[] = [
                'student_id' => $studentId,
                'student_name' => $student->student_name,
                'admission_number' => $student->admission_number,
                'class' => $student->class_name,
                'section' => $student->section_name,
                'total_due' => $fees->sum('due_amount'),
                'total_due_formatted' => '₹ ' . number_format($fees->sum('due_amount'), 2),
                'fees_breakdown' => $fees->map(function($fee) {
                    return [
                        'category' => $fee->fee_category,
                        'total_amount' => $fee->total_amount,
                        'paid_amount' => $fee->paid_amount,
                        'due_amount' => $fee->due_amount,
                        'due_date' => $fee->due_date,
                        'status' => $fee->status,
                    ];
                }),
            ];
        }

        return $this->sendResponse([
            'total_outstanding' => $formattedOutstanding->sum('total_due'),
            'total_outstanding_formatted' => '₹ ' . number_format($formattedOutstanding->sum('total_due'), 2),
            'students' => $formattedOutstanding,
        ], 'Outstanding fees report generated');
    }

    // ==================== EXAM RESULTS REPORT METHODS ====================

    private function examSummaryReport($request)
    {
        $query = ExamResult::with(['student.user', 'examSchedule.subject', 'examSchedule.exam'])
            ->whereHas('examSchedule', function($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            });

        $this->applyExamFilters($query, $request);

        $results = $query->get();

        $summary = [
            'exam_id' => $request->exam_id,
            'exam_name' => $results->first()?->examSchedule->exam->name,
            'total_students' => $results->groupBy('student_id')->count(),
            'total_subjects' => $results->groupBy('examSchedule.subject_id')->count(),
            'overall_average' => $results->avg('percentage'),
            'pass_percentage' => $this->calculatePassPercentage($results),
            'grade_distribution' => $this->getGradeDistribution($results),
            'top_performers' => $this->getTopPerformers($results, 10),
        ];

        return $this->sendResponse($summary, 'Exam summary report generated');
    }

    private function examDetailedReport($request)
    {
        $query = ExamResult::with(['student.user', 'examSchedule.subject', 'examSchedule.exam'])
            ->whereHas('examSchedule', function($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            });

        $this->applyExamFilters($query, $request);

        $results = $query->orderBy('student_id')
            ->orderBy('examSchedule.subject_id')
            ->get()
            ->groupBy('student_id');

        $detailedReport = [];
        foreach ($results as $studentId => $studentResults) {
            $student = $studentResults->first()->student;
            $detailedReport[] = [
                'student_id' => $studentId,
                'student_name' => $student->user->name,
                'admission_number' => $student->admission_number,
                'class' => $student->class->name,
                'section' => $student->section->name,
                'subjects' => $studentResults->map(function($result) {
                    return [
                        'subject' => $result->examSchedule->subject->name,
                        'marks' => $result->total_marks_obtained,
                        'max_marks' => $result->examSchedule->total_marks,
                        'percentage' => $result->percentage,
                        'grade' => $result->grade,
                    ];
                }),
                'total_marks' => $studentResults->sum('total_marks_obtained'),
                'total_max_marks' => $studentResults->sum('examSchedule.total_marks'),
                'percentage' => $studentResults->avg('percentage'),
                'grade' => $this->calculateOverallGrade($studentResults->avg('percentage')),
                'rank' => $studentResults->first()->rank,
            ];
        }

        // Sort by rank
        usort($detailedReport, function($a, $b) {
            return $a['rank'] - $b['rank'];
        });

        return $this->sendResponse($detailedReport, 'Detailed exam report generated');
    }

    private function examRankingsReport($request)
    {
        $query = ExamResult::with(['student.user', 'examSchedule.subject'])
            ->whereHas('examSchedule', function($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            });

        $this->applyExamFilters($query, $request);

        $results = $query->get()->groupBy('student_id');

        $rankings = [];
        foreach ($results as $studentId => $studentResults) {
            $student = $studentResults->first()->student;
            $rankings[] = [
                'rank' => $studentResults->first()->rank,
                'student_id' => $studentId,
                'student_name' => $student->user->name,
                'admission_number' => $student->admission_number,
                'class' => $student->class->name,
                'section' => $student->section->name,
                'total_marks' => $studentResults->sum('total_marks_obtained'),
                'percentage' => $studentResults->avg('percentage'),
                'grade' => $this->calculateOverallGrade($studentResults->avg('percentage')),
            ];
        }

        // Sort by rank
        usort($rankings, function($a, $b) {
            return $a['rank'] - $b['rank'];
        });

        return $this->sendResponse([
            'rankings' => $rankings,
            'total_students' => count($rankings),
        ], 'Exam rankings report generated');
    }

    private function examSubjectWiseReport($request)
    {
        $query = ExamResult::with(['student.user', 'examSchedule.subject'])
            ->whereHas('examSchedule', function($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            });

        $this->applyExamFilters($query, $request);

        $results = $query->get()->groupBy('examSchedule.subject_id');

        $subjectWise = [];
        foreach ($results as $subjectId => $subjectResults) {
            $subject = $subjectResults->first()->examSchedule->subject;
            $subjectWise[] = [
                'subject_id' => $subjectId,
                'subject_name' => $subject->name,
                'subject_code' => $subject->code,
                'total_students' => $subjectResults->count(),
                'average_marks' => $subjectResults->avg('total_marks_obtained'),
                'average_percentage' => $subjectResults->avg('percentage'),
                'highest_marks' => $subjectResults->max('total_marks_obtained'),
                'lowest_marks' => $subjectResults->min('total_marks_obtained'),
                'pass_count' => $subjectResults->filter(function($result) {
                    return $result->percentage >= 40;
                })->count(),
                'fail_count' => $subjectResults->filter(function($result) {
                    return $result->percentage < 40;
                })->count(),
                'grade_distribution' => $this->getSubjectGradeDistribution($subjectResults),
            ];
        }

        return $this->sendResponse($subjectWise, 'Subject-wise exam report generated');
    }

    // ==================== STUDENT PERFORMANCE METHODS ====================

    private function studentOverallPerformance($student, $request)
    {
        $examResults = $student->examResults()
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get();

        $performance = [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name,
                'admission_number' => $student->admission_number,
                'class' => $student->class->name,
                'section' => $student->section->name,
                'roll_number' => $student->roll_number,
            ],
            'attendance' => [
                'overall' => $student->attendance_percentage,
                'monthly' => $this->getStudentMonthlyAttendance($student),
            ],
            'academic' => [
                'average_percentage' => $examResults->avg('percentage'),
                'best_performance' => $examResults->max('percentage'),
                'worst_performance' => $examResults->min('percentage'),
                'total_exams' => $examResults->groupBy('examSchedule.exam_id')->count(),
                'grade_distribution' => $this->getStudentGradeDistribution($examResults),
            ],
            'subject_performance' => $this->getStudentSubjectPerformance($examResults),
            'exam_trend' => $this->getStudentExamTrend($examResults),
        ];

        return $this->sendResponse($performance, 'Student performance report generated');
    }

    private function studentTermWisePerformance($student, $request)
    {
        $termResults = $student->examResults()
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get()
            ->groupBy('examSchedule.exam.term');

        $termWise = [];
        foreach ($termResults as $term => $results) {
            $termWise[] = [
                'term' => $term,
                'total_marks' => $results->sum('total_marks_obtained'),
                'percentage' => $results->avg('percentage'),
                'grade' => $this->calculateOverallGrade($results->avg('percentage')),
                'subjects' => $results->groupBy('examSchedule.subject_id')->map(function($subjectResults) {
                    return [
                        'subject' => $subjectResults->first()->examSchedule->subject->name,
                        'marks' => $subjectResults->sum('total_marks_obtained'),
                        'percentage' => $subjectResults->avg('percentage'),
                        'grade' => $this->calculateOverallGrade($subjectResults->avg('percentage')),
                    ];
                })->values(),
            ];
        }

        return $this->sendResponse($termWise, 'Term-wise performance report');
    }

    private function studentSubjectWisePerformance($student, $request)
    {
        $subjectResults = $student->examResults()
            ->with(['examSchedule.subject', 'examSchedule.exam'])
            ->get()
            ->groupBy('examSchedule.subject_id');

        $subjectPerformance = [];
        foreach ($subjectResults as $subjectId => $results) {
            $subject = $results->first()->examSchedule->subject;
            $subjectPerformance[] = [
                'subject' => $subject->name,
                'subject_code' => $subject->code,
                'average_marks' => $results->avg('total_marks_obtained'),
                'average_percentage' => $results->avg('percentage'),
                'highest_marks' => $results->max('total_marks_obtained'),
                'lowest_marks' => $results->min('total_marks_obtained'),
                'exams' => $results->map(function($result) {
                    return [
                        'exam' => $result->examSchedule->exam->name,
                        'marks' => $result->total_marks_obtained,
                        'percentage' => $result->percentage,
                        'grade' => $result->grade,
                    ];
                }),
                'trend' => $this->calculateSubjectTrend($results),
            ];
        }

        return $this->sendResponse($subjectPerformance, 'Subject-wise performance report');
    }

    // ==================== HELPER METHODS ====================

    private function applyAttendanceFilters($query, $request)
    {
        if ($request->has('class_id')) {
            $query->whereHas('attendable', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('section_id')) {
            $query->whereHas('attendable', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        if ($request->has('student_id')) {
            $query->where('attendable_id', $request->student_id);
        }
    }

    private function applyFeesFilters($query, $request)
    {
        if ($request->has('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('section_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    }

    private function applyExamFilters($query, $request)
    {
        if ($request->has('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('section_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }
    }

    private function validateAttendanceReportAccess($user, $request)
    {
        // Implement access control logic based on user type
        if ($user->user_type == 'teacher') {
            // Teacher can only see their own students
            $teacherClasses = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->pluck('class_id');
            
            if ($request->has('class_id') && !$teacherClasses->contains($request->class_id)) {
                abort(403, 'Unauthorized access to this class');
            }
        }
    }

    private function validateFeesReportAccess($user, $request)
    {
        // Implement access control logic
        if ($user->user_type == 'student') {
            $student = $user->student;
            if ($request->has('student_id') && $student->id != $request->student_id) {
                abort(403, 'Unauthorized access');
            }
        }
    }

    private function validateExamReportAccess($user, $request)
    {
        // Implement access control logic
        if ($user->user_type == 'student') {
            $student = $user->student;
            if ($request->has('student_id') && $student->id != $request->student_id) {
                abort(403, 'Unauthorized access');
            }
        }
    }

    private function getDailyAttendanceBreakdown($attendances)
    {
        return $attendances->groupBy(function($attendance) {
            return $attendance->attendance_date->format('Y-m-d');
        })->map(function($dayAttendances) {
            return [
                'total' => $dayAttendances->count(),
                'present' => $dayAttendances->where('status', 'present')->count(),
                'absent' => $dayAttendances->where('status', 'absent')->count(),
                'late' => $dayAttendances->where('status', 'late')->count(),
            ];
        });
    }

    private function getClassWiseAttendance($attendances)
    {
        return $attendances->groupBy(function($attendance) {
            return $attendance->attendable->class->name ?? 'Unknown';
        })->map(function($classAttendances) {
            return [
                'total' => $classAttendances->count(),
                'present' => $classAttendances->where('status', 'present')->count(),
                'absent' => $classAttendances->where('status', 'absent')->count(),
                'percentage' => $classAttendances->count() > 0 ? 
                    round(($classAttendances->where('status', 'present')->count() / $classAttendances->count()) * 100, 2) : 0,
            ];
        });
    }

    private function getDailyCollection($payments)
    {
        return $payments->groupBy(function($payment) {
            return $payment->payment_date->format('Y-m-d');
        })->map(function($dayPayments) {
            return [
                'amount' => $dayPayments->sum('amount'),
                'amount_formatted' => '₹ ' . number_format($dayPayments->sum('amount'), 2),
                'count' => $dayPayments->count(),
            ];
        });
    }

    private function getClassWiseCollection($payments)
    {
        return $payments->groupBy(function($payment) {
            return $payment->student->class->name ?? 'Unknown';
        })->map(function($classPayments) {
            return [
                'amount' => $classPayments->sum('amount'),
                'amount_formatted' => '₹ ' . number_format($classPayments->sum('amount'), 2),
                'count' => $classPayments->count(),
            ];
        });
    }

    private function getFeeCategoryWiseCollection($payments)
    {
        return $payments->groupBy(function($payment) {
            return $payment->studentFee->feeStructure->feeCategory->name ?? 'Unknown';
        })->map(function($categoryPayments) {
            return [
                'amount' => $categoryPayments->sum('amount'),
                'amount_formatted' => '₹ ' . number_format($categoryPayments->sum('amount'), 2),
                'count' => $categoryPayments->count(),
            ];
        });
    }

    private function calculatePassPercentage($results)
    {
        $students = $results->groupBy('student_id');
        $passed = 0;
        
        foreach ($students as $studentResults) {
            $failedSubjects = $studentResults->filter(function($result) {
                return $result->percentage < 40;
            });
            
            if ($failedSubjects->count() == 0) {
                $passed++;
            }
        }
        
        return $students->count() > 0 ? round(($passed / $students->count()) * 100, 2) : 0;
    }

    private function getGradeDistribution($results)
    {
        $grades = ['A+', 'A', 'B+', 'B', 'C', 'D', 'F'];
        $distribution = [];
        
        foreach ($grades as $grade) {
            $distribution[$grade] = 0;
        }
        
        $students = $results->groupBy('student_id');
        foreach ($students as $studentResults) {
            $avgPercentage = $studentResults->avg('percentage');
            $grade = $this->calculateOverallGrade($avgPercentage);
            $distribution[$grade]++;
        }
        
        return $distribution;
    }

    private function getSubjectGradeDistribution($results)
    {
        $grades = ['A+', 'A', 'B+', 'B', 'C', 'D', 'F'];
        $distribution = [];
        
        foreach ($grades as $grade) {
            $distribution[$grade] = 0;
        }
        
        foreach ($results as $result) {
            $distribution[$result->grade]++;
        }
        
        return $distribution;
    }

    private function getTopPerformers($results, $limit = 10)
    {
        $students = $results->groupBy('student_id');
        $performers = [];
        
        foreach ($students as $studentId => $studentResults) {
            $student = $studentResults->first()->student;
            $performers[] = [
                'rank' => $studentResults->first()->rank,
                'student_id' => $studentId,
                'student_name' => $student->user->name,
                'admission_number' => $student->admission_number,
                'percentage' => $studentResults->avg('percentage'),
                'grade' => $this->calculateOverallGrade($studentResults->avg('percentage')),
            ];
        }
        
        usort($performers, function($a, $b) {
            return $a['rank'] - $b['rank'];
        });
        
        return array_slice($performers, 0, $limit);
    }

    private function calculateOverallGrade($percentage)
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

    private function getStudentMonthlyAttendance($student)
    {
        $monthly = [];
        for ($i = 1; $i <= 12; $i++) {
            $attendances = $student->attendances()
                ->whereMonth('attendance_date', $i)
                ->whereYear('attendance_date', now()->year)
                ->get();
            
            $total = $attendances->count();
            $present = $attendances->where('status', 'present')->count();
            
            $monthly[] = [
                'month' => Carbon::createFromDate(null, $i, 1)->format('M'),
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ];
        }
        
        return $monthly;
    }

    private function getStudentGradeDistribution($results)
    {
        $grades = ['A+', 'A', 'B+', 'B', 'C', 'D', 'F'];
        $distribution = [];
        
        foreach ($grades as $grade) {
            $distribution[$grade] = $results->where('grade', $grade)->count();
        }
        
        return $distribution;
    }

    private function getStudentSubjectPerformance($results)
    {
        return $results->groupBy('examSchedule.subject_id')->map(function($subjectResults) {
            $subject = $subjectResults->first()->examSchedule->subject;
            return [
                'subject' => $subject->name,
                'average_percentage' => $subjectResults->avg('percentage'),
                'best' => $subjectResults->max('percentage'),
                'worst' => $subjectResults->min('percentage'),
                'grade' => $this->calculateOverallGrade($subjectResults->avg('percentage')),
            ];
        })->values();
    }

    private function getStudentExamTrend($results)
    {
        return $results->groupBy('examSchedule.exam_id')->map(function($examResults, $examId) {
            $exam = $examResults->first()->examSchedule->exam;
            return [
                'exam' => $exam->name,
                'percentage' => $examResults->avg('percentage'),
                'grade' => $this->calculateOverallGrade($examResults->avg('percentage')),
            ];
        })->values();
    }

    private function calculateSubjectTrend($results)
    {
        $trend = [];
        foreach ($results as $result) {
            $trend[] = [
                'exam' => $result->examSchedule->exam->name,
                'percentage' => $result->percentage,
            ];
        }
        
        return $trend;
    }

    private function getTeacherPerformanceData($teacher, $request)
    {
        $academicYearId = $request->academic_year_id ?? \App\Models\AcademicYear::getCurrent()->id;
        
        $classSubjects = \App\Models\ClassSubject::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->get();
        
        $performance = [
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'employee_id' => $teacher->employee->employee_id,
                'designation' => $teacher->employee->designation,
                'department' => $teacher->employee->department,
            ],
            'classes_handled' => $classSubjects->map(function($cs) {
                return [
                    'class' => $cs->class->name,
                    'subject' => $cs->subject->name,
                ];
            }),
            'students_count' => $this->getTeacherStudentsCount($teacher),
            'attendance_summary' => $this->getTeacherAttendanceSummary($teacher, $academicYearId),
            'exam_results_summary' => $this->getTeacherExamResultsSummary($teacher, $academicYearId),
            'homework_summary' => $this->getTeacherHomeworkSummary($teacher),
        ];
        
        return $performance;
    }

    private function getTeacherStudentsCount($teacher)
    {
        $classIds = \App\Models\ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        return \App\Models\Student::whereIn('class_id', $classIds)->count();
    }

    private function getTeacherAttendanceSummary($teacher, $academicYearId)
    {
        $classIds = \App\Models\ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id');
        
        $studentIds = \App\Models\Student::whereIn('class_id', $classIds)->pluck('id');
        
        $attendances = \App\Models\Attendance::whereIn('attendable_id', $studentIds)
            ->where('attendable_type', \App\Models\Student::class)
            ->whereYear('attendance_date', now()->year)
            ->get();
        
        return [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'percentage' => $attendances->count() > 0 ? 
                round(($attendances->where('status', 'present')->count() / $attendances->count()) * 100, 2) : 0,
        ];
    }

    private function getTeacherExamResultsSummary($teacher, $academicYearId)
    {
        $classSubjectIds = \App\Models\ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('id');
        
        $examSchedules = \App\Models\ExamSchedule::whereIn('class_subject_id', $classSubjectIds)
            ->pluck('id');
        
        $results = \App\Models\ExamResult::whereIn('exam_schedule_id', $examSchedules)
            ->get();
        
        return [
            'total_students' => $results->groupBy('student_id')->count(),
            'average_marks' => $results->avg('total_marks_obtained'),
            'average_percentage' => $results->avg('percentage'),
            'pass_count' => $results->filter(function($result) {
                return $result->percentage >= 40;
            })->count(),
            'fail_count' => $results->filter(function($result) {
                return $result->percentage < 40;
            })->count(),
        ];
    }

    private function getTeacherHomeworkSummary($teacher)
    {
        $homeworks = \App\Models\Homework::where('teacher_id', $teacher->id)->get();
        $submissions = \App\Models\HomeworkSubmission::whereIn('homework_id', $homeworks->pluck('id'))->get();
        
        return [
            'total_homework' => $homeworks->count(),
            'total_submissions' => $submissions->count(),
            'graded' => $submissions->where('status', 'graded')->count(),
            'pending_grading' => $submissions->where('status', 'submitted')->count(),
            'average_marks' => $submissions->avg('obtained_marks'),
        ];
    }

    private function getAttendanceOverviewData($request)
    {
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;
        
        $attendances = Attendance::whereYear('attendance_date', $year)
            ->where('attendable_type', Student::class);
        
        if ($month) {
            $attendances->whereMonth('attendance_date', $month);
        }
        
        $attendances = $attendances->get();
        
        $overview = [
            'year' => $year,
            'month' => $month ? Carbon::createFromDate($year, $month, 1)->format('F') : null,
            'total_students' => Student::count(),
            'total_attendance_records' => $attendances->count(),
            'overall_percentage' => $attendances->count() > 0 ? 
                round(($attendances->where('status', 'present')->count() / $attendances->count()) * 100, 2) : 0,
            'class_wise' => $this->getClassWiseAttendanceOverview($attendances),
            'monthly_trend' => $this->getMonthlyAttendanceTrend($year),
        ];
        
        return $overview;
    }

    private function getClassWiseAttendanceOverview($attendances)
    {
        return $attendances->groupBy(function($attendance) {
            return $attendance->attendable->class->id ?? 'unknown';
        })->map(function($classAttendances) {
            $class = $classAttendances->first()->attendable->class;
            return [
                'class' => $class->name,
                'total_students' => Student::where('class_id', $class->id)->count(),
                'total_records' => $classAttendances->count(),
                'present' => $classAttendances->where('status', 'present')->count(),
                'absent' => $classAttendances->where('status', 'absent')->count(),
                'percentage' => $classAttendances->count() > 0 ? 
                    round(($classAttendances->where('status', 'present')->count() / $classAttendances->count()) * 100, 2) : 0,
            ];
        })->values();
    }

    private function getMonthlyAttendanceTrend($year)
    {
        $trend = [];
        for ($i = 1; $i <= 12; $i++) {
            $attendances = Attendance::whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $i)
                ->where('attendable_type', Student::class)
                ->get();
            
            $trend[] = [
                'month' => Carbon::createFromDate($year, $i, 1)->format('M'),
                'percentage' => $attendances->count() > 0 ? 
                    round(($attendances->where('status', 'present')->count() / $attendances->count()) * 100, 2) : 0,
            ];
        }
        
        return $trend;
    }

    private function financialRevenueReport($request)
    {
        $payments = Payment::with(['student.class'])
            ->whereBetween('payment_date', [$request->from_date, $request->to_date])
            ->get();
        
        $report = [
            'period' => [
                'from' => $request->from_date,
                'to' => $request->to_date,
            ],
            'total_revenue' => $payments->sum('amount'),
            'total_revenue_formatted' => '₹ ' . number_format($payments->sum('amount'), 2),
            'by_payment_method' => $this->getRevenueByPaymentMethod($payments),
            'by_class' => $this->getRevenueByClass($payments),
            'monthly_breakdown' => $this->getRevenueMonthlyBreakdown($payments, $request),
        ];
        
        return $this->sendResponse($report, 'Financial revenue report generated');
    }

    private function getRevenueByPaymentMethod($payments)
    {
        return $payments->groupBy('payment_method')->map(function($methodPayments) {
            return [
                'amount' => $methodPayments->sum('amount'),
                'amount_formatted' => '₹ ' . number_format($methodPayments->sum('amount'), 2),
                'count' => $methodPayments->count(),
            ];
        });
    }

    private function getRevenueByClass($payments)
    {
        return $payments->groupBy(function($payment) {
            return $payment->student->class->name ?? 'Unknown';
        })->map(function($classPayments) {
            return [
                'amount' => $classPayments->sum('amount'),
                'amount_formatted' => '₹ ' . number_format($classPayments->sum('amount'), 2),
                'count' => $classPayments->count(),
            ];
        });
    }

    private function getRevenueMonthlyBreakdown($payments, $request)
    {
        $breakdown = [];
        $startDate = Carbon::parse($request->from_date);
        $endDate = Carbon::parse($request->to_date);
        
        while ($startDate <= $endDate) {
            $month = $startDate->format('Y-m');
            $monthPayments = $payments->filter(function($payment) use ($month) {
                return Carbon::parse($payment->payment_date)->format('Y-m') == $month;
            });
            
            $breakdown[] = [
                'month' => $startDate->format('F Y'),
                'amount' => $monthPayments->sum('amount'),
                'amount_formatted' => '₹ ' . number_format($monthPayments->sum('amount'), 2),
                'count' => $monthPayments->count(),
            ];
            
            $startDate->addMonth();
        }
        
        return $breakdown;
    }

    private function financialExpensesReport($request)
    {
        // Implement expenses logic based on your expenses table
        return $this->sendResponse([], 'Expenses report - Implementation pending');
    }

    private function financialProfitLossReport($request)
    {
        // Implement profit/loss logic
        return $this->sendResponse([], 'Profit/Loss report - Implementation pending');
    }
}