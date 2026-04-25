<?php
// app/Http/Controllers/Admin/ClassController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index(Request $request)
    {
        $query = Classes::with(['academicYear', 'classTeacher', 'sections']);

        // Filter by academic year
        if ($request->has('academic_year_id') && $request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('numeric_name', 'like', "%{$search}%");
            });
        }

        $classes = $query->orderBy('numeric_name')->paginate(50);
        $academicYears = AcademicYear::all();

        return view('admin.classes.index', compact('classes', 'academicYears'));
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $academicYears = AcademicYear::all();
        $teachers = User::where('user_type', 'teacher')->get();
        
        return view('admin.classes.create', compact('academicYears', 'teachers'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'numeric_name' => 'nullable|integer',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|integer|min:1',
        ]);

        Classes::create($request->all());

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified class.
     */
    public function show(Classes $class)
    {
        // $class->load(['academicYear', 'classTeacher', 'sections', 'students.user']);
        
        return view('admin.classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(Classes $class)
    {
        $academicYears = AcademicYear::all();
        $teachers = User::where('user_type', 'teacher')->get();
        
        return view('admin.classes.edit', compact('class', 'academicYears', 'teachers'));
    }

    /**
     * Update the specified class in storage.
     */
    public function update(Request $request, Classes $class)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'numeric_name' => 'nullable|integer',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $class->update($request->all());

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(Classes $class)
    {
        $studentCount = 0;
        $sections = $class->sections()->withCount('students')->get();
        foreach ($sections as $section) {
            $studentCount += $section->students_count;
        }

        if ($studentCount > 0) {
            return redirect()->back()->with('error',
                "Cannot delete class. It has {$studentCount} student(s) in its sections."
            );
        }

        if( $class->sections()->count() > 0) {
            return redirect()->back()->with('error',
                "Cannot delete class. It has {$class->sections()->count()} section(s). Please delete sections first."
            );
        }

        

        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class deleted successfully.');
    }

    /**
     * Get students for a class (AJAX)
     */
    public function students(Classes $class)
    {
        $students = $class->students()->with('user')->orderBy('roll_number')->get();
        
        if (request()->ajax()) {
            return response()->json($students);
        }
        
        return view('admin.classes.students', compact('class', 'students'));
    }

    /**
     * Assign teacher to class (AJAX)
     */
    public function assignTeacher(Request $request, Classes $class)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id'
        ]);

        $class->update(['class_teacher_id' => $request->teacher_id]);

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Teacher assigned successfully.');
    }
}