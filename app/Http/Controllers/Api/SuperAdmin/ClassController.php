<?php
// app/Http/Controllers/Api/SuperAdmin/ClassController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Classes;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class ClassController extends BaseController
{
    public function index()
    {
        $classes = Classes::with('academicYear')->get();
        return $this->sendResponse($classes, 'Classes retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'numeric_name' => 'nullable|integer',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|integer',
        ]);

        $class = Classes::create($validated);
        return $this->sendResponse($class, 'Class created', 201);
    }

    public function show(Classes $class)
    {
        $class->load('academicYear', 'sections', 'subjects');
        return $this->sendResponse($class, 'Class retrieved');
    }

    public function update(Request $request, Classes $class)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'numeric_name' => 'nullable|integer',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'class_teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|integer',
        ]);

        $class->update($validated);
        return $this->sendResponse($class, 'Class updated');
    }

    public function destroy(Classes $class)
    {
        $class->delete();
        return $this->sendResponse([], 'Class deleted');
    }

    public function students(Classes $class)
    {
        $students = $class->students()->with('user')->get();
        return $this->sendResponse($students, 'Students retrieved');
    }

    public function assignTeacher(Request $request, Classes $class)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);
        $class->update(['class_teacher_id' => $request->teacher_id]);
        return $this->sendResponse($class, 'Teacher assigned');
    }
}