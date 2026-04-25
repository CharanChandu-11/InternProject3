<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Payment;
use App\Models\StudentFee;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Exam;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Attendance Report
     */
    public function attendance(Request $request)
    {
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $fromDate = $request->from_date ?? Carbon::now()->startOfMonth();
        $toDate = $request->to_date ?? Carbon::now()->endOfMonth();

        // Get classes for filter
        $classes = Classes::with('sections')->orderBy('name')->get();

        // Build query for attendance
        $query = Attendance::with(['attendable.user', 'attendable.class', 'attendable.section'])
            ->where('attendable_type', Student::class)
            ->whereBetween('attendance_date', [$fromDate, $toDate]);

        if ($request->filled('class_id')) {
            $query->whereHas('attendable', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('section_id')) {
            $query->whereHas('attendable', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate(50)
            ->appends($request->query());

        // Summary statistics
        $summary = [
            'total_records' => $query->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
            'half_day' => (clone $query)->where('status', 'half_day')->count(),
        ];

        if ($summary['total_records'] > 0) {
            $summary['present_percent'] = round(($summary['present'] / $summary['total_records']) * 100, 2);
            $summary['absent_percent'] = round(($summary['absent'] / $summary['total_records']) * 100, 2);
        } else {
            $summary['present_percent'] = 0;
            $summary['absent_percent'] = 0;
        }

        // Class-wise breakdown
        $classWise = [];
        $allClasses = Classes::all();
        foreach ($allClasses as $class) {
            $classAttendances = (clone $query)->whereHas('attendable', function($q) use ($class) {
                $q->where('class_id', $class->id);
            })->get();

            $total = $classAttendances->count();
            $present = $classAttendances->where('status', 'present')->count();
            $classWise[] = [
                'class' => $class->name,
                'total' => $total,
                'present' => $present,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ];
        }

        // Daily breakdown chart data
        $dailyData = [];
        $period = Carbon::parse($fromDate);
        while ($period <= $toDate) {
            $dayAttendances = (clone $query)->whereDate('attendance_date', $period)->get();
            $dailyData[] = [
                'date' => $period->format('Y-m-d'),
                'day' => $period->format('D'),
                'present' => $dayAttendances->where('status', 'present')->count(),
                'absent' => $dayAttendances->where('status', 'absent')->count(),
                'total' => $dayAttendances->count(),
                'percentage' => $dayAttendances->count() > 0 
                    ? round(($dayAttendances->where('status', 'present')->count() / $dayAttendances->count()) * 100, 2) 
                    : 0,
            ];
            $period->addDay();
        }

        return view('admin.reports.attendance', compact(
            'attendances', 'classes', 'summary', 'classWise', 'dailyData', 'fromDate', 'toDate'
        ));
    }

    /**
     * Fees Report
     */
    public function fees(Request $request)
    {
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'status' => 'nullable|in:paid,pending,partial,overdue,all',
        ]);

        $fromDate = $request->from_date ?? Carbon::now()->startOfMonth();
        $toDate = $request->to_date ?? Carbon::now()->endOfMonth();

        // Get classes for filter
        $classes = Classes::with('sections')->orderBy('name')->get();

        // Payment query
        $paymentQuery = Payment::with(['student.user', 'student.class', 'student.section', 'studentFee.feeStructure.feeCategory'])
            ->whereBetween('payment_date', [$fromDate, $toDate]);

        if ($request->filled('class_id')) {
            $paymentQuery->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('section_id')) {
            $paymentQuery->whereHas('student', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        $payments = $paymentQuery->orderBy('payment_date', 'desc')
            ->paginate(50)
            ->appends($request->query());

        // Summary statistics
        $summary = [
            'total_collected' => $paymentQuery->sum('amount'),
            'total_collected_formatted' => '₹ ' . number_format($paymentQuery->sum('amount'), 2),
            'total_transactions' => $paymentQuery->count(),
            'by_method' => (clone $paymentQuery)->select('payment_method', DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get(),
        ];

        // Collection trend (daily)
        $collectionTrend = [];
        $period = Carbon::parse($fromDate);
        while ($period <= $toDate) {
            $dayPayments = (clone $paymentQuery)->whereDate('payment_date', $period)->get();
            $collectionTrend[] = [
                'date' => $period->format('Y-m-d'),
                'day' => $period->format('D'),
                'amount' => $dayPayments->sum('amount'),
                'amount_formatted' => '₹ ' . number_format($dayPayments->sum('amount'), 2),
                'count' => $dayPayments->count(),
            ];
            $period->addDay();
        }

        // Outstanding fees
        $outstandingQuery = StudentFee::with(['student.user', 'student.class', 'student.section', 'feeStructure.feeCategory'])
            ->where('due_amount', '>', 0);

        if ($request->filled('class_id')) {
            $outstandingQuery->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('section_id')) {
            $outstandingQuery->whereHas('student', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        $outstandingFees = $outstandingQuery->orderBy('due_date')
            ->paginate(50, ['*'], 'outstanding_page')
            ->appends($request->query());

        $outstandingSummary = [
            'total_outstanding' => $outstandingQuery->sum('due_amount'),
            'total_outstanding_formatted' => '₹ ' . number_format($outstandingQuery->sum('due_amount'), 2),
            'total_students' => $outstandingQuery->distinct('student_id')->count('student_id'),
        ];

        return view('admin.reports.fees', compact(
            'payments', 'classes', 'summary', 'collectionTrend', 
            'outstandingFees', 'outstandingSummary', 'fromDate', 'toDate'
        ));
    }

    /**
     * Exam Results Report
     */
    public function examResults(Request $request)
    {
        $request->validate([
            'exam_id' => 'nullable|exists:exams,id',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        // Get exams for filter
        $exams = Exam::with('examType')->orderBy('start_date', 'desc')->get();
        $classes = Classes::orderBy('name')->get();
        $subjects = \App\Models\Subject::orderBy('name')->get();

        // Build query
        $query = ExamResult::with(['student.user', 'student.class', 'student.section', 'examSchedule.exam', 'examSchedule.subject']);

        if ($request->filled('exam_id')) {
            $query->whereHas('examSchedule', function($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('section_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('examSchedule', function($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        $results = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->appends($request->query());

        // Summary statistics
        $allResults = (clone $query)->get();
        $summary = [
            'total_results' => $allResults->count(),
            'total_students' => $allResults->groupBy('student_id')->count(),
            'average_percentage' => round($allResults->avg('percentage'), 2),
            'pass_count' => $allResults->filter(function($r) { return $r->percentage >= 40; })->count(),
            'fail_count' => $allResults->filter(function($r) { return $r->percentage < 40; })->count(),
            'pass_percentage' => $allResults->count() > 0 
                ? round(($allResults->filter(function($r) { return $r->percentage >= 40; })->count() / $allResults->count()) * 100, 2) 
                : 0,
        ];

        // Grade distribution
        $gradeDistribution = [
            'A+' => $allResults->filter(fn($r) => $r->percentage >= 90)->count(),
            'A' => $allResults->filter(fn($r) => $r->percentage >= 80 && $r->percentage < 90)->count(),
            'B+' => $allResults->filter(fn($r) => $r->percentage >= 70 && $r->percentage < 80)->count(),
            'B' => $allResults->filter(fn($r) => $r->percentage >= 60 && $r->percentage < 70)->count(),
            'C' => $allResults->filter(fn($r) => $r->percentage >= 50 && $r->percentage < 60)->count(),
            'D' => $allResults->filter(fn($r) => $r->percentage >= 40 && $r->percentage < 50)->count(),
            'F' => $allResults->filter(fn($r) => $r->percentage < 40)->count(),
        ];

        // Subject-wise performance
        $subjectPerformance = [];
        if ($request->filled('exam_id')) {
            $subjectResults = $allResults->groupBy('examSchedule.subject_id');
            foreach ($subjectResults as $subjectId => $subjectItems) {
                $subject = $subjectItems->first()->examSchedule->subject;
                $subjectPerformance[] = [
                    'subject' => $subject->name,
                    'subject_code' => $subject->code,
                    'total_students' => $subjectItems->count(),
                    'average_marks' => round($subjectItems->avg('total_marks_obtained'), 2),
                    'average_percentage' => round($subjectItems->avg('percentage'), 2),
                    'highest' => $subjectItems->max('percentage'),
                    'lowest' => $subjectItems->min('percentage'),
                    'pass_count' => $subjectItems->filter(fn($r) => $r->percentage >= 40)->count(),
                    'fail_count' => $subjectItems->filter(fn($r) => $r->percentage < 40)->count(),
                ];
            }
        }

        // Top performers
        $topPerformers = [];
        if ($request->filled('exam_id')) {
            $studentResults = $allResults->groupBy('student_id');
            foreach ($studentResults as $studentId => $studentItems) {
                $student = $studentItems->first()->student;
                $topPerformers[] = [
                    'student_id' => $studentId,
                    'student_name' => $student->user->name,
                    'admission_number' => $student->admission_number,
                    'class' => $student->class->name,
                    'section' => $student->section->name,
                    'total_marks' => $studentItems->sum('total_marks_obtained'),
                    'max_marks' => $studentItems->sum(fn($r) => $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0)),
                    'percentage' => round($studentItems->avg('percentage'), 2),
                    'grade' => $this->calculateGrade($studentItems->avg('percentage')),
                ];
            }
            $topPerformers = collect($topPerformers)->sortByDesc('percentage')->take(10)->values();
        }

        return view('admin.reports.exam-results', compact(
            'results', 'exams', 'classes', 'subjects', 'summary', 
            'gradeDistribution', 'subjectPerformance', 'topPerformers'
        ));
    }

    /**
     * Export Attendance Report to Excel
     */
    public function exportAttendance(Request $request)
    {
        // Implement Excel export
        return redirect()->back()->with('success', 'Export feature coming soon.');
    }

    /**
     * Export Fees Report to Excel
     */
    public function exportFees(Request $request)
    {
        // Implement Excel export
        return redirect()->back()->with('success', 'Export feature coming soon.');
    }

    /**
     * Export Exam Results to Excel
     */
    public function exportExamResults(Request $request)
    {
        // Implement Excel export
        return redirect()->back()->with('success', 'Export feature coming soon.');
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
            default => 'F'
        };
    }
}