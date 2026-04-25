<?php
// app/Http/Controllers/Api/SuperAdmin/TeacherController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\SuperAdmin\StoreTeacherRequest;
use App\Http\Requests\Api\SuperAdmin\UpdateTeacherRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Employee;
use App\Models\UserProfile;
use App\Models\ClassSubject;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TeacherController extends BaseController
{
    /**
     * Display a listing of teachers
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'teacher')
            ->with(['profile', 'employee', 'teachingSubjects']);
        
        // Filter by department
        if ($request->has('department')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('employee_id', 'like', "%{$search}%")
                         ->orWhere('designation', 'like', "%{$search}%");
                  });
            });
        }
        
        $teachers = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return $this->sendPaginatedResponse(
            UserResource::collection($teachers),
            'Teachers retrieved successfully'
        );
    }
    
    /**
     * Store a newly created teacher
     */
    public function store(StoreTeacherRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $this->generateUsername($validated['name']),
                'password' => Hash::make($validated['password'] ?? 'password123'),
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'user_type' => 'teacher',
                'is_active' => true
            ]);
            
            $user->assignRole('teacher');
            
            // Create profile
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'qualification' => $validated['qualification'],
                'experience_years' => $validated['experience_years'],
                'emergency_contact' => $validated['emergency_contact'] ?? null
            ]);
            
            // Generate employee ID
            $employeeId = $this->generateEmployeeId();
            
            // Create employee record
            Employee::create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'employment_type' => $validated['employment_type'],
                'joining_date' => $validated['joining_date'],
                'department' => $validated['department'],
                'designation' => $validated['designation'],
                'qualification' => $validated['qualification'],
                'experience_years' => $validated['experience_years'],
                'salary' => $validated['salary'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
                'ifsc_code' => $validated['ifsc_code'] ?? null,
                'pan_number' => $validated['pan_number'] ?? null
            ]);
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'teacher',
                'description' => "Created teacher: {$user->name}"
            ]);
            
            return $this->sendResponse(
                new UserResource($user->load(['profile', 'employee'])),
                'Teacher created successfully',
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create teacher: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified teacher
     */
    public function show(User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return $this->sendError('User is not a teacher', [], 404);
        }
        
        $teacher->load(['profile', 'employee', 'teachingSubjects.class']);
        
        // Get classes taught
        $classes = ClassSubject::where('teacher_id', $teacher->id)
            ->with(['class', 'subject', 'class.sections'])
            ->get()
            ->groupBy('class_id');
        
        return $this->sendResponse([
            'teacher' => new UserResource($teacher),
            'classes_taught' => $classes->map(function($items) {
                $class = $items->first()->class;
                return [
                    'class' => [
                        'id' => $class->id,
                        'name' => $class->name,
                        'sections' => $class->sections,
                    ],
                    'subjects' => $items->map(function($item) {
                        return [
                            'id' => $item->subject->id,
                            'name' => $item->subject->name,
                            'code' => $item->subject->code,
                        ];
                    }),
                ];
            })->values(),
        ], 'Teacher retrieved successfully');
    }
    
    /**
     * Update the specified teacher
     */
    public function update(UpdateTeacherRequest $request, User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return $this->sendError('User is not a teacher', [], 404);
        }
        
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Update user
            $teacher->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address']
            ]);
            
            // Update profile
            if ($teacher->profile) {
                $teacher->profile->update([
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'qualification' => $validated['qualification'],
                    'experience_years' => $validated['experience_years'],
                    'emergency_contact' => $validated['emergency_contact'] ?? null
                ]);
            }
            
            // Update employee
            if ($teacher->employee) {
                $teacher->employee->update([
                    'employment_type' => $validated['employment_type'],
                    'joining_date' => $validated['joining_date'],
                    'department' => $validated['department'],
                    'designation' => $validated['designation'],
                    'qualification' => $validated['qualification'],
                    'experience_years' => $validated['experience_years'],
                    'salary' => $validated['salary'] ?? null,
                    'bank_name' => $validated['bank_name'] ?? null,
                    'bank_account' => $validated['bank_account'] ?? null,
                    'ifsc_code' => $validated['ifsc_code'] ?? null,
                    'pan_number' => $validated['pan_number'] ?? null
                ]);
            }
            
            // Update password if provided
            if ($request->filled('password')) {
                $teacher->update(['password' => Hash::make($request->password)]);
            }
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'teacher',
                'description' => "Updated teacher: {$teacher->name}"
            ]);
            
            return $this->sendResponse(
                new UserResource($teacher->fresh(['profile', 'employee'])),
                'Teacher updated successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update teacher: ' . $e->getMessage());
        }
    }
    
    /**
     * Assign class to teacher
     */
    public function assignClass(Request $request, User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return $this->sendError('User is not a teacher', [], 404);
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
                'description' => "Assigned class to teacher: {$teacher->name}"
            ]);
            
            return $this->sendResponse([
                'class_subject' => $classSubject,
            ], 'Class assigned successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to assign class: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified teacher
     */
    public function destroy(User $teacher)
    {
        if ($teacher->user_type != 'teacher') {
            return $this->sendError('User is not a teacher', [], 404);
        }
        
        $teacherName = $teacher->name;
        $teacher->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'teacher',
            'description' => "Deleted teacher: {$teacherName}"
        ]);
        
        return $this->sendResponse([], 'Teacher deleted successfully');
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
        
        return 'EMP' . $year . $newNumber;
    }
}