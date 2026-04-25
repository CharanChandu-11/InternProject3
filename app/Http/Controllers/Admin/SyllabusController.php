<?php
// app/Http/Controllers/Admin/SyllabusController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\SyllabusTopic;
use App\Models\SyllabusResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SyllabusController extends Controller
{
    public function index(Request $request)
    {
        $query = Syllabus::with(['class', 'subject', 'academicYear', 'creator']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $syllabi = $query->orderBy('created_at', 'desc')->paginate(20);
        $classes = Classes::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.syllabi.index', compact('syllabi', 'classes', 'subjects'));
    }

    public function create()
    {
        $classes = Classes::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('admin.syllabi.create', compact('classes', 'subjects', 'academicYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'status' => 'required|in:draft,published,archived',
            'publish_date' => 'nullable|date',
        ]);

        $syllabus = Syllabus::create([
            'title' => $request->title,
            'description' => $request->description,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'academic_year_id' => $request->academic_year_id,
            'created_by' => Auth::id(),
            'status' => $request->status,
            'publish_date' => $request->publish_date,
        ]);

        return redirect()->route('admin.syllabi.show', $syllabus)
            ->with('success', 'Syllabus created successfully.');
    }

    public function show(Syllabus $syllabus)
    {
        $syllabus->load(['class', 'subject', 'academicYear', 'creator', 'topics.resources']);
        return view('admin.syllabi.show', compact('syllabus'));
    }

    public function edit(Syllabus $syllabus)
    {
        $classes = Classes::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('admin.syllabi.edit', compact('syllabus', 'classes', 'subjects', 'academicYears'));
    }

    public function update(Request $request, Syllabus $syllabus)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'status' => 'required|in:draft,published,archived',
            'publish_date' => 'nullable|date',
        ]);

        $syllabus->update($request->all());

        return redirect()->route('admin.syllabi.show', $syllabus)
            ->with('success', 'Syllabus updated successfully.');
    }

    public function destroy(Syllabus $syllabus)
    {
        $syllabus->delete();
        return redirect()->route('admin.syllabi.index')
            ->with('success', 'Syllabus deleted successfully.');
    }
}