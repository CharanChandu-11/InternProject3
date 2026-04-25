<?php
// app/Http/Controllers/Admin/StudentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Classes;
use App\Models\Section;
use App\Models\ParentModel;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Services\FeeService;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'class', 'section', 'academicYear']);
        
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('admission_number', 'like', "%{$search}%");
        }
        
        $students = $query->orderBy('created_at', 'desc')->paginate(20);
        $classes = Classes::all();
        
        return view('admin.students.index', compact('students', 'classes'));
    }
    
    public function create()
    {
        $classes = Classes::with('sections')->get();
        $academicYears = AcademicYear::all();
        $parents = User::where('user_type', 'parent')->get();
        
        return view('admin.students.create', compact('classes', 'academicYears', 'parents'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'admission_date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'address' => 'nullable|string',
            'parent_ids' => 'nullable|array',
        ]);
        
        DB::beginTransaction();
        
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $this->generateUsername($validated['name']),
                'password' => Hash::make('password123'),
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
                'user_type' => 'student',
                'is_active' => true,
            ]);
            
            $user->assignRole('student');
            
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
            ]);
            
            $admissionNumber = $this->generateAdmissionNumber();
            
            $student = Student::create([
                'user_id' => $user->id,
                'admission_number' => $admissionNumber,
                'admission_date' => $validated['admission_date'],
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'],
                'academic_year_id' => $validated['academic_year_id'],
            ]);
            
            // Handle parent relationships - FIXED
            if ($request->has('parent_ids')) {
                $parentIds = [];
                
                foreach ($request->parent_ids as $parentUserId) {
                    // Find the parent record for this user
                    $parent = ParentModel::where('user_id', $parentUserId)->first();
                    
                    if ($parent) {
                        $parentIds[] = $parent->id; // Use parent table ID, not user ID
                    } else {
                        // Parent doesn't exist, create it
                        $parent = ParentModel::create([
                            'user_id' => $parentUserId,
                            'parent_type' => 'other', // or set a default
                            'occupation' => null,
                            'office_address' => null,
                            'office_phone' => null
                        ]);
                        $parentIds[] = $parent->id;
                    }
                }
                
                // Sync using parent table IDs
                $student->parents()->sync($parentIds);
            }
            
            $this->generateRollNumber($student);
            
            DB::commit();

            FeeService::generateForStudent($student);
            
            return redirect()->route('admin.students.index')->with('success', "Student created. Admission No: {$admissionNumber}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create student: ' . $e->getMessage());
        }
    }
    
    public function show(Student $student)
    {
        // $student->load(['user', 'class', 'section', 'academicYear', 'parents.user', 'fees']);
        
        $attendanceSummary = [
            'total_days' => $student->attendances()->count(),
            'present' => $student->attendances()->where('status', 'present')->count(),
            'absent' => $student->attendances()->where('status', 'absent')->count(),
            'percentage' => $student->attendance_percentage,
        ];
        
        $feeSummary = [
            'total_fees' => $student->fees()->sum('total_amount'),
            'paid' => $student->fees()->sum('paid_amount'),
            'due' => $student->fees()->sum('due_amount'),
        ];
        
        return view('admin.students.show', compact('student', 'attendanceSummary', 'feeSummary'));
    }
    
    public function edit(Student $student)
    {
        $classes = Classes::with('sections')->get();
        $academicYears = AcademicYear::all();
        $parents = User::where('user_type', 'parent')->get();
        $selectedParents = $student->parents->pluck('user_id')->toArray();
        
        return view('admin.students.edit', compact('student', 'classes', 'academicYears', 'parents', 'selectedParents'));
    }
    
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'roll_number' => 'nullable|integer',
            'previous_school' => 'nullable|string',
            'previous_grade' => 'nullable|string',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:users,id', // Validate against users table, not parents
        ]);

        DB::beginTransaction();

        try {
            // Update user
            $student->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address
            ]);

            // Update profile
            if ($student->user->profile) {
                $student->user->profile->update([
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'blood_group' => $request->blood_group
                ]);
            }

            // Update student
            $student->update([
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'academic_year_id' => $request->academic_year_id,
                'roll_number' => $request->roll_number,
                'previous_school' => $request->previous_school,
                'previous_grade' => $request->previous_grade
            ]);

            // Handle parent relationships - FIXED
            if ($request->has('parent_ids')) {
                $parentIds = [];
                
                foreach ($request->parent_ids as $parentUserId) {
                    // Find the parent record for this user
                    $parent = ParentModel::where('user_id', $parentUserId)->first();
                    
                    if ($parent) {
                        $parentIds[] = $parent->id; // Use parent table ID, not user ID
                    } else {
                        // Parent doesn't exist, create it
                        $parent = ParentModel::create([
                            'user_id' => $parentUserId,
                            'parent_type' => 'other', // or set a default
                            'occupation' => null,
                            'office_address' => null,
                            'office_phone' => null
                        ]);
                        $parentIds[] = $parent->id;
                    }
                }
                
                // Sync using parent table IDs
                $student->parents()->sync($parentIds);
            }

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', 'Student updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update student: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student deleted');
    }
    
    public function importForm()
    {
        return view('admin.students.import');
    }
    
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,xlsx']);
        
        try {
            Excel::import(new StudentsImport, $request->file('file'));
            return redirect()->route('admin.students.index')->with('success', 'Students imported successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
    
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
    
    private function generateAdmissionNumber()
    {
        $year = now()->format('Y');
        $lastStudent = Student::whereYear('created_at', now()->year)->orderBy('id', 'desc')->first();
        
        if ($lastStudent) {
            $lastNumber = intval(substr($lastStudent->admission_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return 'ADM' . $year . $newNumber;
    }
    
    private function generateRollNumber(Student $student)
    {
        $count = Student::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->count();
        
        $student->update(['roll_number' => $count]);
    }
}