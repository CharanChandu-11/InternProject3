<?php
// app/Http/Controllers/Teacher/ExamController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Timetable;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExamMarksExport;
use Carbon\Carbon;

class ExamController extends Controller
{
    /**
     * Display a listing of exams for the teacher
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        $query = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['exam', 'class', 'section', 'subject', 'classSubject']);
        
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
        }
        
        // Filter by exam
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        $examSchedules = $query->orderBy('exam_date')
            ->paginate(20)
            ->appends($request->query());
        
        // Get unique exams for filter
        $exams = Exam::whereHas('schedules', function($q) use ($teacher) {
            $q->whereHas('classSubject', function($cq) use ($teacher) {
                $cq->where('teacher_id', $teacher->id);
            });
        })->get();
        
        // Get classes for filter
        $classes = Timetable::where('teacher_id', $teacher->id)
            ->with('class')
            ->get()
            ->pluck('class')
            ->unique('id')
            ->values();
        
        return view('teacher.exams.index', compact('examSchedules', 'exams', 'classes'));
    }
    
    /**
     * Show form for creating new exam schedule
     */
    public function create()
    {
        $teacher = Auth::user();
        
        // Get classes taught by teacher
        $classSections = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section'])
            ->get()
            ->unique(function($item) {
                return $item->class_id;
            })
            ->map(function($item) {
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name,
                    'section_id' => $item->section_id,
                    'section_name' => $item->section->name,
                ];
            })
            ->values();
        
        // Get exam types
        $examTypes = ExamType::all();
        
