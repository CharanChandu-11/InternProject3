<?php
// app/Http/Controllers/Student/ExamController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExamController extends Controller
{
    /**
     * Display upcoming exams for the student
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = ExamSchedule::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with(['exam', 'subject']);
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'upcoming') {
                $query->whereHas('exam', function($q) {
                    $q->where('start_date', '>=', Carbon::today());
                });
            } elseif ($request->status == 'ongoing') {
                $query->whereHas('exam', function($q) {
                    $q->where('start_date', '<=', Carbon::today())
                      ->where('end_date', '>=', Carbon::today());
                });
            } elseif ($request->status == 'completed') {
                $query->whereHas('exam', function($q) {
                    $q->where('end_date', '<', Carbon::today());
                });
            }
        } else {
            $query->whereHas('exam', function($q) {
                $q->where('start_date', '>=', Carbon::today());
            });
        }
        
        // Filter by exam type
        if ($request->filled('exam_type_id')) {
            $query->whereHas('exam', function($q) use ($request) {
                $q->where('exam_type_id', $request->exam_type_id);
            });
        }
        
        $exams = $query->orderBy('exam_date')
            ->paginate(10)
            ->appends($request->query());
        
        // Get statistics
        $stats = [
            'upcoming' => ExamSchedule::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->whereHas('exam', function($q) {
                    $q->where('start_date', '>', Carbon::today());
                })
                ->count(),
            'ongoing' => ExamSchedule::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->whereHas('exam', function($q) {
                    $q->where('start_date', '<=', Carbon::today())
                      ->where('end_date', '>=', Carbon::today());
                })
                ->count(),
            'completed' => ExamSchedule::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->whereHas('exam', function($q) {
                    $q->where('end_date', '<', Carbon::today());
                })
                ->count(),
        ];
        
        // Get exam types for filter
        $examTypes = \App\Models\ExamType::all();
        
        return view('student.exams.index', compact('exams', 'stats', 'examTypes'));
    }
}