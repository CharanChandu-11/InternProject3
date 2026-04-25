<?php
// app/Http/Controllers/Api/SuperAdmin/ExamResultController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\ExamResult;
use Illuminate\Http\Request;

class ExamResultController extends BaseController
{
    public function index()
    {
        $results = ExamResult::with(['examSchedule', 'student.user'])->get();
        return $this->sendResponse($results, 'Results retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_schedule_id' => 'required|exists:exam_schedules,id',
            'student_id' => 'required|exists:students,id',
            'theory_marks_obtained' => 'nullable|integer',
            'practical_marks_obtained' => 'nullable|integer',
            'remarks' => 'nullable|string',
        ]);

        $schedule = \App\Models\ExamSchedule::find($validated['exam_schedule_id']);
        $total = ($validated['theory_marks_obtained'] ?? 0) + ($validated['practical_marks_obtained'] ?? 0);
        $grade = $this->calculateGrade($total, $schedule->total_marks);

        $result = ExamResult::create(array_merge($validated, [
            'total_marks_obtained' => $total,
            'grade' => $grade,
        ]));

        return $this->sendResponse($result, 'Result created', 201);
    }

    public function show(ExamResult $examResult)
    {
        $examResult->load(['examSchedule', 'student.user']);
        return $this->sendResponse($examResult, 'Result retrieved');
    }

    public function update(Request $request, ExamResult $examResult)
    {
        $validated = $request->validate([
            'theory_marks_obtained' => 'nullable|integer',
            'practical_marks_obtained' => 'nullable|integer',
            'remarks' => 'nullable|string',
        ]);

        $schedule = $examResult->examSchedule;
        $total = ($validated['theory_marks_obtained'] ?? $examResult->theory_marks_obtained) +
                 ($validated['practical_marks_obtained'] ?? $examResult->practical_marks_obtained);
        $grade = $this->calculateGrade($total, $schedule->total_marks);

        $examResult->update(array_merge($validated, [
            'total_marks_obtained' => $total,
            'grade' => $grade,
        ]));

        return $this->sendResponse($examResult, 'Result updated');
    }

    public function destroy(ExamResult $examResult)
    {
        $examResult->delete();
        return $this->sendResponse([], 'Result deleted');
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
            default => 'F',
        };
    }
}