        return view('teacher.exams.create', compact('classSections', 'examTypes'));
    }
    
    /**
     * Store a newly created exam schedule
     */
    public function store(Request $request)
    {
        $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'exam_name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
            'practical_marks' => 'nullable|integer|min:0',
            'room_number' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        
        $teacher = Auth::user();
        
        // Verify teacher teaches this subject and class
        $teachesSubject = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();
        
        if (!$teachesSubject) {
            return redirect()->back()->with('error', 'You are not authorized to create exam for this subject/class.')
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Create or get exam
            $exam = Exam::updateOrCreate(
                [
                    'name' => $request->exam_name,
                    'exam_type_id' => $request->exam_type_id,
                    'academic_year_id' => \App\Models\AcademicYear::getCurrent()->id,
                ],
                [
                    'start_date' => $request->exam_date,
                    'end_date' => $request->exam_date,
                    'description' => $request->description,
                    'status' => 'upcoming',
                ]
            );
            
            // Create exam schedule
            $examSchedule = ExamSchedule::create([
                'exam_id' => $exam->id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'subject_id' => $request->subject_id,
                'exam_date' => $request->exam_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'total_marks' => $request->total_marks,
                'passing_marks' => $request->passing_marks,
                'practical_marks' => $request->practical_marks ?? 0,
                'room_number' => $request->room_number,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'exam',
                'description' => "Created exam schedule for {$exam->name} - {$examSchedule->subject->name}",
            ]);
            
            return redirect()->route('teacher.exams.index')
                ->with('success', 'Exam scheduled successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create exam: ' . $e->getMessage());
        }
    }
    
    /**
     * Get upcoming exams only
     */
    public function upcoming()
    {
        $teacher = Auth::user();
        
        $upcomingExams = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereHas('exam', function($q) {
                $q->where('start_date', '>=', Carbon::today());
            })
            ->with(['exam', 'class', 'section', 'subject'])
            ->orderBy('exam_date')
            ->get();
        
        return view('teacher.exams.upcoming', compact('upcomingExams'));
    }
    
    /**
     * Get students for marking marks
     */
    public function students(ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        // Verify teacher is assigned to this exam
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            abort(403, 'Unauthorized action.');
        }
        
        $students = Student::where('class_id', $examSchedule->class_id)
            ->where('section_id', $examSchedule->section_id)
            ->with('user')
            ->orderBy('roll_number')
            ->get();
        
        // Get existing results
        $existingResults = ExamResult::where('exam_schedule_id', $examSchedule->id)
            ->get()
            ->keyBy('student_id');
        
        $studentsData = $students->map(function($student) use ($existingResults) {
            $result = $existingResults[$student->id] ?? null;
            return [
                'id' => $student->id,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'name' => $student->user->name,
                'profile_photo' => $student->user->profile_photo_url,
                'theory_marks' => $result?->theory_marks_obtained,
                'practical_marks' => $result?->practical_marks_obtained,
                'total_marks' => $result?->total_marks_obtained,
                'remarks' => $result?->remarks,
                'is_marked' => !is_null($result),
            ];
        });
        
        $stats = [
            'total_students' => $students->count(),
            'marked_count' => $existingResults->count(),
            'pending_count' => $students->count() - $existingResults->count(),
            'theory_max' => $examSchedule->total_marks,
            'practical_max' => $examSchedule->practical_marks ?? 0,
            'total_max' => ($examSchedule->total_marks ?? 0) + ($examSchedule->practical_marks ?? 0),
        ];
        
        return view('teacher.exams.mark', compact('examSchedule', 'studentsData', 'stats'));
    }
    
    /**
     * Save marks for students
     */
    public function saveMarks(Request $request, ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        // Verify teacher is assigned to this exam
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }
        
        $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.theory_marks' => 'nullable|integer|min:0|max:' . ($examSchedule->total_marks ?? 100),
            'marks.*.practical_marks' => 'nullable|integer|min:0|max:' . ($examSchedule->practical_marks ?? 0),
            'marks.*.remarks' => 'nullable|string|max:500',
        ]);
        
        DB::beginTransaction();
        
        try {
            $saved = 0;
            foreach ($request->marks as $data) {
                $theoryMarks = $data['theory_marks'] ?? 0;
                $practicalMarks = $data['practical_marks'] ?? 0;
                $totalMarks = $theoryMarks + $practicalMarks;
                $maxMarks = ($examSchedule->total_marks ?? 0) + ($examSchedule->practical_marks ?? 0);
                $percentage = $maxMarks > 0 ? round(($totalMarks / $maxMarks) * 100, 2) : 0;
                
                ExamResult::updateOrCreate(
                    [
                        'exam_schedule_id' => $examSchedule->id,
                        'student_id' => $data['student_id'],
                    ],
                    [
                        'theory_marks_obtained' => $theoryMarks,
                        'practical_marks_obtained' => $practicalMarks,
                        'total_marks_obtained' => $totalMarks,
                        'percentage' => $percentage,
                        'grade' => $this->calculateGrade($percentage),
                        'remarks' => $data['remarks'] ?? null,
                    ]
                );
                $saved++;
            }
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'exam',
                'description' => "Saved marks for exam schedule ID: {$examSchedule->id}",
            ]);
            
            return redirect()->route('teacher.exams.index')
                ->with('success', "Marks saved successfully for {$saved} students.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save marks: ' . $e->getMessage());
        }
    }
    
    /**
     * View exam results
     */
    public function results(Request $request)
    {
        $teacher = Auth::user();
        
        $query = ExamResult::whereHas('examSchedule', function($q) use ($teacher) {
                $q->whereHas('classSubject', function($cq) use ($teacher) {
                    $cq->where('teacher_id', $teacher->id);
                });
            })
            ->with(['student.user', 'examSchedule.exam', 'examSchedule.subject']);
        
        // Filter by exam
        if ($request->filled('exam_id')) {
            $query->whereHas('examSchedule', function($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            });
        }
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }
        
        // Filter by section
        if ($request->filled('section_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }
        
        $results = $query->orderBy('created_at', 'desc')
            ->paginate(30)
            ->appends($request->query());
        
        // Get filters data
        $exams = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with('exam')
            ->get()
            ->pluck('exam')
            ->unique('id')
            ->values();
        
        $classes = Timetable::where('teacher_id', $teacher->id)
            ->with('class')
            ->get()
            ->pluck('class')
            ->unique('id')
            ->values();
        
        return view('teacher.exams.results', compact('results', 'exams', 'classes'));
    }
    
    /**
     * Export marks to Excel
     */
    public function export(ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        // Verify teacher is assigned to this exam
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            abort(403, 'Unauthorized action.');
        }
        
        $students = Student::where('class_id', $examSchedule->class_id)
            ->where('section_id', $examSchedule->section_id)
            ->with('user')
            ->orderBy('roll_number')
            ->get();
        
        $results = ExamResult::where('exam_schedule_id', $examSchedule->id)
            ->get()
            ->keyBy('student_id');
        
        $exportData = [];
        foreach ($students as $student) {
            $result = $results[$student->id] ?? null;
            $exportData[] = [
                'Roll No' => $student->roll_number ?? '-',
                'Admission No' => $student->admission_number,
                'Student Name' => $student->user->name,
                'Theory Marks' => $result?->theory_marks_obtained ?? '-',
                'Practical Marks' => $result?->practical_marks_obtained ?? '-',
                'Total Marks' => $result?->total_marks_obtained ?? '-',
                'Max Marks' => ($examSchedule->total_marks ?? 0) + ($examSchedule->practical_marks ?? 0),
                'Percentage' => $result?->percentage ? $result->percentage . '%' : '-',
                'Grade' => $result?->grade ?? '-',
                'Remarks' => $result?->remarks ?? '-',
            ];
        }
        
        return Excel::download(new ExamMarksExport($exportData), "exam_results_{$examSchedule->exam->name}_{$examSchedule->subject->name}.xlsx");
    }
    
    /**
     * Show form for editing exam schedule
     */
    public function edit(ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        // Verify teacher is assigned to this exam
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            abort(403, 'Unauthorized action.');
        }
        
        $classSections = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section'])
            ->get()
            ->unique(function($item) {
                return $item->class_id . '_' . $item->section_id;
            })
            ->map(function($item) {
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name,
                    'section_id' => $item->section_id,
                    'section_name' => $item->section->name,
                ];
            })
            ->values();
        
        $subjects = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->values();
        
        $examTypes = ExamType::all();
        
        return view('teacher.exams.edit', compact('examSchedule', 'classSections', 'subjects', 'examTypes'));
    }
    
    /**
     * Update exam schedule
     */
    public function update(Request $request, ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        // Verify teacher is assigned to this exam
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'exam_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
            'practical_marks' => 'nullable|integer|min:0',
            'room_number' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update exam
            $examSchedule->exam->update([
                'start_date' => $request->exam_date,
                'end_date' => $request->exam_date,
                'description' => $request->description,
            ]);
            
            // Update exam schedule
            $examSchedule->update([
                'exam_date' => $request->exam_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'total_marks' => $request->total_marks,
                'passing_marks' => $request->passing_marks,
                'practical_marks' => $request->practical_marks ?? 0,
                'room_number' => $request->room_number,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'exam',
                'description' => "Updated exam schedule ID: {$examSchedule->id}",
            ]);
            
            return redirect()->route('teacher.exams.index')
                ->with('success', 'Exam updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update exam: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete exam schedule
     */
    public function destroy(ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        // Verify teacher is assigned to this exam
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if results exist
        if ($examSchedule->results()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete exam with existing results.');
        }
        
        $examSchedule->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'exam',
            'description' => "Deleted exam schedule ID: {$examSchedule->id}",
        ]);
        
        return redirect()->route('teacher.exams.index')
            ->with('success', 'Exam deleted successfully.');
    }
    
    /**
     * Get subjects by class (AJAX)
     */
    public function getSubjectsByClass($classId)
    {
        $teacher = Auth::user();
        
        $subjects = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->with('subject')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->subject->id,
                    'name' => $item->subject->name,
                    'code' => $item->subject->code,
                ];
            });
        
        return response()->json($subjects);
    }
    
    /**
     * Get sections by class (AJAX)
     */
    public function getSectionsByClass($classId)
    {
        $class = Classes::findOrFail($classId);
        return response()->json($class->sections);
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