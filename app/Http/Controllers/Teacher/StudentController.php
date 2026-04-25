<?php
// app/Http/Controllers/Teacher/StudentController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\HomeworkSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Display a listing of students taught by the teacher
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        // Get all class-section combinations taught by this teacher
        $classSections = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section'])
            ->get()
            ->unique(function($item) {
                return $item->class_id . '_' . $item->section_id;
            })
            ->values();
        
        // Build query for students
        $query = Student::with(['user', 'class', 'section']);
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter by section
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        } else {
            // If no section selected, show students from first section of selected class
            if ($request->filled('class_id')) {
                $firstSection = Timetable::where('teacher_id', $teacher->id)
                    ->where('class_id', $request->class_id)
                    ->first();
                if ($firstSection) {
                    $query->where('section_id', $firstSection->section_id);
                }
            }
        }
        
        // Search by name or admission number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('admission_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        
        $students = $query->orderBy('roll_number')
            ->paginate(20)
            ->appends($request->query());
        
        // Get unique classes for filter
        $classes = $classSections->pluck('class')->unique('id')->values();
        
        // Get sections for selected class
        $sections = collect();
        if ($request->filled('class_id')) {
            $sections = Timetable::where('teacher_id', $teacher->id)
                ->where('class_id', $request->class_id)
                ->with('section')
                ->get()
                ->pluck('section')
                ->unique('id')
                ->values();
        }
        
        // Statistics
        $totalStudents = Student::whereIn('class_id', $classes->pluck('id'))->count();
        $totalClasses = $classSections->count();
        
        $stats = [
            'total_students' => $totalStudents,
            'total_classes' => $totalClasses,
            'average_per_class' => $totalClasses > 0 ? round($totalStudents / $totalClasses, 2) : 0,
        ];
        
        return view('teacher.students.index', compact('students', 'classes', 'sections', 'stats'));
    }
    
    /**
     * Display detailed information of a specific student
     */
    public function show(Student $student)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this student's class
        $teachesClass = Timetable::where('teacher_id', $teacher->id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->exists();
        
        if (!$teachesClass) {
            abort(403, 'Unauthorized to view this student.');
        }
        
        $student->load(['user', 'class', 'section', 'parents.user']);
        
        // Attendance Summary
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $monthlyAttendance = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereMonth('attendance_date', $currentMonth)
            ->whereYear('attendance_date', $currentYear)
            ->get();
        
        $attendanceSummary = [
            'today' => Attendance::where('attendable_type', Student::class)
                ->where('attendable_id', $student->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first(),
            'monthly' => [
                'total_days' => $monthlyAttendance->count(),
                'present' => $monthlyAttendance->where('status', 'present')->count(),
                'absent' => $monthlyAttendance->where('status', 'absent')->count(),
                'late' => $monthlyAttendance->where('status', 'late')->count(),
                'percentage' => $monthlyAttendance->count() > 0 
                    ? round(($monthlyAttendance->where('status', 'present')->count() / $monthlyAttendance->count()) * 100, 2) 
                    : 0,
            ],
            'overall_percentage' => $student->attendance_percentage,
        ];
        
        // Exam Results
        $examResults = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('examSchedule.exam_id');
        
        $resultsData = [];
        foreach ($examResults as $examId => $results) {
            $exam = $results->first()->examSchedule->exam;
            $totalMarks = $results->sum('total_marks_obtained');
            $maxMarks = $results->sum('examSchedule.total_marks') + $results->sum('examSchedule.practical_marks');
            $percentage = $maxMarks > 0 ? round(($totalMarks / $maxMarks) * 100, 2) : 0;
            
            $resultsData[] = [
                'exam_id' => $examId,
                'exam_name' => $exam->name,
                'exam_date' => $exam->start_date->format('d-m-Y'),
                'total_marks' => $totalMarks,
                'max_marks' => $maxMarks,
                'percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
                'subjects' => $results->map(function($result) {
                    return [
                        'subject' => $result->examSchedule->subject->name,
                        'marks' => $result->total_marks_obtained,
                        'max_marks' => $result->examSchedule->total_marks + ($result->examSchedule->practical_marks ?? 0),
                        'percentage' => $result->percentage,
                        'grade' => $result->grade,
                    ];
                }),
            ];
        }
        
        // Homework Summary
        $homeworkSubmissions = HomeworkSubmission::where('student_id', $student->id)
            ->with('homework')
            ->latest()
            ->take(10)
            ->get();
        
        $homeworkStats = [
            'total_submitted' => HomeworkSubmission::where('student_id', $student->id)->count(),
            'graded' => HomeworkSubmission::where('student_id', $student->id)->where('status', 'graded')->count(),
            'average_marks' => HomeworkSubmission::where('student_id', $student->id)
                ->where('status', 'graded')
                ->avg('obtained_marks'),
        ];
        
        return view('teacher.students.show', compact('student', 'attendanceSummary', 'resultsData', 'homeworkSubmissions', 'homeworkStats'));
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
}