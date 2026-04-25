<?php
// app/Http/Controllers/Api/SuperAdmin/EmployeeController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\SuperAdmin\StoreEmployeeRequest;
use App\Http\Requests\Api\SuperAdmin\UpdateEmployeeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Employee;
use App\Models\UserProfile;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeController extends BaseController
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'employee')
            ->with(['profile', 'employee']);
        
        // Filter by department
        if ($request->has('department')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }
        
        // Filter by employment type
        if ($request->has('employment_type')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('employment_type', $request->employment_type);
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
        
        $employees = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return $this->sendPaginatedResponse(
            UserResource::collection($employees),
            'Employees retrieved successfully'
        );
    }
    
    /**
     * Store a newly created employee
     */
    public function store(StoreEmployeeRequest $request)
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
                'user_type' => 'employee',
                'is_active' => true
            ]);
            
            $user->assignRole('employee');
            
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
                'module' => 'employee',
                'description' => "Created employee: {$user->name}"
            ]);
            
            return $this->sendResponse(
                new UserResource($user->load(['profile', 'employee'])),
                'Employee created successfully',
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create employee: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified employee
     */
    public function show(User $employee)
    {
        if ($employee->user_type != 'employee') {
            return $this->sendError('User is not an employee', [], 404);
        }
        
        $employee->load(['profile', 'employee']);
        
        return $this->sendResponse(
            new UserResource($employee),
            'Employee retrieved successfully'
        );
    }
    
    /**
     * Update the specified employee
     */
    public function update(UpdateEmployeeRequest $request, User $employee)
    {
        if ($employee->user_type != 'employee') {
            return $this->sendError('User is not an employee', [], 404);
        }
        
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Update user
            $employee->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address']
            ]);
            
            // Update profile
            if ($employee->profile) {
                $employee->profile->update([
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'qualification' => $validated['qualification'],
                    'experience_years' => $validated['experience_years'],
                    'emergency_contact' => $validated['emergency_contact'] ?? null
                ]);
            }
            
            // Update employee
            if ($employee->employee) {
                $employee->employee->update([
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
                $employee->update(['password' => Hash::make($request->password)]);
            }
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'employee',
                'description' => "Updated employee: {$employee->name}"
            ]);
            
            return $this->sendResponse(
                new UserResource($employee->fresh(['profile', 'employee'])),
                'Employee updated successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update employee: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified employee
     */
    public function destroy(User $employee)
    {
        if ($employee->user_type != 'employee') {
            return $this->sendError('User is not an employee', [], 404);
        }
        
        $employeeName = $employee->name;
        $employee->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'employee',
            'description' => "Deleted employee: {$employeeName}"
        ]);
        
        return $this->sendResponse([], 'Employee deleted successfully');
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