<?php
// app/Http/Controllers/Teacher/SyllabusController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\ClassSubject;
use App\Models\Classes;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyllabusController extends Controller
{
    /**
     * Display a listing of syllabi for teacher's classes
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        // Get class IDs taught by this teacher
        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        $query = Syllabus::with(['class', 'subject', 'academicYear', 'topics'])
            ->whereIn('class_id', $classIds)
            ->where('status', 'published');
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        
        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        $syllabi = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->query());
        
        // Get filter data
        $classes = Classes::whereIn('id', $classIds)->orderBy('name')->get();

        $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();
        
        return view('teacher.syllabi.index', compact('syllabi', 'classes', 'academicYears'));
    }
    
    /**
     * Display the specified syllabus
     */
    public function show(Syllabus $syllabus)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this class
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $syllabus->class_id)
            ->exists();
        
        if (!$teachesClass || $syllabus->status !== 'published') {
            abort(404, 'Syllabus not found.');
        }
        
        $syllabus->load(['class', 'subject', 'academicYear', 'topics.resources', 'creator']);
        
        return view('teacher.syllabi.show', compact('syllabus'));
    }
}