<?php
// app/Http/Controllers/SuperAdmin/SubjectController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\User;
use App\Models\ClassSubject;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects
     */
    public function index(Request $request)
    {
        $query = Subject::query();
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $subjects = $query->orderBy('name')->paginate(20);
        $subjectTypes = ['core', 'elective', 'language', 'practical'];
        
        return view('super-admin.subjects.index', compact('subjects', 'subjectTypes'));
    }
    
    /**
     * Show form for creating new subject
     */
    public function create()
    {
        $subjectTypes = ['core', 'elective', 'language', 'practical'];
        return view('super-admin.subjects.create', compact('subjectTypes'));
    }
    
    /**
     * Store a newly created subject
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects',
            'type' => 'required|in:core,elective,language,practical',
            'description' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $subject = Subject::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'type' => $request->type,
                'description' => $request->description,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'subject',
                'description' => "Created subject: {$subject->name} ({$subject->code})",
            ]);
            
            return redirect()->route('super-admin.subjects.index')
                ->with('success', "Subject '{$subject->name}' created successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create subject: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display subject details
     */
    public function show(Subject $subject)
    {
        $subject->load(['classes' => function($q) {
            $q->withPivot('teacher_id', 'theory_marks', 'practical_marks');
        }]);
        
        // Get assigned classes with teacher details
        $assignedClasses = [];
        foreach ($subject->classes as $class) {
            $assignedClasses[] = [
                'class' => $class,
                'teacher' => User::find($class->pivot->teacher_id),
                'theory_marks' => $class->pivot->theory_marks,
                'practical_marks' => $class->pivot->practical_marks,
                'is_lab_required' => $class->pivot->is_lab_required ?? false,
            ];
        }
        
        $availableClasses = Classes::with('academicYear')->orderBy('numeric_name')->get();
        $teachers = User::where('user_type', 'teacher')->with('employee')->get();
        
        return view('super-admin.subjects.show', compact('subject', 'assignedClasses', 'availableClasses', 'teachers'));
    }
    
    /**
     * Show form for editing subject
     */
    public function edit(Subject $subject)
    {
        $subjectTypes = ['core', 'elective', 'language', 'practical'];
        return view('super-admin.subjects.edit', compact('subject', 'subjectTypes'));
    }
    
    /**
     * Update the specified subject
     */
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'type' => 'required|in:core,elective,language,practical',
            'description' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $oldName = $subject->name;
            $oldCode = $subject->code;
            
            $subject->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'type' => $request->type,
                'description' => $request->description,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'subject',
                'description' => "Updated subject: {$oldName} ({$oldCode}) to {$subject->name} ({$subject->code})",
            ]);
            
            return redirect()->route('super-admin.subjects.index')
                ->with('success', "Subject '{$subject->name}' updated successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update subject: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete subject
     */
    public function destroy(Subject $subject)
    {
        // Check if subject is assigned to any class
        if ($subject->classes()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete subject assigned to classes. Remove assignments first.');
        }
        
        $subjectName = $subject->name;
        $subjectCode = $subject->code;
        
        $subject->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'subject',
            'description' => "Deleted subject: {$subjectName} ({$subjectCode})",
        ]);
        
        return redirect()->route('super-admin.subjects.index')
            ->with('success', "Subject '{$subjectName}' deleted successfully.");
    }
    
    /**
     * Assign subject to class
     */
    public function assignToClass(Request $request, Subject $subject)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:users,id',
            'theory_marks' => 'nullable|integer|min:0',
            'practical_marks' => 'nullable|integer|min:0',
            'is_lab_required' => 'nullable|boolean',
        ]);
        
        // Check if teacher is actually a teacher
        $teacher = User::find($request->teacher_id);
        if ($teacher->user_type != 'teacher') {
            return redirect()->back()->with('error', 'Selected user is not a teacher.');
        }
        
        DB::beginTransaction();
        
        try {
            $classSubject = ClassSubject::updateOrCreate(
                [
                    'class_id' => $request->class_id,
                    'subject_id' => $subject->id,
                ],
                [
                    'teacher_id' => $request->teacher_id,
                    'theory_marks' => $request->theory_marks ?? 100,
                    'practical_marks' => $request->practical_marks ?? 0,
                    'is_lab_required' => $request->is_lab_required ?? false,
                ]
            );
            
            DB::commit();
            
            $class = Classes::find($request->class_id);
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'assigned',
                'module' => 'subject',
                'description' => "Assigned subject {$subject->name} to class {$class->name} with teacher {$teacher->name}",
            ]);
            
            return redirect()->back()->with('success', "Subject assigned to class successfully.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to assign subject: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove subject from class
     */
    public function removeFromClass($assignmentId)
    {
        $assignment = ClassSubject::findOrFail($assignmentId);
        $subjectName = $assignment->subject->name;
        $className = $assignment->class->name;
        
        $assignment->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'removed',
            'module' => 'subject',
            'description' => "Removed subject {$subjectName} from class {$className}",
        ]);
        
        return redirect()->back()->with('success', "Subject removed from class successfully.");
    }
    
    /**
     * Update subject assignment
     */
    public function updateAssignment(Request $request, $assignmentId)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'theory_marks' => 'nullable|integer|min:0',
            'practical_marks' => 'nullable|integer|min:0',
            'is_lab_required' => 'nullable|boolean',
        ]);
        
        $assignment = ClassSubject::findOrFail($assignmentId);
        
        // Check if teacher is actually a teacher
        $teacher = User::find($request->teacher_id);
        if ($teacher->user_type != 'teacher') {
            return redirect()->back()->with('error', 'Selected user is not a teacher.');
        }
        
        $assignment->update([
            'teacher_id' => $request->teacher_id,
            'theory_marks' => $request->theory_marks ?? 100,
            'practical_marks' => $request->practical_marks ?? 0,
            'is_lab_required' => $request->is_lab_required ?? false,
        ]);
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated_assignment',
            'module' => 'subject',
            'description' => "Updated subject assignment for {$assignment->subject->name} in class {$assignment->class->name}",
        ]);
        
        return redirect()->back()->with('success', "Subject assignment updated successfully.");
    }
    
    /**
     * Get subjects by class (AJAX)
     */
    public function getByClass($classId)
    {
        $subjects = ClassSubject::where('class_id', $classId)
            ->with('subject')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->subject->id,
                    'name' => $item->subject->name,
                    'code' => $item->subject->code,
                    'teacher_id' => $item->teacher_id,
                    'teacher_name' => $item->teacher->name,
                    'theory_marks' => $item->theory_marks,
                    'practical_marks' => $item->practical_marks,
                ];
            });
        
        return response()->json($subjects);
    }
    
    /**
     * Bulk import subjects
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'subjects' => 'required|string',
        ]);
        
        $lines = explode("\n", $request->subjects);
        $created = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $parts = explode(',', $line);
                if (count($parts) < 2) {
                    $errors[] = "Invalid line: {$line}";
                    continue;
                }
                
                $name = trim($parts[0]);
                $code = trim($parts[1]);
                $type = isset($parts[2]) ? trim($parts[2]) : 'core';
                $description = isset($parts[3]) ? trim($parts[3]) : null;
                
                if (!in_array($type, ['core', 'elective', 'language', 'practical'])) {
                    $type = 'core';
                }
                
                // Check if subject already exists
                $existing = Subject::where('code', $code)->first();
                if ($existing) {
                    $errors[] = "Subject with code {$code} already exists. Skipped.";
                    continue;
                }
                
                Subject::create([
                    'name' => $name,
                    'code' => strtoupper($code),
                    'type' => $type,
                    'description' => $description,
                ]);
                $created++;
            }
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_import',
                'module' => 'subject',
                'description' => "Bulk imported {$created} subjects",
            ]);
            
            $message = "Successfully imported {$created} subjects.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', $errors);
            }
            
            return redirect()->route('super-admin.subjects.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to import subjects: ' . $e->getMessage());
        }
    }
}