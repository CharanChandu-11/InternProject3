<?php
// app/Http/Controllers/Api/Student/ResultController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\Exam;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ResultController extends BaseController
{
    /**
     * Get all exam results for the student (paginated)
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;

        $query = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject', 'examSchedule.exam.examType']);

        if ($request->filled('exam_type_id')) {
            $query->whereHas('examSchedule.exam', fn($q) => $q->where('exam_type_id', $request->exam_type_id));
        }
        if ($request->filled('year')) {
            $query->whereHas('examSchedule.exam', fn($q) => $q->whereYear('start_date', $request->year));
        }

        $results = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        // Group by exam for frontend convenience
        $grouped = $results->getCollection()->groupBy('examSchedule.exam_id')->map(function ($items, $examId) {
            $exam = $items->first()->examSchedule->exam;
            $totalObtained = $items->sum('total_marks_obtained');
            $totalMax = $items->sum(fn($r) => $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0));
            return [
                'exam_id' => $examId,
                'exam_name' => $exam->name,
                'exam_date' => $exam->start_date->toDateString(),
                'total_marks' => $totalObtained,
                'max_marks' => $totalMax,
                'percentage' => $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0,
                'subjects' => $items->map(fn($r) => [
                    'subject' => $r->examSchedule->subject->name,
                    'theory' => $r->theory_marks_obtained,
                    'practical' => $r->practical_marks_obtained,
                    'total' => $r->total_marks_obtained,
                    'max' => $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0),
                    'percentage' => $r->percentage,
                    'grade' => $r->grade,
                ]),
            ];
        })->values();

        // Overall stats using all results (not just current page)
        $allResults = ExamResult::where('student_id', $student->id)->get();
        $overallStats = [
            'total_exams' => $allResults->groupBy('examSchedule.exam_id')->count(),
            'total_subjects' => $allResults->count(),
            'average_percentage' => round($allResults->avg('percentage'), 2),
            'total_passed' => $allResults->filter(fn($r) => $r->percentage >= 40)->count(),
            'total_failed' => $allResults->filter(fn($r) => $r->percentage < 40)->count(),
        ];

        // Available years for filter
        $years = ExamResult::where('student_id', $student->id)
            ->whereHas('examSchedule.exam', fn($q) => $q->whereNotNull('start_date'))
            ->get()
            ->pluck('examSchedule.exam.start_date')
            ->filter()
            ->map(fn($date) => $date->year)
            ->unique()
            ->sort()
            ->values();

        $examTypes = \App\Models\ExamType::all();

        return $this->sendResponse([
            'results' => $grouped,
            'overall_stats' => $overallStats,
            'available_years' => $years,
            'exam_types' => $examTypes,
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ], 'Exam results retrieved');
    }

    /**
     * Get detailed results for a specific exam
     */
    public function show(Exam $exam)
    {
        $student = Auth::user()->student;

        $results = ExamResult::where('student_id', $student->id)
            ->whereHas('examSchedule', fn($q) => $q->where('exam_id', $exam->id))
            ->with(['examSchedule.subject'])
            ->get();

        if ($results->isEmpty()) {
            return $this->sendError('No results found for this exam', [], 404);
        }

        $totalObtained = $results->sum('total_marks_obtained');
        $totalMax = $results->sum(fn($r) => $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0));
        $overallPercentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;

        $subjectPerformance = $results->map(fn($r) => [
            'subject' => $r->examSchedule->subject->name,
            'code' => $r->examSchedule->subject->code,
            'theory' => $r->theory_marks_obtained,
            'practical' => $r->practical_marks_obtained,
            'total' => $r->total_marks_obtained,
            'max' => $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0),
            'percentage' => $r->percentage,
            'grade' => $r->grade,
            'remarks' => $r->remarks,
        ]);

        $rank = $this->getStudentRank($exam, $student);
        $classAverage = $this->getClassAverage($exam);

        return $this->sendResponse([
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'type' => $exam->examType->name ?? null,
                'date' => $exam->start_date->toDateString(),
            ],
            'summary' => [
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $overallPercentage,
                'rank' => $rank,
                'class_average' => $classAverage,
            ],
            'subjects' => $subjectPerformance,
        ], 'Exam result details');
    }

    /**
     * Performance summary with charts and analytics
     */
    public function summary(Request $request)
    {
        $student = Auth::user()->student;
        $year = $request->year ?? Carbon::now()->year;

        $allResults = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get();

        // Subject-wise performance
        $subjectPerformance = [];
        foreach ($allResults->groupBy('examSchedule.subject_id') as $subjectId => $items) {
            $subject = $items->first()->examSchedule->subject;
            $subjectPerformance[] = [
                'subject' => $subject->name,
                'code' => $subject->code,
                'average' => round($items->avg('percentage'), 2),
                'best' => round($items->max('percentage'), 2),
                'worst' => round($items->min('percentage'), 2),
                'exams_count' => $items->count(),
            ];
        }

        // Exam trend (chronological)
        $examTrend = $allResults->groupBy('examSchedule.exam_id')
            ->map(function ($items) {
                $exam = $items->first()->examSchedule->exam;
                return [
                    'exam_name' => $exam->name,
                    'exam_date' => $exam->start_date->format('M Y'),
                    'average' => round($items->avg('percentage'), 2),
                ];
            })
            ->sortBy('exam_date')
            ->values();

        // Monthly performance for selected year
        $monthlyPerformance = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthResults = $allResults->filter(function ($r) use ($month, $year) {
                $date = $r->examSchedule->exam->start_date;
                return $date && $date->month == $month && $date->year == $year;
            });
            if ($monthResults->isNotEmpty()) {
                $monthlyPerformance[] = [
                    'month' => Carbon::createFromDate($year, $month, 1)->format('F'),
                    'average' => round($monthResults->avg('percentage'), 2),
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
            'best_subject' => collect($subjectPerformance)->sortByDesc('average')->first(),
            'worst_subject' => collect($subjectPerformance)->sortBy('average')->first(),
            'pass_percentage' => $totalSubjects > 0
                ? round(($allResults->filter(fn($r) => $r->percentage >= 40)->count() / $totalSubjects) * 100, 2)
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

        // Available years
        $years = $allResults->pluck('examSchedule.exam.start_date')
            ->filter()
            ->map(fn($date) => $date->year)
            ->unique()
            ->sort()
            ->values();

        return $this->sendResponse([
            'overall_stats' => $stats,
            'subject_wise' => $subjectPerformance,
            'exam_trend' => $examTrend,
            'monthly_performance' => $monthlyPerformance,
            'available_years' => $years,
        ], 'Performance summary');
    }

    // ---------- Helper methods ----------
    private function getStudentRank($exam, $student)
    {
        $allResults = ExamResult::whereHas('examSchedule', fn($q) => $q->where('exam_id', $exam->id))->get();
        $studentPercentages = [];

        foreach ($allResults->groupBy('student_id') as $sid => $results) {
            $totalObtained = $results->sum('total_marks_obtained');
            $totalMax = $results->sum(fn($r) => $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0));
            $studentPercentages[$sid] = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
        }

        arsort($studentPercentages);
        $rank = 1;
        foreach ($studentPercentages as $sid => $pct) {
            if ($sid == $student->id) return $rank;
            $rank++;
        }
        return null;
    }

    private function getClassAverage($exam)
    {
        $allResults = ExamResult::whereHas('examSchedule', fn($q) => $q->where('exam_id', $exam->id))->get();
        $total = $allResults->sum('percentage');
        $count = $allResults->count();
        return $count > 0 ? round($total / $count, 2) : 0;
    }
}