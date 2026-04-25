<?php
// app/Http/Controllers/Student/ResultController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ResultController extends Controller
{
    /**
     * Display all exam results for the student
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject', 'examSchedule.exam.examType']);
        
        // Filter by exam type
        if ($request->filled('exam_type_id')) {
            $query->whereHas('examSchedule.exam', function($q) use ($request) {
                $q->where('exam_type_id', $request->exam_type_id);
            });
        }
        
        // Filter by year
        if ($request->filled('year')) {
            $query->whereHas('examSchedule.exam', function($q) use ($request) {
                $q->whereYear('start_date', $request->year);
            });
        }
        
        $results = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());
        
        // Group results by exam
        $groupedResults = $results->groupBy('examSchedule.exam_id');
        
        // Calculate overall performance
        $allResults = ExamResult::where('student_id', $student->id)->get();
        $overallStats = [
            'total_exams' => $allResults->groupBy('examSchedule.exam_id')->count(),
            'total_subjects' => $allResults->count(),
            'average_percentage' => $allResults->avg('percentage'),
            'best_performance' => $allResults->sortByDesc('percentage')->first(),
            'worst_performance' => $allResults->sortBy('percentage')->first(),
            'total_passed' => $allResults->filter(function($result) {
                return $result->percentage >= 40;
            })->count(),
            'total_failed' => $allResults->filter(function($result) {
                return $result->percentage < 40;
            })->count(),
        ];
        
        // Get available years for filter
        $years = ExamResult::where('student_id', $student->id)
            ->whereHas('examSchedule.exam', function($q) {
                $q->whereNotNull('start_date');
            })
            ->with('examSchedule.exam')
            ->get()
            ->pluck('examSchedule.exam.start_date')
            ->filter()
            ->map(function($date) {
                return $date->year;
            })
            ->unique()
            ->sort()
            ->values();
        
        $examTypes = \App\Models\ExamType::all();
        
        return view('student.results.index', compact('results', 'groupedResults', 'overallStats', 'years', 'examTypes'));
    }
    
    /**
     * Display detailed results for a specific exam
     */
    public function show(Exam $exam)
    {
        $student = Auth::user()->student;
        
        // Get all results for this exam
        $results = ExamResult::where('student_id', $student->id)
            ->whereHas('examSchedule', function($q) use ($exam) {
                $q->where('exam_id', $exam->id);
            })
            ->with(['examSchedule.subject'])
            ->get();
        
        if ($results->isEmpty()) {
            return redirect()->route('student.results')
                ->with('error', 'No results found for this exam.');
        }
        
        // Calculate totals
        $totalObtained = $results->sum('total_marks_obtained');
        $totalMaxMarks = $results->sum(function($result) {
            return $result->examSchedule->total_marks + ($result->examSchedule->practical_marks ?? 0);
        });
        $overallPercentage = $totalMaxMarks > 0 ? round(($totalObtained / $totalMaxMarks) * 100, 2) : 0;
        
        // Calculate subject-wise performance
        $subjectPerformance = [];
        foreach ($results as $result) {
            $subjectPerformance[] = [
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
        }
        
        // Get rank if available
        $rank = $this->getStudentRank($exam, $student);
        
        // Get class average
        $classAverage = $this->getClassAverage($exam, $student);
        
        return view('student.results.show', compact('exam', 'results', 'subjectPerformance', 'totalObtained', 'totalMaxMarks', 'overallPercentage', 'rank', 'classAverage'));
    }
    
    /**
     * Display performance summary with charts
     */
    public function summary(Request $request)
    {
        $student = Auth::user()->student;
        
        $year = $request->year ?? Carbon::now()->year;
        
        // Get all results for the student
        $allResults = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get();
        
        // Subject-wise performance
        $subjectPerformance = [];
        foreach ($allResults->groupBy('examSchedule.subject_id') as $subjectId => $subjectResults) {
            $subject = $subjectResults->first()->examSchedule->subject;
            $subjectPerformance[] = [
                'subject' => $subject->name,
                'code' => $subject->code,
                'average' => round($subjectResults->avg('percentage'), 2),
                'best' => $subjectResults->max('percentage'),
                'worst' => $subjectResults->min('percentage'),
                'exams_count' => $subjectResults->count(),
            ];
        }
        
        // Exam-wise performance trend
        $examTrend = [];
        foreach ($allResults->groupBy('examSchedule.exam_id') as $examId => $examResults) {
            $exam = $examResults->first()->examSchedule->exam;
            $examTrend[] = [
                'exam_id' => $examId,
                'exam_name' => $exam->name,
                'exam_date' => $exam->start_date->format('M Y'),
                'average' => round($examResults->avg('percentage'), 2),
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
            
            if ($monthResults->count() > 0) {
                $monthlyPerformance[] = [
                    'month' => Carbon::createFromDate($year, $month, 1)->format('F'),
                    'average' => round($monthResults->avg('percentage'), 2),
                    'exams_count' => $monthResults->groupBy('examSchedule.exam_id')->count(),
                ];
            }
        }
        
        // Overall statistics
        $stats = [
            'total_exams' => $allResults->groupBy('examSchedule.exam_id')->count(),
            'total_subjects' => $allResults->count(),
            'overall_percentage' => round($allResults->avg('percentage'), 2),
            'best_subject' => collect($subjectPerformance)->sortByDesc('average')->first(),
            'worst_subject' => collect($subjectPerformance)->sortBy('average')->first(),
            'pass_percentage' => $allResults->count() > 0 
                ? round(($allResults->filter(function($r) { return $r->percentage >= 40; })->count() / $allResults->count()) * 100, 2) 
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
        
        // Get available years
        $years = $allResults->pluck('examSchedule.exam.start_date')
            ->filter()
            ->map(fn($date) => $date->year)
            ->unique()
            ->sort()
            ->values();
        
        return view('student.results.summary', compact('subjectPerformance', 'examTrend', 'monthlyPerformance', 'stats', 'year', 'years'));
    }
    
    /**
     * Get student rank in exam
     */
    private function getStudentRank($exam, $student)
    {
        // Get all results for this exam
        $allResults = ExamResult::whereHas('examSchedule', function($q) use ($exam) {
            $q->where('exam_id', $exam->id);
        })->get();
        
        // Calculate total percentage per student
        $studentPercentages = [];
        foreach ($allResults->groupBy('student_id') as $studentId => $results) {
            $totalObtained = $results->sum('total_marks_obtained');
            $totalMax = $results->sum(function($r) {
                return $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0);
            });
            $studentPercentages[$studentId] = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
        }
        
        // Sort by percentage descending
        arsort($studentPercentages);
        
        // Find rank
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
}