<?php
// app/Http/Controllers/Admin/ExamResultController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Student;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    public function index(Request $request)
    {
        $query = ExamResult::with(['student.user', 'examSchedule.exam', 'examSchedule.subject']);

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

        $results = $query->orderBy('created_at', 'desc')->paginate(20);
        $examSchedules = ExamSchedule::with('exam')->get();

        return view('admin.exam-results.index', compact('results', 'examSchedules'));
    }

    public function create()
    {
        $examSchedules = ExamSchedule::with(['exam', 'class', 'section', 'subject'])->get();
        return view('admin.exam-results.create', compact('examSchedules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_schedule_id' => 'required|exists:exam_schedules,id',
            'student_id' => 'required|exists:students,id',
            'theory_marks_obtained' => 'nullable|integer|min:0',
            'practical_marks_obtained' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $examSchedule = ExamSchedule::find($validated['exam_schedule_id']);
        $theoryMarks = $validated['theory_marks_obtained'] ?? 0;
        $practicalMarks = $validated['practical_marks_obtained'] ?? 0;
        $totalMarks = $theoryMarks + $practicalMarks;
        $maxMarks = $examSchedule->total_marks;

        $validated['total_marks_obtained'] = $totalMarks;
        $validated['grade'] = $this->calculateGrade($totalMarks, $maxMarks);

        ExamResult::create($validated);

        return redirect()->route('admin.exam-results.index')->with('success', 'Exam result added successfully.');
    }

    public function show(ExamResult $examResult)
    {
        $examResult->load(['student.user', 'examSchedule.exam', 'examSchedule.subject']);
        return view('admin.exam-results.show', compact('examResult'));
    }

    public function edit(ExamResult $examResult)
    {
        $examSchedules = ExamSchedule::with(['exam', 'class', 'section', 'subject'])->get();
        return view('admin.exam-results.edit', compact('examResult', 'examSchedules'));
    }

    public function update(Request $request, ExamResult $examResult)
    {
        $validated = $request->validate([
            'exam_schedule_id' => 'required|exists:exam_schedules,id',
            'student_id' => 'required|exists:students,id',
            'theory_marks_obtained' => 'nullable|integer|min:0',
            'practical_marks_obtained' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $examSchedule = ExamSchedule::find($validated['exam_schedule_id']);
        $theoryMarks = $validated['theory_marks_obtained'] ?? 0;
        $practicalMarks = $validated['practical_marks_obtained'] ?? 0;
        $totalMarks = $theoryMarks + $practicalMarks;
        $maxMarks = $examSchedule->total_marks;

        $validated['total_marks_obtained'] = $totalMarks;
        $validated['grade'] = $this->calculateGrade($totalMarks, $maxMarks);

        $examResult->update($validated);

        return redirect()->route('admin.exam-results.index')->with('success', 'Exam result updated successfully.');
    }

    public function destroy(ExamResult $examResult)
    {
        $examResult->delete();
        return redirect()->route('admin.exam-results.index')->with('success', 'Exam result deleted successfully.');
    }

    public function bulkEntry(Request $request, ExamSchedule $examSchedule)
    {
        $students = Student::where('class_id', $examSchedule->class_id)
            ->where('section_id', $examSchedule->section_id)
            ->with('user')
            ->orderBy('roll_number')
            ->get();

        $existingResults = ExamResult::where('exam_schedule_id', $examSchedule->id)
            ->get()
            ->keyBy('student_id');

        return view('admin.exam-results.bulk', compact('examSchedule', 'students', 'existingResults'));
    }

    public function bulkStore(Request $request, ExamSchedule $examSchedule)
    {
        $validated = $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.theory_marks' => 'nullable|integer|min:0',
            'marks.*.practical_marks' => 'nullable|integer|min:0',
            'marks.*.remarks' => 'nullable|string|max:500',
        ]);

        foreach ($validated['marks'] as $data) {
            $theoryMarks = $data['theory_marks'] ?? 0;
            $practicalMarks = $data['practical_marks'] ?? 0;
            $totalMarks = $theoryMarks + $practicalMarks;
            $maxMarks = $examSchedule->total_marks;

            ExamResult::updateOrCreate(
                [
                    'exam_schedule_id' => $examSchedule->id,
                    'student_id' => $data['student_id'],
                ],
                [
                    'theory_marks_obtained' => $theoryMarks,
                    'practical_marks_obtained' => $practicalMarks,
                    'total_marks_obtained' => $totalMarks,
                    'grade' => $this->calculateGrade($totalMarks, $maxMarks),
                    'remarks' => $data['remarks'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.exam-results.index')->with('success', 'Results saved successfully.');
    }

    private function calculateGrade($obtained, $total)
    {
        $percentage = ($obtained / $total) * 100;
        
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