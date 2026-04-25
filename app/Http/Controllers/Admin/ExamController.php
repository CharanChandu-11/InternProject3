<?php
// app/Http/Controllers/Admin/ExamController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $query = Exam::with(['examType', 'academicYear']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('exam_type_id')) {
            $query->where('exam_type_id', $request->exam_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $exams = $query->orderBy('start_date', 'desc')->paginate(20);
        $examTypes = ExamType::all();
        $academicYears = AcademicYear::all();

        return view('admin.exams.index', compact('exams', 'examTypes', 'academicYears'));
    }

    public function create()
    {
        $examTypes = ExamType::all();
        $academicYears = AcademicYear::all();
        return view('admin.exams.create', compact('examTypes', 'academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'exam_type_id' => 'required|exists:exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => 'required|in:upcoming,ongoing,completed',
        ]);

        Exam::create($validated);

        return redirect()->route('admin.exams.index')->with('success', 'Exam created successfully.');
    }

    public function show(Exam $exam)
    {
        $exam->load(['examType', 'academicYear', 'schedules.class', 'schedules.section', 'schedules.subject']);
        return view('admin.exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $examTypes = ExamType::all();
        $academicYears = AcademicYear::all();
        return view('admin.exams.edit', compact('exam', 'examTypes', 'academicYears'));
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'exam_type_id' => 'required|exists:exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => 'required|in:upcoming,ongoing,completed',
        ]);

        $exam->update($validated);

        return redirect()->route('admin.exams.index')->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Exam deleted successfully.');
    }

    public function results(Exam $exam)
    {
        $exam->load(['schedules.class', 'schedules.section', 'schedules.subject', 'schedules.results.student.user']);
        return view('admin.exams.results', compact('exam'));
    }

    public function publishResults(Request $request, Exam $exam)
    {
        // Logic to publish results
        return redirect()->back()->with('success', 'Results published successfully.');
    }
}