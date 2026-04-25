<?php
// app/Http/Controllers/Admin/ExamScheduleController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\Request;

class ExamScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = ExamSchedule::with(['exam', 'class', 'section', 'subject']);

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $schedules = $query->orderBy('exam_date')->paginate(20);
        $exams = Exam::all();
        $classes = Classes::all();

        return view('admin.exam-schedules.index', compact('schedules', 'exams', 'classes'));
    }

    public function create()
    {
        $exams = Exam::all();
        $classes = Classes::with('sections')->get();
        $subjects = Subject::all();
        return view('admin.exam-schedules.create', compact('exams', 'classes', 'subjects'));
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
            'end_time' => 'required|after:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
            'room_number' => 'nullable|string|max:50',
        ]);

        ExamSchedule::create($validated);

        return redirect()->route('admin.exam-schedules.index')->with('success', 'Exam schedule created successfully.');
    }

    public function show(ExamSchedule $examSchedule)
    {
        $examSchedule->load(['exam', 'class', 'section', 'subject', 'results.student.user']);
        return view('admin.exam-schedules.show', compact('examSchedule'));
    }

    public function edit(ExamSchedule $examSchedule)
    {
        $exams = Exam::all();
        $classes = Classes::with('sections')->get();
        $subjects = Subject::all();
        return view('admin.exam-schedules.edit', compact('examSchedule', 'exams', 'classes', 'subjects'));
    }

    public function update(Request $request, ExamSchedule $examSchedule)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
            'room_number' => 'nullable|string|max:50',
        ]);

        $examSchedule->update($validated);

        return redirect()->route('admin.exam-schedules.index')->with('success', 'Exam schedule updated successfully.');
    }

    public function destroy(ExamSchedule $examSchedule)
    {
        $examSchedule->delete();
        return redirect()->route('admin.exam-schedules.index')->with('success', 'Exam schedule deleted successfully.');
    }
}