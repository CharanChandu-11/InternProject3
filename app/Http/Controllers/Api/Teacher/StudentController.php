<?php
// app/Http/Controllers/Api/Teacher/StudentController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Attendance;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentController extends BaseController
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        $query = Student::whereIn('class_id', $classIds)
            ->with(['user', 'class', 'section']);
        
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }
        
        $students = $query->orderBy('roll_number')->paginate($request->per_page ?? 50);
        
        return $this->sendPaginatedResponse($students, 'Students retrieved');
    }
    
    public function show(Student $student)
    {
        $teacher = Auth::user();
        
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $student->class_id)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $student->load(['user', 'class', 'section', 'parents.user']);
        
        $attendanceSummary = [
            'overall_percentage' => $student->attendance_percentage,
            'monthly_percentage' => $this->getMonthlyAttendance($student),
            'today' => $student->attendances()->whereDate('attendance_date', Carbon::today())->first(),
        ];
        
        $results = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return $this->sendResponse([
            'student' => $student,
            'attendance_summary' => $attendanceSummary,
            'recent_results' => $results,
        ], 'Student details retrieved');
    }
    
    public function attendance(Student $student)
    {
        $teacher = Auth::user();
        
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $student->class_id)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $attendances = $student->attendances()
            ->orderBy('attendance_date', 'desc')
            ->paginate(30);
        
        return $this->sendPaginatedResponse($attendances, 'Attendance records retrieved');
    }
    
    public function results(Student $student)
    {
        $teacher = Auth::user();
        
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $student->class_id)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $results = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $this->sendResponse($results, 'Exam results retrieved');
    }
    
    public function performance(Student $student)
    {
        $teacher = Auth::user();
        
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $student->class_id)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $results = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get();
        
        $subjectPerformance = [];
        foreach ($results->groupBy('examSchedule.subject_id') as $subjectId => $subjectResults) {
            $subject = $subjectResults->first()->examSchedule->subject;
            $subjectPerformance[] = [
                'subject' => $subject->name,
                'average_percentage' => round($subjectResults->avg('percentage'), 2),
                'best' => round($subjectResults->max('percentage'), 2),
                'worst' => round($subjectResults->min('percentage'), 2),
                'exams_count' => $subjectResults->count(),
            ];
        }
        
        $examTrend = [];
        foreach ($results->groupBy('examSchedule.exam_id') as $examId => $examResults) {
            $exam = $examResults->first()->examSchedule->exam;
            $examTrend[] = [
                'exam' => $exam->name,
                'date' => $exam->start_date->format('M Y'),
                'percentage' => round($examResults->avg('percentage'), 2),
            ];
        }
        
        return $this->sendResponse([
            'overall_percentage' => round($results->avg('percentage'), 2),
            'subject_wise' => $subjectPerformance,
            'exam_trend' => $examTrend,
        ], 'Performance data retrieved');
    }
    
    private function getMonthlyAttendance($student)
    {
        $attendances = $student->attendances()
            ->whereMonth('attendance_date', Carbon::now()->month)
            ->get();
        
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        
        return $total > 0 ? round(($present / $total) * 100, 2) : 0;
    }
}