<?php
// app/Http/Controllers/SuperAdmin/TeacherController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\UserProfile;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TeachersImport;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'teacher')
            ->with(['profile', 'employee', 'teachingSubjects.class']);
        
        // Filter by department
        if ($request->filled('department')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('employee_id', 'like', "%{$search}%")
                         ->orWhere('designation', 'like', "%{$search}%");
                  });
            });
        }
        
        $teachers = $query->orderBy('created_at', 'desc')->paginate(20);
        $departments = Employee::whereNotNull('department')->distinct()->pluck('department');
        
        return view('super-admin.teachers.index', compact('teachers', 'departments'));
    }
    
    /**
     * Show form for creating new teacher
     */
    public function create()
    {
        $classes = Classes::with('sections')->get();
        $subjects = Subject::all();
        
        return view('super-admin.teachers.create', compact('classes', 'subjects'));
    }
    
    /**
     * Store a newly created teacher
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'username' => 'required|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'department' => 'required|string',
            'designation' => 'required|string',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'joining_date' => 'required|date',
            'salary' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')->store('profiles', 'public');
            }
            
            // Create user account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'user_type' => 'teacher',
                'profile_photo' => $photoPath,
                'is_active' => true,
            ]);
            
            $user->assignRole('teacher');
            
            // Create profile
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'qualification' => $request->qualification,
                'experience_years' => $request->experience_years,
            ]);
            
            // Generate employee ID
            $employeeId = $this->generateEmployeeId();
            
            // Create employee record
            Employee::create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'employment_type' => $request->employment_type,
                'joining_date' => $request->joining_date,
                'department' => $request->department,
                'designation' => $request->designation,
                'salary' => $request->salary,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'ifsc_code' => $request->ifsc_code,
                'pan_number' => $request->pan_number,
                'qualification' => $request->qualification,
                'experience_years' => $request->experience_years,
            ]);
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'teacher',
                'description' => "Created teacher: {$user->name} (ID: {$employeeId})",
            ]);
            
            return redirect()->route('super-admin.teachers.index')
                ->with('success', "Teacher created successfully. Employee ID: {$employeeId}");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create teacher: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display teacher details
     */
    public function show(User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return redirect()->route('super-admin.teachers.index')->with('error', 'User is not a teacher');
        }
        
        $teacher->load(['profile', 'employee', 'teachingSubjects.class', 'teachingSubjects.subject']);
        
        // Get assigned classes with subjects
        $assignedClasses = ClassSubject::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->get()
            ->groupBy('class_id')
            ->map(function($items) {
                $class = $items->first()->class;
                return [
                    'class' => $class,
                    'subjects' => $items->pluck('subject'),
                ];
            });
        
        $availableClasses = Classes::with('sections')->get();
        $availableSubjects = Subject::all();
        
        return view('super-admin.teachers.show', compact('teacher', 'assignedClasses', 'availableClasses', 'availableSubjects'));
    }
    
    /**
     * Show form for editing teacher
     */
    public function edit(User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return redirect()->route('super-admin.teachers.index')->with('error', 'User is not a teacher');
        }
        
        $teacher->load(['profile', 'employee']);
        $classes = Classes::with('sections')->get();
        $subjects = Subject::all();
        
        return view('super-admin.teachers.edit', compact('teacher', 'classes', 'subjects'));
    }
    
    /**
     * Update the specified teacher
     */
    public function update(Request $request, User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return redirect()->route('super-admin.teachers.index')->with('error', 'User is not a teacher');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $teacher->id,
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'department' => 'required|string',
            'designation' => 'required|string',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'joining_date' => 'required|date',
            'salary' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update user
            $teacher->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
            
            // Update profile photo
            if ($request->hasFile('profile_photo')) {
                if ($teacher->profile_photo) {
                    \Storage::disk('public')->delete($teacher->profile_photo);
                }
                $photoPath = $request->file('profile_photo')->store('profiles', 'public');
                $teacher->update(['profile_photo' => $photoPath]);
            }
            
            // Update password if provided
            if ($request->filled('password')) {
                $request->validate(['password' => 'min:8|confirmed']);
                $teacher->update(['password' => Hash::make($request->password)]);
            }
            
            // Update profile
            if ($teacher->profile) {
                $teacher->profile->update([
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'qualification' => $request->qualification,
                    'experience_years' => $request->experience_years,
                ]);
            }
            
            // Update employee
            if ($teacher->employee) {
                $teacher->employee->update([
                    'employment_type' => $request->employment_type,
                    'joining_date' => $request->joining_date,
                    'department' => $request->department,
                    'designation' => $request->designation,
                    'salary' => $request->salary,
                    'bank_name' => $request->bank_name,
                    'bank_account' => $request->bank_account,
                    'ifsc_code' => $request->ifsc_code,
                    'pan_number' => $request->pan_number,
                    'qualification' => $request->qualification,
                    'experience_years' => $request->experience_years,
                ]);
            }
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'teacher',
                'description' => "Updated teacher: {$teacher->name}",
            ]);
            
            return redirect()->route('super-admin.teachers.index')
                ->with('success', 'Teacher updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update teacher: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete teacher
     */
    public function destroy(User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return redirect()->route('super-admin.teachers.index')->with('error', 'User is not a teacher');
        }
        
        $teacherName = $teacher->name;
        
        // Delete profile photo
        if ($teacher->profile_photo) {
            \Storage::disk('public')->delete($teacher->profile_photo);
        }
        
        $teacher->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'teacher',
            'description' => "Deleted teacher: {$teacherName}",
        ]);
        
        return redirect()->route('super-admin.teachers.index')
            ->with('success', 'Teacher deleted successfully.');
    }
    
    /**
     * Assign class/subject to teacher
     */
    public function assignClass(Request $request, User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return response()->json(['error' => 'User is not a teacher'], 422);
        }
        
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'theory_marks' => 'nullable|integer',
            'practical_marks' => 'nullable|integer',
        ]);
        
        DB::beginTransaction();
        
        try {
            $classSubject = ClassSubject::updateOrCreate(
                [
                    'class_id' => $request->class_id,
                    'subject_id' => $request->subject_id,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'theory_marks' => $request->theory_marks ?? 100,
                    'practical_marks' => $request->practical_marks ?? 0,
                ]
            );
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'assigned',
                'module' => 'teacher',
                'description' => "Assigned class/subject to teacher: {$teacher->name}",
            ]);
            
            return redirect()->back()->with('success', 'Class assigned successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to assign class: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove class assignment
     */
    public function removeClass($assignmentId)
    {
        $assignment = ClassSubject::findOrFail($assignmentId);
        $assignment->delete();
        
        return redirect()->back()->with('success', 'Class assignment removed successfully.');
    }
    
    /**
     * Show import form
     */
    public function importForm()
    {
        return view('super-admin.teachers.import');
    }
    
    /**
     * Import teachers from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:5120',
        ]);
        
        try {
            Excel::import(new TeachersImport, $request->file('file'));
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'imported',
                'module' => 'teacher',
                'description' => "Imported teachers from file",
            ]);
            
            return redirect()->route('super-admin.teachers.index')
                ->with('success', 'Teachers imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing teachers: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle teacher status
     */
    public function toggleStatus(User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return redirect()->back()->with('error', 'User is not a teacher');
        }
        
        $teacher->update(['is_active' => !$teacher->is_active]);
        
        $status = $teacher->is_active ? 'activated' : 'deactivated';
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $status,
            'module' => 'teacher',
            'description' => "{$status} teacher: {$teacher->name}",
        ]);
        
        return redirect()->back()->with('success', "Teacher {$status} successfully.");
    }
    
    /**
     * Helper: Generate employee ID
     */
    private function generateEmployeeId()
    {
        $year = now()->format('Y');
        $lastEmployee = Employee::whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastEmployee) {
            $lastNumber = intval(substr($lastEmployee->employee_id, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return 'TCH' . $year . $newNumber;
    }
}