<?php
// app/Http/Controllers/SuperAdmin/SectionController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Classes;
use App\Models\Student;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    /**
     * Display a listing of sections
     */
    public function index(Request $request)
    {
        $query = Section::with(['class', 'class.academicYear']);
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->whereHas('class', function($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id);
            });
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('class', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $sections = $query->orderBy('class_id')
            ->orderBy('name')
            ->paginate(20);
        
        $classes = Classes::with('academicYear')->orderBy('numeric_name')->get();
        $academicYears = \App\Models\AcademicYear::all();
        
        return view('super-admin.sections.index', compact('sections', 'classes', 'academicYears'));
    }
    
    /**
     * Show form for creating new section
     */
    public function create()
    {
        $classes = Classes::with('academicYear')->orderBy('numeric_name')->get();
        return view('super-admin.sections.create', compact('classes'));
    }
    
    /**
     * Store a newly created section
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:10',
            'class_id' => 'required|exists:classes,id',
            'capacity' => 'required|integer|min:1',
        ]);
        
        // Check if section already exists for this class
        $exists = Section::where('class_id', $request->class_id)
            ->where('name', $request->name)
            ->exists();
        
        if ($exists) {
            return redirect()->back()->with('error', 'Section ' . $request->name . ' already exists for this class.')->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $section = Section::create([
                'name' => $request->name,
                'class_id' => $request->class_id,
                'capacity' => $request->capacity,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'section',
                'description' => "Created section {$section->name} for class {$section->class->name}",
            ]);
            
            return redirect()->route('super-admin.sections.index')
                ->with('success', "Section '{$section->name}' created successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create section: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display section details
     */
    public function show(Section $section)
    {
        $section->load(['class', 'class.academicYear', 'students.user']);
        
        $students = $section->students()
            ->with('user')
            ->orderBy('roll_number')
            ->paginate(20);
        
        $stats = [
            'total_students' => $section->students()->count(),
            'capacity_utilization' => $section->capacity > 0 
                ? round(($section->students()->count() / $section->capacity) * 100, 2) 
                : 0,
            'boys' => $section->students()->whereHas('user.profile', function($q) {
                $q->where('gender', 'male');
            })->count(),
            'girls' => $section->students()->whereHas('user.profile', function($q) {
                $q->where('gender', 'female');
            })->count(),
        ];
        
        return view('super-admin.sections.show', compact('section', 'students', 'stats'));
    }
    
    /**
     * Show form for editing section
     */
    public function edit(Section $section)
    {
        $classes = Classes::with('academicYear')->orderBy('numeric_name')->get();
        return view('super-admin.sections.edit', compact('section', 'classes'));
    }
    
    /**
     * Update the specified section
     */
    public function update(Request $request, Section $section)
    {
        $request->validate([
            'name' => 'required|string|max:10',
            'class_id' => 'required|exists:classes,id',
            'capacity' => 'required|integer|min:1',
        ]);
        
        // Check if section already exists for this class (excluding current)
        $exists = Section::where('class_id', $request->class_id)
            ->where('name', $request->name)
            ->where('id', '!=', $section->id)
            ->exists();
        
        if ($exists) {
            return redirect()->back()->with('error', 'Section ' . $request->name . ' already exists for this class.')->withInput();
        }
        
        // Check if new capacity is less than current student count
        $currentStudents = $section->students()->count();
        if ($request->capacity < $currentStudents) {
            return redirect()->back()->with('error', "Cannot reduce capacity below current student count ({$currentStudents}).")->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $oldName = $section->name;
            $oldClass = $section->class->name;
            
            $section->update([
                'name' => $request->name,
                'class_id' => $request->class_id,
                'capacity' => $request->capacity,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'section',
                'description' => "Updated section {$oldName} of class {$oldClass} to {$section->name}",
            ]);
            
            return redirect()->route('super-admin.sections.index')
                ->with('success', "Section '{$section->name}' updated successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update section: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete section
     */
    public function destroy(Section $section)
    {
        // Check if has students
        $studentCount = $section->students()->count();
        if ($studentCount > 0) {
            return redirect()->back()->with('error', "Cannot delete section with {$studentCount} enrolled students. Please transfer students first.");
        }
        
        $sectionName = $section->name;
        $className = $section->class->name;
        
        $section->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'section',
            'description' => "Deleted section {$sectionName} from class {$className}",
        ]);
        
        return redirect()->route('super-admin.sections.index')
            ->with('success', "Section '{$sectionName}' deleted successfully.");
    }
    
    /**
     * Get sections by class (AJAX)
     */
    public function getByClass($classId)
    {
        $sections = Section::where('class_id', $classId)
            ->select('id', 'name', 'capacity')
            ->get();
        
        return response()->json($sections);
    }
    
    /**
     * Bulk create sections for a class
     */
    public function bulkCreate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'sections' => 'required|array|min:1',
            'sections.*.name' => 'required|string|max:10',
            'sections.*.capacity' => 'required|integer|min:1',
        ]);
        
        DB::beginTransaction();
        
        try {
            $created = [];
            foreach ($request->sections as $sectionData) {
                // Skip if already exists
                $exists = Section::where('class_id', $request->class_id)
                    ->where('name', $sectionData['name'])
                    ->exists();
                
                if (!$exists) {
                    $section = Section::create([
                        'name' => $sectionData['name'],
                        'class_id' => $request->class_id,
                        'capacity' => $sectionData['capacity'],
                    ]);
                    $created[] = $section->name;
                }
            }
            
            DB::commit();
            
            $class = Classes::find($request->class_id);
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_created',
                'module' => 'section',
                'description' => "Bulk created sections for class {$class->name}: " . implode(', ', $created),
            ]);
            
            return redirect()->route('super-admin.sections.index', ['class_id' => $request->class_id])
                ->with('success', count($created) . ' sections created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create sections: ' . $e->getMessage());
        }
    }
}