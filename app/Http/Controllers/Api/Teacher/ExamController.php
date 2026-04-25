<?php
// app/Http/Controllers/Api/Teacher/ExamController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\ExamSchedule;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ExamController extends BaseController
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        $query = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['exam', 'class', 'section', 'subject']);
        
        if ($request->filled('status')) {
            if ($request->status == 'upcoming') {
                $query->where('exam_date', '>=', Carbon::today());
            } elseif ($request->status == 'completed') {
                $query->where('exam_date', '<', Carbon::today());
            }
        }
        
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }
        
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        
        $exams = $query->orderBy('exam_date')->paginate($request->per_page ?? 20);
        
        return $this->sendPaginatedResponse($exams, 'Exams retrieved');
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', [$validator->errors()], 422);
        }
        
        $teacher = Auth::user();
        
        $teachesSubject = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();
        
        if (!$teachesSubject) {
            return $this->sendError('Unauthorized for this subject/class', [], 403);
        }
        
        DB::beginTransaction();
        
        try {
            $exam = Exam::updateOrCreate(
                [
                    'name' => $request->exam_name,
                    'exam_type_id' => $request->exam_type_id,
                    'academic_year_id' => AcademicYear::where('is_current', true)->first()?->id,
                ],
                [
                    'start_date' => $request->exam_date,
                    'end_date' => $request->exam_date,
                    'status' => 'upcoming',
                ]
            );
            
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
            
            return $this->sendResponse($examSchedule, 'Exam scheduled', 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to schedule exam: ' . $e->getMessage(), [], 500);
        }
    }
    
    public function show(ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $examSchedule->load(['exam', 'class', 'section', 'subject']);
        
        return $this->sendResponse($examSchedule, 'Exam retrieved');
    }
    
    public function update(Request $request, ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'exam_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
            'practical_marks' => 'nullable|integer|min:0',
            'room_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', [$validator->errors()], 422);
        }
    
        
        DB::beginTransaction();
        
        try {
            $examSchedule->exam->update([
                'start_date' => $request->exam_date,
                'end_date' => $request->exam_date,
            ]);
            
            $examSchedule->update($request->only([
                'exam_date', 'start_time', 'end_time', 'total_marks',
                'passing_marks', 'practical_marks', 'room_number'
            ]));
            
            DB::commit();
            
            return $this->sendResponse($examSchedule, 'Exam updated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update exam: ' . $e->getMessage(), [], 500);
        }
    }
    
    public function destroy(ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        if ($examSchedule->results()->count() > 0) {
            return $this->sendError('Cannot delete exam with existing results', [], 422);
        }
        
        $examSchedule->delete();
        
        return $this->sendResponse([], 'Exam deleted');
    }
    
    public function upcoming()
    {
        $teacher = Auth::user();
        
        $exams = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('exam_date', '>=', Carbon::today())
            ->with(['exam', 'class', 'section', 'subject'])
            ->orderBy('exam_date')
            ->limit(10)
            ->get();
        
        return $this->sendResponse($exams, 'Upcoming exams retrieved');
    }
    
    public function students(ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $students = Student::where('class_id', $examSchedule->class_id)
            ->where('section_id', $examSchedule->section_id)
            ->with('user')
            ->orderBy('roll_number')
            ->get();
        
        $existingResults = ExamResult::where('exam_schedule_id', $examSchedule->id)
            ->get()
            ->keyBy('student_id');
        
        $studentsData = $students->map(function($student) use ($existingResults) {
            $result = $existingResults[$student->id] ?? null;
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'theory_marks' => $result?->theory_marks_obtained,
                'practical_marks' => $result?->practical_marks_obtained,
                'remarks' => $result?->remarks,
                'is_marked' => !is_null($result),
            ];
        });
        
        return $this->sendResponse([
            'students' => $studentsData,
            'exam_schedule' => $examSchedule,
            'total_students' => $students->count(),
            'marked_count' => $existingResults->count(),
        ], 'Students retrieved');
    }
    
    public function saveMarks(Request $request, ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $validator  =   Validator::make($request->all(), [
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.theory_marks' => 'nullable|integer|min:0|max:' . $examSchedule->total_marks,
            'marks.*.practical_marks' => 'nullable|integer|min:0|max:' . ($examSchedule->practical_marks ?? 0),
            'marks.*.remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', [$validator->errors()], 422);
        }

        DB::beginTransaction();
        
        try {
            foreach ($request->marks as $data) {
                $theoryMarks = $data['theory_marks'] ?? 0;
                $practicalMarks = $data['practical_marks'] ?? 0;
                $totalMarks = $theoryMarks + $practicalMarks;
                $maxMarks = $examSchedule->total_marks + ($examSchedule->practical_marks ?? 0);
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
            }
            
            DB::commit();
            
            return $this->sendResponse([
                'saved_count' => count($request->marks),
            ], 'Marks saved successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to save marks: ' . $e->getMessage(), [], 500);
        }
    }
    
    public function results(Request $request)
    {
        $teacher = Auth::user();
        
        $query = ExamResult::whereHas('examSchedule', function($q) use ($teacher) {
                $q->whereHas('classSubject', function($cq) use ($teacher) {
                    $cq->where('teacher_id', $teacher->id);
                });
            })
            ->with(['student.user', 'examSchedule.exam', 'examSchedule.subject']);
        
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
        
        $results = $query->paginate($request->per_page ?? 50);
        
        return $this->sendPaginatedResponse($results, 'Results retrieved');
    }
    
    public function export(ExamSchedule $examSchedule)
    {
        $teacher = Auth::user();
        
        $isAssigned = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $examSchedule->class_id)
            ->where('subject_id', $examSchedule->subject_id)
            ->exists();
        
        if (!$isAssigned) {
            return $this->sendError('Unauthorized', [], 403);
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
                'Roll No' => $student->roll_number,
                'Admission No' => $student->admission_number,
                'Student Name' => $student->user->name,
                'Theory Marks' => $result?->theory_marks_obtained ?? '-',
                'Practical Marks' => $result?->practical_marks_obtained ?? '-',
                'Total Marks' => $result?->total_marks_obtained ?? '-',
                'Max Marks' => $examSchedule->total_marks + ($examSchedule->practical_marks ?? 0),
                'Percentage' => $result?->percentage ?? '-',
                'Grade' => $result?->grade ?? '-',
            ];
        }
        
        return $this->sendResponse($exportData, 'Export data prepared');
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

    public function dropdown()
    {
        $examTypes = ExamType::orderBy('name')
            ->get()
            ->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'value' => $type->id,
                    'label' => $type->name,
                ];
            });
        
        return $this->sendResponse($examTypes, 'Exam types retrieved for dropdown');
    }
}