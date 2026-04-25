<?php
// app/Http/Controllers/Api/Student/SyllabusController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\SyllabusResource;
use App\Models\Syllabus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyllabusController extends BaseController
{
    /**
     * Get list of syllabi for student's class
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

        $syllabi = $query->orderBy('subject_id')->paginate($request->per_page ?? 12);

        // Get subjects for filter
        $subjects = Syllabus::where('class_id', $classId)
            ->where('status', 'published')
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();

        return $this->sendResponse([
            'syllabi' => SyllabusResource::collection($syllabi),
            'subjects' => $subjects,
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
        $student = Auth::user()->student;

        if ($syllabus->class_id != $student->class_id || $syllabus->status != 'published') {
            return $this->sendError('Syllabus not found', [], 404);
        }

        $syllabus->load(['class', 'subject', 'academicYear', 'topics.resources']);

        return $this->sendResponse(new SyllabusResource($syllabus), 'Syllabus retrieved successfully');
    }
}