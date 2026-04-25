<?php
// app/Http/Controllers/Student/SyllabusController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyllabusController extends Controller
{
    /**
     * Display a listing of syllabi for the student's class
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        $classId = $student->class_id;

        $query = Syllabus::with(['class', 'subject', 'academicYear', 'topics'])
            ->where('class_id', $classId)
            ->where('status', 'published');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $syllabi = $query->orderBy('subject_id')->paginate(12);

        // Get subjects for filter
        $subjects = Syllabus::where('class_id', $classId)
            ->where('status', 'published')
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();

        return view('student.syllabi.index', compact('syllabi', 'subjects'));
    }

    /**
     * Display the specified syllabus
     */
    public function show(Syllabus $syllabus)
    {
        $student = Auth::user()->student;

        // Ensure syllabus belongs to student's class and is published
        if ($syllabus->class_id != $student->class_id || $syllabus->status != 'published') {
            abort(404, 'Syllabus not found.');
        }

        $syllabus->load(['class', 'subject', 'academicYear', 'topics.resources']);

        return view('student.syllabi.show', compact('syllabus'));
    }
}