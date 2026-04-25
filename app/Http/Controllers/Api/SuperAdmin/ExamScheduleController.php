<?php
// app/Http/Controllers/Api/SuperAdmin/ExamScheduleController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\ExamSchedule;
use Illuminate\Http\Request;

class ExamScheduleController extends BaseController
{
    public function index()
    {
        $schedules = ExamSchedule::with(['exam', 'class', 'section', 'subject'])->get();
        return $this->sendResponse($schedules, 'Schedules retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'total_marks' => 'required|integer',
            'passing_marks' => 'required|integer|lt:total_marks',
            'room_number' => 'nullable|string',
        ]);
        $schedule = ExamSchedule::create($validated);
        return $this->sendResponse($schedule, 'Schedule created', 201);
    }

    public function show(ExamSchedule $examSchedule)
    {
        $examSchedule->load(['exam', 'class', 'section', 'subject']);
        return $this->sendResponse($examSchedule, 'Schedule retrieved');
    }

    public function update(Request $request, ExamSchedule $examSchedule)
    {
        $validated = $request->validate([
            'exam_id' => 'sometimes|exists:exams,id',
            'class_id' => 'sometimes|exists:classes,id',
            'section_id' => 'sometimes|exists:sections,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'exam_date' => 'sometimes|date',
            'start_time' => 'sometimes',
            'end_time' => 'sometimes',
            'total_marks' => 'sometimes|integer',
            'passing_marks' => 'sometimes|integer|lt:total_marks',
            'room_number' => 'nullable|string',
        ]);
        $examSchedule->update($validated);
        return $this->sendResponse($examSchedule, 'Schedule updated');
    }

    public function destroy(ExamSchedule $examSchedule)
    {
        $examSchedule->delete();
        return $this->sendResponse([], 'Schedule deleted');
    }
}