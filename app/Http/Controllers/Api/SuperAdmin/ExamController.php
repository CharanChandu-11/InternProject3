<?php
// app/Http/Controllers/Api/SuperAdmin/ExamController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends BaseController
{
    public function index()
    {
        $exams = Exam::with('examType', 'academicYear')->get();
        return $this->sendResponse($exams, 'Exams retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'exam_type_id' => 'required|exists:exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => 'in:upcoming,ongoing,completed',
        ]);
        $exam = Exam::create($validated);
        return $this->sendResponse($exam, 'Exam created', 201);
    }

    public function show(Exam $exam)
    {
        $exam->load('examType', 'academicYear', 'schedules.subject');
        return $this->sendResponse($exam, 'Exam retrieved');
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'exam_type_id' => 'sometimes|exists:exam_types,id',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => 'in:upcoming,ongoing,completed',
        ]);
        $exam->update($validated);
        return $this->sendResponse($exam, 'Exam updated');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return $this->sendResponse([], 'Exam deleted');
    }

    public function results(Exam $exam)
    {
        $results = $exam->schedules()->with('results.student.user')->get();
        return $this->sendResponse($results, 'Results retrieved');
    }

    public function publishResults(Exam $exam)
    {
        // Logic to publish results (e.g., update status, send notifications)
        $exam->update(['status' => 'completed']);
        return $this->sendResponse($exam, 'Results published');
    }
}