<?php
// app/Http/Controllers/Api/Teacher/SyllabusController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\SyllabusResource;
use App\Models\Syllabus;
use App\Models\ClassSubject;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyllabusController extends BaseController
{
    /**
     * Get list of syllabi for teacher's classes
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
        
        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        $syllabi = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 12);
        
        // Get filter data
        $classes = Classes::whereIn('id', $classIds)->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        return $this->sendResponse([
            'syllabi' => SyllabusResource::collection($syllabi),
            'filters' => [
                'classes' => $classes,
                'academic_years' => $academicYears,
            ],
            'pagination' => [
                'current_page' => $syllabi->currentPage(),
                'last_page' => $syllabi->lastPage(),
                'per_page' => $syllabi->perPage(),
                'total' => $syllabi->total(),
            ],
        ], 'Syllabi retrieved successfully');
    }
    
    /**
     * Get specific syllabus details
     */
    public function show(Syllabus $syllabus)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this class
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $syllabus->class_id)
            ->exists();
        
        if (!$teachesClass || $syllabus->status !== 'published') {
            return $this->sendError('Syllabus not found', [], 404);
        }
        
        $syllabus->load(['class', 'subject', 'academicYear', 'topics.resources', 'creator']);
        
        return $this->sendResponse(new SyllabusResource($syllabus), 'Syllabus retrieved successfully');
    }
}