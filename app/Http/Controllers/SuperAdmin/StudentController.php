<?php
// app/Http/Controllers/SuperAdmin/StudentController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Classes;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Display a listing of students
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'class', 'section', 'academicYear']);
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter by section
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        
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
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('admission_number', 'like', "%{$search}%")
                  ->orWhere('roll_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        
        $students = $query->orderBy('created_at', 'desc')->paginate(20);
        $classes = Classes::with('sections')->get();
        $academicYears = AcademicYear::all();
        
        return view('super-admin.students.index', compact('students', 'classes', 'academicYears'));
    }
    
    /**
     * Show form for creating new student
     */
    public function create()
    {
        $classes = Classes::with('sections')->get();
        $academicYears = AcademicYear::all();
        $parents = User::where('user_type', 'parent')->get();
        
        return view('super-admin.students.create', compact('classes', 'academicYears', 'parents'));
    }
    
    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'admission_date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'previous_school' => 'nullable|string',
            'previous_grade' => 'nullable|numeric',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:users,id',
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
                'username' => $this->generateUsername($request->name),
                'password' => Hash::make('password123'),
                'phone' => $request->phone,
                'address' => $request->address,
                'user_type' => 'student',
                'profile_photo' => $photoPath,
                'is_active' => true,
            ]);
            
            $user->assignRole('student');
            
            // Create profile
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
            ]);
            
            // Generate admission number
            $admissionNumber = $this->generateAdmissionNumber();
            
            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'admission_number' => $admissionNumber,
                'admission_date' => $request->admission_date,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'academic_year_id' => $request->academic_year_id,
                'roll_number' => $request->roll_number ?? null,
                'previous_school' => $request->previous_school,
                'previous_grade' => $request->previous_grade,
            ]);
            
            // Generate roll number if not provided
            if (!$student->roll_number) {
                $this->generateRollNumber($student);
            }
            
            // Attach parents
            if ($request->has('parent_ids')) {
                $student->parents()->attach($request->parent_ids);
            }
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'student',
                'description' => "Created student: {$user->name} (Admission No: {$admissionNumber})",
            ]);
            
            return redirect()->route('super-admin.students.index')
                ->with('success', "Student created successfully. Admission Number: {$admissionNumber}");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create student: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display student details
     */
    public function show(Student $student)
    {
        $student->load(['user', 'class', 'section', 'academicYear', 'parents.user', 'fees', 'attendances']);
        
        // Attendance summary
        $attendanceSummary = [
            'total_days' => $student->attendances()->count(),
            'present' => $student->attendances()->where('status', 'present')->count(),
            'absent' => $student->attendances()->where('status', 'absent')->count(),
            'late' => $student->attendances()->where('status', 'late')->count(),
            'percentage' => $student->attendance_percentage,
        ];
        
        // Fee summary
        $feeSummary = [
            'total_fees' => $student->fees()->sum('total_amount'),
            'paid' => $student->fees()->sum('paid_amount'),
            'due' => $student->fees()->sum('due_amount'),
            'due_formatted' => '₹ ' . number_format($student->fees()->sum('due_amount'), 2),
        ];
        
        // Recent exam results
        $recentResults = $student->examResults()
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->latest()
            ->take(5)
            ->get();
        
        return view('super-admin.students.show', compact('student', 'attendanceSummary', 'feeSummary', 'recentResults'));
    }
    
    /**
     * Show form for editing student
     */
    public function edit(Student $student)
    {
        $classes = Classes::with('sections')->get();
        $academicYears = AcademicYear::all();
        $parents = User::where('user_type', 'parent')->get();
        $selectedParents = $student->parents->pluck('id')->toArray();
        
        return view('super-admin.students.edit', compact('student', 'classes', 'academicYears', 'parents', 'selectedParents'));
    }
    
    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'previous_school' => 'nullable|string',
            'previous_grade' => 'nullable|numeric',
            'parent_ids' => 'nullable|array',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update user
            $student->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
            
            // Update profile photo
            if ($request->hasFile('profile_photo')) {
                if ($student->user->profile_photo) {
                    Storage::disk('public')->delete($student->user->profile_photo);
                }
                $photoPath = $request->file('profile_photo')->store('profiles', 'public');
                $student->user->update(['profile_photo' => $photoPath]);
            }
            
            // Update profile
            if ($student->user->profile) {
                $student->user->profile->update([
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'blood_group' => $request->blood_group,
                ]);
            }
            
            // Update student
            $student->update([
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'academic_year_id' => $request->academic_year_id,
                'roll_number' => $request->roll_number ?? $student->roll_number,
                'previous_school' => $request->previous_school,
                'previous_grade' => $request->previous_grade,
            ]);
            
            // Sync parents
            if ($request->has('parent_ids')) {
                $student->parents()->sync($request->parent_ids);
            }
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'student',
                'description' => "Updated student: {$student->user->name}",
            ]);
            
            return redirect()->route('super-admin.students.index')
                ->with('success', 'Student updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update student: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete student
     */
    public function destroy(Student $student)
    {
        $studentName = $student->user->name;
        
        // Delete profile photo
        if ($student->user->profile_photo) {
            Storage::disk('public')->delete($student->user->profile_photo);
        }
        
        $student->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'student',
            'description' => "Deleted student: {$studentName}",
        ]);
        
        return redirect()->route('super-admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }
    
    /**
     * Promote student to next class
     */
    public function promote(Request $request, Student $student)
    {
        $request->validate([
            'new_class_id' => 'required|exists:classes,id',
            'new_section_id' => 'required|exists:sections,id',
            'new_academic_year_id' => 'required|exists:academic_years,id',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Create new student record for next year
            $newStudent = Student::create([
                'user_id' => $student->user_id,
                'admission_number' => $student->admission_number,
                'admission_date' => $student->admission_date,
                'class_id' => $request->new_class_id,
                'section_id' => $request->new_section_id,
                'academic_year_id' => $request->new_academic_year_id,
                'previous_school' => $student->previous_school,
                'previous_grade' => $student->previous_grade,
            ]);
            
            // Generate new roll number
            $this->generateRollNumber($newStudent);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'promoted',
                'module' => 'student',
                'description' => "Promoted student: {$student->user->name} to class {$newStudent->class->name}",
            ]);
            
            return redirect()->route('super-admin.students.show', $newStudent)
                ->with('success', 'Student promoted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to promote student: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate ID Card
     */
    public function generateIdCard(Student $student)
    {
        $student->load(['user', 'class', 'section']);
        $school = \App\Models\SchoolSetting::first();
        
        $pdf = PDF::loadView('super-admin.students.id-card', compact('student', 'school'));
        $pdf->setPaper([0, 0, 400, 250], 'landscape');
        
        return $pdf->download('id-card-' . $student->admission_number . '.pdf');
    }
    
    /**
     * Show import form
     */
    public function importForm()
    {
        $classes = Classes::all();
        $academicYears = AcademicYear::all();
        return view('super-admin.students.import', compact('classes', 'academicYears'));
    }
    
    /**
     * Import students from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:5120',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        
        try {
            Excel::import(new StudentsImport(
                $request->class_id,
                $request->section_id,
                $request->academic_year_id
            ), $request->file('file'));
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'imported',
                'module' => 'student',
                'description' => "Imported students from file",
            ]);
            
            return redirect()->route('super-admin.students.index')
                ->with('success', 'Students imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing students: ' . $e->getMessage());
        }
    }
    
    /**
     * Export students to Excel
     */
    public function export(Request $request)
    {
        return Excel::download(new StudentsExport($request->all()), 'students_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Helper: Generate username
     */
    private function generateUsername($name)
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $username = $base;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * Helper: Generate admission number
     */
    private function generateAdmissionNumber()
    {
        $year = now()->format('Y');
        $lastStudent = Student::whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastStudent) {
            $lastNumber = intval(substr($lastStudent->admission_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return 'ADM' . $year . $newNumber;
    }
    
    /**
     * Helper: Generate roll number
     */
    private function generateRollNumber(Student $student)
    {
        $count = Student::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->count();
        
        $student->update(['roll_number' => $count]);
    }
}