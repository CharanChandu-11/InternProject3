<?php
// app/Http/Controllers/SuperAdmin/ClassController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\Student;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    /**
     * Display a listing of classes
     */
    public function index(Request $request)
    {
        $query = Classes::with(['academicYear', 'classTeacher', 'sections']);
        
        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        } else {
            $currentYear = AcademicYear::where('is_current', true)->first();
            if ($currentYear) {
                $query->where('academic_year_id', $currentYear->id);
            }
        }
        
        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('numeric_name', 'like', "%{$request->search}%");
        }
        
        $classes = $query->orderBy('numeric_name')->paginate(20);
        $academicYears = AcademicYear::all();
        
        return view('super-admin.classes.index', compact('classes', 'academicYears'));
    }
    
    /**
     * Show form for creating new class
     */
    public function create()
    {
        $academicYears = AcademicYear::all();
        $teachers = User::where('user_type', 'teacher')->with('employee')->get();
        
        return view('super-admin.classes.create', compact('academicYears', 'teachers'));
    }
    
    /**
     * Store a newly created class
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'numeric_name' => 'nullable|integer',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'sections' => 'required|array|min:1',
            'sections.*.name' => 'required|string|max:10',
            'sections.*.capacity' => 'required|integer|min:1',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Create class
            $class = Classes::create([
                'name' => $request->name,
                'numeric_name' => $request->numeric_name,
                'academic_year_id' => $request->academic_year_id,
                'class_teacher_id' => $request->class_teacher_id,
                'capacity' => $request->capacity,
            ]);
            
            // Create sections
            foreach ($request->sections as $sectionData) {
                Section::create([
                    'name' => $sectionData['name'],
                    'class_id' => $class->id,
                    'capacity' => $sectionData['capacity'],
                ]);
            }
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'class',
                'description' => "Created class: {$class->name} with " . count($request->sections) . " sections",
            ]);
            
            return redirect()->route('super-admin.classes.index')
                ->with('success', "Class '{$class->name}' created successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create class: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display class details
     */
    public function show(Classes $class)
    {
        $class->load(['academicYear', 'classTeacher', 'sections', 'subjects']);
        
        // Get students count per section
        foreach ($class->sections as $section) {
            $section->students_count = Student::where('class_id', $class->id)
                ->where('section_id', $section->id)
                ->count();
        }
        
        $totalStudents = Student::where('class_id', $class->id)->count();
        
        return view('super-admin.classes.show', compact('class', 'totalStudents'));
    }
    
    /**
     * Show form for editing class
     */
    public function edit(Classes $class)
    {
        $academicYears = AcademicYear::all();
        $teachers = User::where('user_type', 'teacher')->with('employee')->get();
        $class->load('sections');
        
        return view('super-admin.classes.edit', compact('class', 'academicYears', 'teachers'));
    }
    
    /**
     * Update the specified class
     */
    public function update(Request $request, Classes $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'numeric_name' => 'nullable|integer',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'required|integer|min:1',
        ]);
        
        DB::beginTransaction();
        
        try {
            $class->update([
                'name' => $request->name,
                'numeric_name' => $request->numeric_name,
                'academic_year_id' => $request->academic_year_id,
                'class_teacher_id' => $request->class_teacher_id,
                'capacity' => $request->capacity,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'class',
                'description' => "Updated class: {$class->name}",
            ]);
            
            return redirect()->route('super-admin.classes.index')
                ->with('success', "Class '{$class->name}' updated successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update class: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete class
     */
    public function destroy(Classes $class)
    {
        // Check if has students
        if (Student::where('class_id', $class->id)->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete class with enrolled students.');
        }
        
        $className = $class->name;
        
        // Delete sections first
        Section::where('class_id', $class->id)->delete();
        
        $class->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'class',
            'description' => "Deleted class: {$className}",
        ]);
        
        return redirect()->route('super-admin.classes.index')
            ->with('success', "Class '{$className}' deleted successfully.");
    }
    
    /**
     * Get students of a class
     */
    public function students(Classes $class, Request $request)
    {
        $query = Student::where('class_id', $class->id)
            ->with(['user', 'section']);
        
        // Filter by section
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('admission_number', 'like', "%{$search}%");
        }
        
        $students = $query->orderBy('roll_number')->paginate(20);
        $sections = $class->sections;
        
        return view('super-admin.classes.students', compact('class', 'students', 'sections'));
    }
    
    /**
     * Assign class teacher
     */
    public function assignTeacher(Request $request, Classes $class)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);
        
        $teacher = User::find($request->teacher_id);
        
        if ($teacher->user_type != 'teacher') {
            return redirect()->back()->with('error', 'Selected user is not a teacher.');
        }
        
        $class->update(['class_teacher_id' => $request->teacher_id]);
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'assigned',
            'module' => 'class',
            'description' => "Assigned teacher {$teacher->name} as class teacher for {$class->name}",
        ]);
        
        return redirect()->back()->with('success', "Teacher {$teacher->name} assigned as class teacher.");
    }
    
    /**
     * Add section to class
     */
    public function addSection(Request $request, Classes $class)
    {
        $request->validate([
            'name' => 'required|string|max:10|unique:sections,name,NULL,id,class_id,' . $class->id,
            'capacity' => 'required|integer|min:1',
        ]);
        
        $section = Section::create([
            'name' => $request->name,
            'class_id' => $class->id,
            'capacity' => $request->capacity,
        ]);
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'added_section',
            'module' => 'class',
            'description' => "Added section {$section->name} to class {$class->name}",
        ]);
        
        return redirect()->back()->with('success', "Section '{$section->name}' added successfully.");
    }
    
    /**
     * Edit section
     */
    public function editSection(Request $request, Section $section)
    {
        $request->validate([
            'name' => 'required|string|max:10|unique:sections,name,' . $section->id . ',id,class_id,' . $section->class_id,
            'capacity' => 'required|integer|min:1',
        ]);
        
        $section->update([
            'name' => $request->name,
            'capacity' => $request->capacity,
        ]);
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated_section',
            'module' => 'class',
            'description' => "Updated section {$section->name} in class {$section->class->name}",
        ]);
        
        return redirect()->back()->with('success', 'Section updated successfully.');
    }
    
    /**
     * Delete section
     */
    public function deleteSection(Section $section)
    {
        // Check if has students
        if (Student::where('section_id', $section->id)->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete section with enrolled students.');
        }
        
        $sectionName = $section->name;
        $className = $section->class->name;
        $section->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted_section',
            'module' => 'class',
            'description' => "Deleted section {$sectionName} from class {$className}",
        ]);
        
        return redirect()->back()->with('success', "Section '{$sectionName}' deleted successfully.");
    }
}