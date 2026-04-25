<?php
// app/Http/Controllers/Api/SuperAdmin/StudentController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\SuperAdmin\StoreStudentRequest;
use App\Http\Requests\Api\SuperAdmin\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ActivityLog;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends BaseController
{
    /**
     * Display a listing of students
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'class', 'section', 'academicYear', 'parents']);
        
        // Filter by class
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter by section
        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        
        // Filter by academic year
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        } else {
            $currentYear = AcademicYear::where('is_current', true)->first();
            if ($currentYear) {
                $query->where('academic_year_id', $currentYear->id);
            }
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('admission_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        
        $students = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return $this->sendPaginatedResponse(
            StudentResource::collection($students),
            'Students retrieved successfully'
        );
    }
    
    /**
     * Store a newly created student
     */
    public function store(StoreStudentRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $this->generateUsername($validated['name']),
                'password' => Hash::make('password123'),
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'user_type' => 'student',
                'is_active' => true
            ]);
            
            $user->assignRole('student');
            
            // Create profile
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'blood_group' => $validated['blood_group'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null
            ]);
            
            // Generate admission number
            $admissionNumber = $this->generateAdmissionNumber();
            
            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'admission_number' => $admissionNumber,
                'admission_date' => $validated['admission_date'] ?? now(),
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'roll_number' => $validated['roll_number'] ?? null,
                'previous_school' => $validated['previous_school'] ?? null,
                'previous_grade' => $validated['previous_grade'] ?? null
            ]);
            
            // Attach parents if provided
            if ($request->has('parent_ids')) {
                $student->parents()->attach($request->parent_ids);
            }
            
            // Generate roll number if not provided
            if (!$student->roll_number) {
                $this->generateRollNumber($student);
            }
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'student',
                'description' => "Created student: {$user->name}"
            ]);
            
            return $this->sendResponse(
                new StudentResource($student->load(['user', 'class', 'section'])),
                'Student created successfully',
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create student: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified student
     */
    public function show(Student $student)
    {
        $student->load(['user', 'class', 'section', 'academicYear', 'parents.user', 'fees', 'attendances']);
        
        // Get attendance summary
        $attendanceSummary = [
            'total_days' => $student->attendances()->count(),
            'present' => $student->attendances()->where('status', 'present')->count(),
            'absent' => $student->attendances()->where('status', 'absent')->count(),
            'late' => $student->attendances()->where('status', 'late')->count(),
            'percentage' => $student->attendance_percentage,
        ];
        
        // Get fee summary
        $feeSummary = [
            'total_fees' => $student->fees()->sum('total_amount'),
            'paid' => $student->fees()->sum('paid_amount'),
            'due' => $student->fees()->sum('due_amount'),
            'due_formatted' => '₹ ' . number_format($student->fees()->sum('due_amount'), 2),
            'pending_fees_count' => $student->fees()->whereIn('status', ['pending', 'partial'])->count(),
        ];
        
        // Get exam results
        $examResults = $student->examResults()
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->latest()
            ->take(5)
            ->get();
        
        return $this->sendResponse([
            'student' => new StudentResource($student),
            'attendance_summary' => $attendanceSummary,
            'fee_summary' => $feeSummary,
            'recent_results' => $examResults,
        ], 'Student retrieved successfully');
    }
    
    /**
     * Update the specified student
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Update user
            $student->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address']
            ]);
            
            // Update profile
            if ($student->user->profile) {
                $student->user->profile->update([
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'blood_group' => $validated['blood_group'] ?? null,
                    'emergency_contact' => $validated['emergency_contact'] ?? null
                ]);
            }
            
            // Update student
            $student->update([
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'],
                'roll_number' => $validated['roll_number'] ?? $student->roll_number,
                'previous_school' => $validated['previous_school'] ?? null,
                'previous_grade' => $validated['previous_grade'] ?? null
            ]);
            
            // Sync parents
            if ($request->has('parent_ids')) {
                $student->parents()->sync($request->parent_ids);
            }
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'student',
                'description' => "Updated student: {$student->user->name}"
            ]);
            
            return $this->sendResponse(
                new StudentResource($student->fresh(['user', 'class', 'section'])),
                'Student updated successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update student: ' . $e->getMessage());
        }
    }
    
    /**
     * Promote student to next class
     */
    public function promote(Request $request, Student $student)
    {
        $request->validate([
            'new_class_id' => 'required|exists:classes,id',
            'new_section_id' => 'required|exists:sections,id',
            'new_academic_year_id' => 'required|exists:academic_years,id'
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
                'previous_grade' => $student->previous_grade
            ]);
            
            // Generate new roll number
            $this->generateRollNumber($newStudent);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'promoted',
                'module' => 'student',
                'description' => "Promoted student: {$student->user->name}"
            ]);
            
            return $this->sendResponse(
                new StudentResource($newStudent->load(['user', 'class', 'section'])),
                'Student promoted successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to promote student: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate ID card for student
     */
    public function generateIdCard(Student $student)
    {
        $student->load(['user', 'class', 'section']);
        
        $data = [
            'student' => $student,
            'school' => \App\Models\SchoolSetting::first(),
            'date' => now()->format('F j, Y'),
        ];
        
        $pdf = PDF::loadView('pdf.id-card', $data);
        $pdf->setPaper([0, 0, 300, 400], 'portrait');
        
        return $pdf->download('id-card-' . $student->admission_number . '.pdf');
    }
    
    /**
     * Remove the specified student
     */
    public function destroy(Student $student)
    {
        $studentName = $student->user->name;
        $student->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'student',
            'description' => "Deleted student: {$studentName}"
        ]);
        
        return $this->sendResponse([], 'Student deleted successfully');
    }
    
    /**
     * Bulk import students
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx|max:10240'
        ]);
        
        try {
            Excel::import(new StudentsImport, $request->file('file'));
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_import',
                'module' => 'student',
                'description' => "Imported students from file"
            ]);
            
            return $this->sendResponse([], 'Students imported successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to import students: ' . $e->getMessage());
        }
    }
    
    /**
     * Export students
     */
    public function export(Request $request)
    {
        try {
            $export = new StudentsExport($request->all());
            return Excel::download($export, 'students_' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            return $this->sendError('Failed to export students: ' . $e->getMessage());
        }
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